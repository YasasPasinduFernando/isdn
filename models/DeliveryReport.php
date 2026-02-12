<?php

/**
 * DeliveryReport Model
 *
 * Provides optimized SQL aggregation for RDC-wise delivery efficiency metrics.
 *
 * Architecture:
 *   order_deliveries.order_id → orders.id → orders.rdc_id → rdcs.rdc_id
 *
 * Key Columns:
 *   order_deliveries.delivery_date  = scheduled delivery datetime
 *   order_deliveries.completed_date = actual completion datetime
 *   orders.created_at               = order placement timestamp
 *
 * Metrics Calculation:
 *   On-time    = completed_date IS NOT NULL AND completed_date <= delivery_date
 *   Delayed    = completed_date IS NOT NULL AND completed_date >  delivery_date
 *   Pending    = completed_date IS NULL
 *   Efficiency = (on_time / completed_total) × 100
 *   Avg Time   = AVG(TIMESTAMPDIFF(HOUR, orders.created_at, completed_date))
 *
 * Indexing Strategy (recommended):
 *   - order_deliveries(order_id)       → speeds up JOIN to orders
 *   - order_deliveries(completed_date) → speeds up status filtering
 *   - orders(rdc_id)                   → speeds up GROUP BY and RDC filtering
 *   - orders(created_at)               → speeds up date range filtering
 */
