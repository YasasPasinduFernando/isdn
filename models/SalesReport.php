<?php
class SalesReport
{
    private PDO $pdo;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }



    public function getTotalSales(array $filters = [])
    {
        $date_from = $date_to = $rdc_id = null;
        $conditions = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $date_from = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $date_to = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $rdc_id = (int) $filters['rdc_id'];
        }
        $sql = "
        SELECT
            COUNT(*) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS total_sales
        FROM orders o
        WHERE o.order_date >= :date_from
          AND o.order_date < :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to
        ];

        if (!empty($rdc_id)) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPaymentsReceived(array $filters = [])
    {
        $date_from = $date_to = $rdc_id = null;
        $conditions = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $date_from = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $date_to = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $rdc_id = (int) $filters['rdc_id'];
        }
        $sql = "
        SELECT
            COUNT(p.id) AS payment_count,
            COALESCE(SUM(p.amount), 0) AS total_payment_received
        FROM payments p
        JOIN orders o ON o.id = p.order_id
        WHERE p.payment_date >= :date_from
          AND p.payment_date < :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to
        ];

        // Add RDC condition only if provided
        if (!empty($rdc_id)) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStatusSummary(array $filters = [])
    {
        $date_from = $date_to = $rdc_id = null;
        $conditions = [];
        $params = [];

        if (!empty($filters['start_date'])) {
            $date_from = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $date_to = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $rdc_id = (int) $filters['rdc_id'];
        }
        $sql = "
        SELECT 
            o.status,
            COUNT(*) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS total_amount_sum
        FROM orders o
        WHERE o.order_date >= :date_from
          AND o.order_date < :date_to
          AND o.status IN ('pending','processing','delivered','in transit','cancelled')
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to
        ];

        // Add RDC filter only if provided
        if (!empty($rdc_id)) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $sql .= "
        GROUP BY o.status
        ORDER BY FIELD(o.status,
            'pending',
            'processing',
            'in transit',
            'delivered',
            'cancelled'
        )
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProductAndAverageSale(array $filters = [])
    {
        $date_from = $date_to = $rdc_id = null;

        if (!empty($filters['start_date'])) {
            $date_from = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $date_to = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $rdc_id = (int) $filters['rdc_id'];
        }

        $sql = "
        SELECT 
            p.product_id,
            p.product_name,
            pc.name AS category_name,
            SUM(oi.quantity) AS total_qty_sold,
            COUNT(DISTINCT oi.order_id) AS order_count,
            ROUND(SUM(oi.quantity) / COUNT(DISTINCT oi.order_id), 2) AS avg_qty_per_order
        FROM order_items oi
        JOIN products p            ON p.product_id = oi.product_id
        JOIN product_categories pc ON pc.category_id = p.category_id
        JOIN orders o              ON o.id = oi.order_id
        WHERE o.order_date >= :date_from
          AND o.order_date < :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to
        ];

        if (!empty($rdc_id)) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $sql .= "
        GROUP BY p.product_id, p.product_name, pc.name
        ORDER BY total_qty_sold DESC
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopPurchasingCustomerByAmount(array $filters = [])
    {
        $date_from = $date_to = $rdc_id = null;

        if (!empty($filters['start_date'])) {
            $date_from = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $date_to = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $rdc_id = (int) $filters['rdc_id'];
        }

        $sql = "
        SELECT
            rc.id AS customer_id,
            rc.name AS customer_name,
            r.rdc_id,
            r.rdc_name,
            r.rdc_code,
            COALESCE(SUM(o.total_amount), 0) AS total_purchased_amount,
            COUNT(*) AS order_count
        FROM orders o
        JOIN retail_customers rc ON rc.id = o.customer_id
        JOIN rdcs r             ON r.rdc_id = o.rdc_id
        WHERE o.order_date >= :date_from
          AND o.order_date <  :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to
        ];

        // Add RDC condition only if provided
        if (!empty($rdc_id)) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $sql .= "
        GROUP BY rc.id, rc.name, r.rdc_id, r.rdc_name, r.rdc_code
        ORDER BY total_purchased_amount DESC
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopSalesRefByOrderAmount(array $filters = [])
    {
        $date_from = $date_to = $rdc_id = null;

        if (!empty($filters['start_date'])) {
            $date_from = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $date_to = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $rdc_id = (int) $filters['rdc_id'];
        }

        $sql = "
        SELECT
            sr.user_id AS sales_ref_user_id,
            sr.name AS sales_ref_name,
            r.rdc_id,
            r.rdc_name,
            r.rdc_code,
            COUNT(o.id) AS order_count,
            COALESCE(SUM(o.total_amount), 0) AS total_order_amount
        FROM orders o
        JOIN rdc_sales_refs sr ON sr.user_id = o.placed_by
        JOIN rdcs r            ON r.rdc_id = o.rdc_id
        WHERE o.order_date >= :date_from
          AND o.order_date <  :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to,
        ];

        // Add RDC filter only if provided
        if (!empty($rdc_id)) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $sql .= "
        GROUP BY sr.user_id, sr.name, r.rdc_id, r.rdc_name, r.rdc_code
        ORDER BY total_order_amount DESC
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderReport(array $filters = []): array
    {
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            // throw new InvalidArgumentException("start_date and end_date are required.");
            return [];
        }

        $date_from = $filters['start_date'] . ' 00:00:00';
        $date_to = $filters['end_date'] . ' 23:59:59';

        $sql = "
        SELECT
            o.order_number,
            rc.name AS customer_name,
            COALESCE(sr.name, 'N/A') AS sales_ref_name,
            DATE_FORMAT(o.order_date, '%Y-%m-%d') AS order_date,
            o.total_amount AS amount,
            o.status,
            CASE
                WHEN COALESCE(pay.total_paid, 0) >= o.total_amount THEN 'PAID'
                WHEN COALESCE(pay.total_paid, 0) > 0 THEN 'PARTIAL'
                ELSE 'UNPAID'
            END AS payment_status,
            COALESCE(oi.item_count, 0) AS number_of_items
        FROM orders o
        JOIN retail_customers rc ON rc.id = o.customer_id
        LEFT JOIN rdc_sales_refs sr ON sr.user_id = o.placed_by
        LEFT JOIN (
            SELECT order_id, SUM(amount) AS total_paid
            FROM payments
            GROUP BY order_id
        ) pay ON pay.order_id = o.id
        LEFT JOIN (
            SELECT order_id, SUM(quantity) AS item_count
            FROM order_items
            GROUP BY order_id
        ) oi ON oi.order_id = o.id
        WHERE o.order_date >= :date_from
          AND o.order_date <  :date_to
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to,
        ];

        // Optional RDC filter
        if (!empty($filters['rdc_id'])) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = (int) $filters['rdc_id'];
        }

        $sql .= " ORDER BY o.order_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesSummaryReport(array $filters = []): array
    {
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            // throw new InvalidArgumentException('start_date and end_date are required.');
            return [];
        }

        $results = [];

        // If specific RDC selected
        if (!empty($filters['rdc_id'])) {

            $rdcs = [];
            $stmt = $this->pdo->prepare("SELECT rdc_id, rdc_name FROM rdcs WHERE rdc_id = :rdc_id");
            $stmt->execute([':rdc_id' => (int) $filters['rdc_id']]);
            $rdcs[] = $stmt->fetch(PDO::FETCH_ASSOC);

        } else {

            // Fetch all RDCs
            $stmt = $this->pdo->query("SELECT rdc_id, rdc_name FROM rdcs ORDER BY rdc_name");
            $rdcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($rdcs as $rdc) {

            if (!$rdc)
                continue;

            $filters['rdc_id'] = $rdc['rdc_id'];

            $totalSales = $this->getTotalSales($filters);
            $paymentsReceived = $this->getPaymentsReceived($filters);
            $statusSummary = $this->getStatusSummary($filters);

            // Default status values
            $status = [
                'pending_count' => 0,
                'pending_sum' => 0,
                'delivered_count' => 0,
                'delivered_sum' => 0,
                'cancelled_count' => 0,
                'cancelled_sum' => 0,
            ];

            foreach ($statusSummary as $row) {

                $key = str_replace(' ', '_', strtolower($row['status']));

                if ($key === 'pending') {
                    $status['pending_count'] = (int) $row['order_count'];
                    $status['pending_sum'] = (float) $row['total_amount_sum'];
                }

                if ($key === 'delivered') {
                    $status['delivered_count'] = (int) $row['order_count'];
                    $status['delivered_sum'] = (float) $row['total_amount_sum'];
                }

                if ($key === 'cancelled') {
                    $status['cancelled_count'] = (int) $row['order_count'];
                    $status['cancelled_sum'] = (float) $row['total_amount_sum'];
                }
            }

            $results[] = [
                'rdc_name' => $rdc['rdc_name'],
                'total_sales' => (float) ($totalSales['total_sales'] ?? 0),
                'total_orders' => (int) ($totalSales['order_count'] ?? 0),
                'payment_received' => (float) ($paymentsReceived['total_payment_received'] ?? 0),
                'total_delivered' => $status['delivered_sum'],
                'delivered_count' => $status['delivered_count'],
                'total_pending' => $status['pending_sum'],
                'pending_count' => $status['pending_count'],
                'total_cancelled' => $status['cancelled_sum'],
                'cancelled_count' => $status['cancelled_count']
            ];
        }

        return $results;
    }

    public function getTopSellingItems(array $filters = []): array
    {
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            //throw new InvalidArgumentException('start_date and end_date are required.');
            return [];
        }

        $date_from = $filters['start_date'] . ' 00:00:00';
        $date_to = $filters['end_date'] . ' 23:59:59';
        $rdc_id = !empty($filters['rdc_id']) ? (int) $filters['rdc_id'] : null;

        $sql = "
        SELECT
            p.product_name,
            p.product_code,
            pc.name AS category,
            SUM(oi.quantity) AS total_sales_count,
            ROUND(SUM((COALESCE(oi.selling_price, p.unit_price) * oi.quantity) - COALESCE(oi.discount, 0)), 2) AS total_sales_amount,
            ROUND(SUM(oi.quantity) / NULLIF(COUNT(DISTINCT oi.order_id), 0), 2) AS average_sale
        FROM order_items oi
        JOIN products p
            ON p.product_id = oi.product_id
        JOIN product_categories pc
            ON pc.category_id = p.category_id
        JOIN orders o
            ON o.id = oi.order_id
        WHERE o.order_date >= :date_from
          AND o.order_date <= :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to,
        ];

        if ($rdc_id !== null) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = $rdc_id;
        }

        $sql .= "
        GROUP BY
            p.product_id,
            p.product_name,
            p.product_code,
            pc.name
        ORDER BY
            total_sales_count DESC,
            total_sales_amount DESC,
            p.product_name ASC
        LIMIT 10
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDailySalesByRdc(array $filters = []): array
    {
        if (empty($filters['start_date']) || empty($filters['end_date'])) {
            throw new InvalidArgumentException('start_date and end_date are required.');
        }

        $date_from = $filters['start_date'] . ' 00:00:00';
        $date_to = $filters['end_date'] . ' 23:59:59';

        $sql = "
        SELECT
            r.rdc_name,
            DATE(o.order_date) AS sales_date,
            COALESCE(SUM(o.total_amount), 0) AS total_sales
        FROM orders o
        JOIN rdcs r ON r.rdc_id = o.rdc_id
        WHERE o.order_date >= :date_from
          AND o.order_date <= :date_to
          AND o.status <> 'cancelled'
    ";

        $params = [
            ':date_from' => $date_from,
            ':date_to' => $date_to
        ];

        if (!empty($filters['rdc_id'])) {
            $sql .= " AND o.rdc_id = :rdc_id";
            $params[':rdc_id'] = (int) $filters['rdc_id'];
        }

        $sql .= "
        GROUP BY r.rdc_name, DATE(o.order_date)
        ORDER BY sales_date ASC, r.rdc_name ASC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>