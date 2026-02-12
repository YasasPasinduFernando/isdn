<?php

/**
 * Dashboard Model
 *
 * Data-fetching for Head Office Manager Dashboard.
 * Schema: orders.rdc_id → rdcs.rdc_id (direct link)
 *         products.category (varchar, no separate categories table)
 *         orders.created_at (not order_date)
 */
class Dashboard
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ── helper: build dynamic WHERE for orders (alias o) ── */
    private function buildConditions(array $filters = []): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['rdc_id'])) {
            $conditions[]       = 'o.rdc_id = :f_rdc_id';
            $params['f_rdc_id'] = (int) $filters['rdc_id'];
        }
        if (!empty($filters['category'])) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM order_items oi_x
                JOIN products p_x ON oi_x.product_id = p_x.product_id
                WHERE oi_x.order_id = o.id AND p_x.category = :f_cat
            )";
            $params['f_cat'] = $filters['category'];
        }
        if (!empty($filters['start_date'])) {
            $conditions[]      = 'o.created_at >= :f_start';
            $params['f_start'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]    = 'o.created_at <= :f_end';
            $params['f_end'] = $filters['end_date'] . ' 23:59:59';
        }

        return [
            'where'  => $conditions ? implode(' AND ', $conditions) : '1=1',
            'params' => $params,
        ];
    }

    /* ── KPIs ── */

    public function getTotalRevenue(array $filters = []): float
    {
        $f = $this->buildConditions($filters);

        $sql = "SELECT COALESCE(SUM(o.total_amount), 0)
                FROM orders o
                WHERE o.status != 'cancelled' AND {$f['where']}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($f['params']);
        return (float) $stmt->fetchColumn();
    }

    public function getTotalOrders(array $filters = []): int
    {
        $f = $this->buildConditions($filters);

        $sql = "SELECT COUNT(DISTINCT o.id)
                FROM orders o
                WHERE {$f['where']}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($f['params']);
        return (int) $stmt->fetchColumn();
    }

    public function getOrderCountByStatus(string $status, array $filters = []): int
    {
        $f = $this->buildConditions($filters);

        $sql = "SELECT COUNT(DISTINCT o.id)
                FROM orders o
                WHERE o.status = :status AND {$f['where']}";

        $params           = $f['params'];
        $params['status'] = $status;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getMonthlyGrowth(): array
    {
        $sql = "SELECT
                    COALESCE(SUM(CASE
                        WHEN YEAR(created_at)  = YEAR(CURDATE())
                         AND MONTH(created_at) = MONTH(CURDATE())
                        THEN total_amount END), 0) AS current_month,
                    COALESCE(SUM(CASE
                        WHEN YEAR(created_at)  = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                         AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                        THEN total_amount END), 0) AS previous_month
                FROM orders
                WHERE status != 'cancelled'";

        $row      = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        $current  = (float) $row['current_month'];
        $previous = (float) $row['previous_month'];
        $growth   = $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : ($current > 0 ? 100.0 : 0.0);

        return [
            'current_month'  => $current,
            'previous_month' => $previous,
            'growth_pct'     => $growth,
        ];
    }

    public function getLowStockAlerts(int $limit = 10): array
    {
        $sql = "SELECT p.product_name, p.product_code,
                       p.minimum_stock_level,
                       ps.available_quantity,
                       r.rdc_name
                FROM product_stocks ps
                JOIN products p ON ps.product_id = p.product_id
                JOIN rdcs r     ON ps.rdc_id     = r.rdc_id
                WHERE p.minimum_stock_level IS NOT NULL
                  AND ps.available_quantity < p.minimum_stock_level
                ORDER BY (ps.available_quantity / p.minimum_stock_level) ASC
                LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Reports / Charts ── */

    public function getRdcSalesComparison(array $filters = []): array
    {
        $conditions = ["o.status != 'cancelled'"];
        $params     = [];

        if (!empty($filters['start_date'])) {
            $conditions[]      = 'o.created_at >= :f_start';
            $params['f_start'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]    = 'o.created_at <= :f_end';
            $params['f_end'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $conditions[]       = 'o.rdc_id = :f_rdc_id';
            $params['f_rdc_id'] = (int) $filters['rdc_id'];
        }
        if (!empty($filters['category'])) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM order_items oi_x
                JOIN products p_x ON oi_x.product_id = p_x.product_id
                WHERE oi_x.order_id = o.id AND p_x.category = :f_cat
            )";
            $params['f_cat'] = $filters['category'];
        }

        $where = implode(' AND ', $conditions);

        $sql = "SELECT r.rdc_name, r.rdc_id,
                       COUNT(DISTINCT o.id) AS order_count,
                       COALESCE(SUM(o.total_amount), 0) AS total_revenue
                FROM orders o
                JOIN rdcs r ON o.rdc_id = r.rdc_id
                WHERE {$where}
                GROUP BY r.rdc_id, r.rdc_name
                ORDER BY total_revenue DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopSellingProducts(array $filters = [], int $limit = 5): array
    {
        $conditions = ["o.status != 'cancelled'"];
        $params     = [];

        if (!empty($filters['rdc_id'])) {
            $conditions[]       = 'o.rdc_id = :f_rdc_id';
            $params['f_rdc_id'] = (int) $filters['rdc_id'];
        }
        if (!empty($filters['category'])) {
            $conditions[]    = 'p.category = :f_cat';
            $params['f_cat'] = $filters['category'];
        }
        if (!empty($filters['start_date'])) {
            $conditions[]      = 'o.created_at >= :f_start';
            $params['f_start'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]    = 'o.created_at <= :f_end';
            $params['f_end'] = $filters['end_date'] . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);

        $sql = "SELECT p.product_name, p.product_code,
                       SUM(oi.quantity) AS total_qty,
                       SUM(oi.quantity * COALESCE(oi.selling_price, 0)) AS total_sales
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                JOIN products p    ON oi.product_id = p.product_id
                WHERE {$where}
                GROUP BY p.product_id, p.product_name, p.product_code
                ORDER BY total_sales DESC
                LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMonthlyRevenueTrend(int $months = 6): array
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                       DATE_FORMAT(created_at, '%b %Y')  AS month_label,
                       COALESCE(SUM(total_amount), 0)     AS revenue,
                       COUNT(*) AS order_count
                FROM orders
                WHERE status != 'cancelled'
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderStatusDistribution(array $filters = []): array
    {
        $f = $this->buildConditions($filters);

        $sql = "SELECT o.status, COUNT(DISTINCT o.id) AS count
                FROM orders o
                WHERE {$f['where']}
                GROUP BY o.status
                ORDER BY count DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($f['params']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDeliveryEfficiency(array $filters = []): array
    {
        $conditions = ['1=1'];
        $params     = [];

        if (!empty($filters['rdc_id'])) {
            $conditions[]       = 'o.rdc_id = :f_rdc_id';
            $params['f_rdc_id'] = (int) $filters['rdc_id'];
        }
        if (!empty($filters['category'])) {
            $conditions[] = "EXISTS (
                SELECT 1 FROM order_items oi_x
                JOIN products p_x ON oi_x.product_id = p_x.product_id
                WHERE oi_x.order_id = o.id AND p_x.category = :f_cat
            )";
            $params['f_cat'] = $filters['category'];
        }
        if (!empty($filters['start_date'])) {
            $conditions[]      = 'o.created_at >= :f_start';
            $params['f_start'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]    = 'o.created_at <= :f_end';
            $params['f_end'] = $filters['end_date'] . ' 23:59:59';
        }

        $where = implode(' AND ', $conditions);

        $sql = "SELECT
                    COUNT(d.id) AS total_deliveries,
                    SUM(CASE WHEN d.completed_date IS NOT NULL THEN 1 ELSE 0 END) AS completed,
                    ROUND(AVG(
                        CASE WHEN d.completed_date IS NOT NULL
                             THEN TIMESTAMPDIFF(HOUR, o.created_at, d.completed_date)
                        END
                    ), 1) AS avg_hours,
                    ROUND(
                        SUM(CASE WHEN d.completed_date IS NOT NULL THEN 1 ELSE 0 END) * 100.0
                        / NULLIF(COUNT(d.id), 0)
                    , 1) AS completion_rate
                FROM order_deliveries d
                JOIN orders o ON d.order_id = o.id
                WHERE {$where}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_deliveries' => 0,
            'completed'        => 0,
            'avg_hours'        => 0,
            'completion_rate'  => 0,
        ];
    }

    /* ── Filters ── */

    public function getAllRdcs(): array
    {
        return $this->pdo->query(
            "SELECT rdc_id, rdc_name, rdc_code FROM rdcs ORDER BY rdc_name"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories(): array
    {
        return $this->pdo->query(
            "SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Stock Transfer Approvals ── */

    public function getPendingTransfers(): array
    {
        $sql = "SELECT st.*, r1.rdc_name AS source_rdc, r2.rdc_name AS dest_rdc, u.username AS requester
                FROM stock_transfers st
                JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
                JOIN rdcs r2 ON st.destination_rdc_id = r2.rdc_id
                JOIN users u ON st.requested_by = u.id
                WHERE st.approval_status = 'PENDING'
                ORDER BY st.is_urgent DESC, st.requested_date DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTransferStatus(int $transferId, string $status, int $approvedBy, string $remarks = ''): bool
    {
        if (!in_array($status, ['APPROVED', 'REJECTED'], true)) {
            return false;
        }
        $stmt = $this->pdo->prepare(
            "UPDATE stock_transfers SET approval_status = ?, approved_by = ?, approval_date = NOW(), approval_remarks = ? WHERE transfer_id = ? AND approval_status = 'PENDING'"
        );
        return $stmt->execute([$status, $approvedBy, $remarks, $transferId]);
    }

    public function logTransferStatusChange(int $transferId, string $newStatus, int $changedBy): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO transfer_status_logs (transfer_id, previous_status, new_status, changed_by, changed_date) VALUES (?, 'PENDING', ?, ?, NOW())"
        );
        $stmt->execute([$transferId, $newStatus, $changedBy]);
    }
}