class DeliveryReport
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ================================================================
     * MAIN AGGREGATION: RDC-wise Delivery Efficiency
     * ================================================================
     *
     * Single query performs all aggregations per RDC using conditional
     * SUM(CASE ...) which avoids multiple round-trips to the database.
     * NULLIF prevents division-by-zero when calculating efficiency %.
     *
     * @param array $filters Optional keys: start_date, end_date, rdc_id, status
     * @return array Rows with rdc_id, rdc_name, metrics per RDC
     */
    public function getRdcEfficiency(array $filters = []): array
    {
        $conditions = [];
        $params     = [];

        // Date range filter on order creation date
        if (!empty($filters['start_date'])) {
            $conditions[]      = 'o.created_at >= :start_date';
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]    = 'o.created_at <= :end_date';
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        // RDC filter
        if (!empty($filters['rdc_id'])) {
            $conditions[]       = 'o.rdc_id = :rdc_id';
            $params['rdc_id']   = (int) $filters['rdc_id'];
        }

        // Delivery status filter
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'completed':
                    $conditions[] = 'od.completed_date IS NOT NULL';
                    break;
                case 'pending':
                    $conditions[] = 'od.completed_date IS NULL';
                    break;
                case 'on_time':
                    $conditions[] = 'od.completed_date IS NOT NULL AND od.completed_date <= od.delivery_date';
                    break;
                case 'delayed':
                    $conditions[] = 'od.completed_date IS NOT NULL AND od.completed_date > od.delivery_date';
                    break;
            }
        }

        $where = $conditions ? 'AND ' . implode(' AND ', $conditions) : '';

        /*
         * Optimization Notes:
         * 1. Single aggregation pass with conditional CASE expressions
         * 2. NULLIF(x, 0) prevents division-by-zero for efficiency %
         * 3. TIMESTAMPDIFF(HOUR, ...) gives meaningful avg delivery duration
         * 4. JOINs use indexed columns: od.order_id → o.id, o.rdc_id → r.rdc_id
         */
        $sql = "
            SELECT
                r.rdc_id,
                r.rdc_name,
                r.rdc_code,

                /* Total deliveries assigned to this RDC */
                COUNT(od.id) AS total_deliveries,

                /* Completed deliveries */
                SUM(CASE WHEN od.completed_date IS NOT NULL THEN 1 ELSE 0 END) AS completed,

                /* On-time: delivered before or at scheduled time */
                SUM(CASE
                    WHEN od.completed_date IS NOT NULL
                     AND od.completed_date <= od.delivery_date
                    THEN 1 ELSE 0
                END) AS on_time,

                /* Delayed: delivered after scheduled time */
                SUM(CASE
                    WHEN od.completed_date IS NOT NULL
                     AND od.completed_date > od.delivery_date
                    THEN 1 ELSE 0
                END) AS delayed,

                /* Still pending */
                SUM(CASE WHEN od.completed_date IS NULL THEN 1 ELSE 0 END) AS pending,

                /* Efficiency % = (on-time / completed) × 100 */
                ROUND(
                    SUM(CASE
                        WHEN od.completed_date IS NOT NULL
                         AND od.completed_date <= od.delivery_date
                        THEN 1 ELSE 0
                    END) * 100.0
                    / NULLIF(
                        SUM(CASE WHEN od.completed_date IS NOT NULL THEN 1 ELSE 0 END),
                        0
                    ),
                    1
                ) AS efficiency_pct,

                /* Average delivery duration: order placement → completion (hours) */
                ROUND(AVG(
                    CASE WHEN od.completed_date IS NOT NULL
                         THEN TIMESTAMPDIFF(HOUR, o.created_at, od.completed_date)
                    END
                ), 1) AS avg_delivery_hours

            FROM order_deliveries od
            JOIN orders o ON od.order_id = o.id
            JOIN rdcs   r ON o.rdc_id    = r.rdc_id
            WHERE 1=1 {$where}
            GROUP BY r.rdc_id, r.rdc_name, r.rdc_code
            ORDER BY efficiency_pct DESC, total_deliveries DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get overall summary (totals across all RDCs) for KPI cards.
     */
    public function getOverallSummary(array $filters = []): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['start_date'])) {
            $conditions[]         = 'o.created_at >= :start_date';
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]       = 'o.created_at <= :end_date';
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $conditions[]     = 'o.rdc_id = :rdc_id';
            $params['rdc_id'] = (int) $filters['rdc_id'];
        }

        $where = $conditions ? 'AND ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT
                COUNT(od.id) AS total_deliveries,
                SUM(CASE WHEN od.completed_date IS NOT NULL THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN od.completed_date IS NOT NULL AND od.completed_date <= od.delivery_date THEN 1 ELSE 0 END) AS on_time,
                SUM(CASE WHEN od.completed_date IS NOT NULL AND od.completed_date > od.delivery_date THEN 1 ELSE 0 END) AS delayed,
                SUM(CASE WHEN od.completed_date IS NULL THEN 1 ELSE 0 END) AS pending,
                ROUND(
                    SUM(CASE WHEN od.completed_date IS NOT NULL AND od.completed_date <= od.delivery_date THEN 1 ELSE 0 END) * 100.0
                    / NULLIF(SUM(CASE WHEN od.completed_date IS NOT NULL THEN 1 ELSE 0 END), 0),
                    1
                ) AS overall_efficiency,
                ROUND(AVG(
                    CASE WHEN od.completed_date IS NOT NULL
                         THEN TIMESTAMPDIFF(HOUR, o.created_at, od.completed_date)
                    END
                ), 1) AS avg_hours
            FROM order_deliveries od
            JOIN orders o ON od.order_id = o.id
            WHERE 1=1 {$where}
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_deliveries' => 0, 'completed' => 0, 'on_time' => 0,
            'delayed' => 0, 'pending' => 0, 'overall_efficiency' => 0, 'avg_hours' => 0,
        ];
    }

    /**
     * Get detailed delivery records (for drill-down or extended table).
     */
    public function getDeliveryDetails(array $filters = [], int $limit = 50): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['start_date'])) {
            $conditions[]         = 'o.created_at >= :start_date';
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $conditions[]       = 'o.created_at <= :end_date';
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        if (!empty($filters['rdc_id'])) {
            $conditions[]     = 'o.rdc_id = :rdc_id';
            $params['rdc_id'] = (int) $filters['rdc_id'];
        }
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'completed': $conditions[] = 'od.completed_date IS NOT NULL'; break;
                case 'pending':   $conditions[] = 'od.completed_date IS NULL';     break;
                case 'on_time':   $conditions[] = 'od.completed_date IS NOT NULL AND od.completed_date <= od.delivery_date'; break;
                case 'delayed':   $conditions[] = 'od.completed_date IS NOT NULL AND od.completed_date > od.delivery_date';  break;
            }
        }

        $where = $conditions ? 'AND ' . implode(' AND ', $conditions) : '';

        $sql = "
            SELECT od.id AS delivery_id, o.order_number, r.rdc_name,
                   u.username AS driver_name,
                   od.delivery_date AS scheduled_date,
                   od.completed_date,
                   CASE
                       WHEN od.completed_date IS NULL THEN 'Pending'
                       WHEN od.completed_date <= od.delivery_date THEN 'On-time'
                       ELSE 'Delayed'
                   END AS delivery_status,
                   CASE
                       WHEN od.completed_date IS NOT NULL
                       THEN TIMESTAMPDIFF(HOUR, o.created_at, od.completed_date)
                       ELSE NULL
                   END AS duration_hours
            FROM order_deliveries od
            JOIN orders o  ON od.order_id  = o.id
            JOIN rdcs   r  ON o.rdc_id     = r.rdc_id
            LEFT JOIN users u ON od.driver_id = u.id
            WHERE 1=1 {$where}
            ORDER BY od.delivery_date DESC
            LIMIT :lim
        ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * All RDCs for filter dropdown.
     */
    public function getAllRdcs(): array
    {
        return $this->pdo->query("SELECT rdc_id, rdc_name, rdc_code FROM rdcs ORDER BY rdc_name")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ensure optimal indexes exist for the report queries.
     * Safe to call multiple times (IF NOT EXISTS).
     */
    public function ensureIndexes(): void
    {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_od_order_id ON order_deliveries(order_id)",
            "CREATE INDEX IF NOT EXISTS idx_od_completed ON order_deliveries(completed_date)",
            "CREATE INDEX IF NOT EXISTS idx_orders_rdc ON orders(rdc_id)",
            "CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at)",
        ];
        foreach ($indexes as $sql) {
            try { $this->pdo->exec($sql); } catch (PDOException $e) { /* index may already exist */ }
        }
    }
}
