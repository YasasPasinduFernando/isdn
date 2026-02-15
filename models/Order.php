<?php
class OrderModel
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get recent orders for an RDC
     * returns order_number, customer, total, status, date
     */
    public function getRecentOrdersByRdc(int $rdcId, int $limit = 5): array
    {
        $sql = "SELECT 
            o.order_number, 
            rc.name AS customer, 
            o.total_amount AS total, 
            o.status, 
            o.created_at AS date
        FROM orders o
        LEFT JOIN retail_customers rc ON o.customer_id = rc.id
        LEFT JOIN users u ON rc.user_id = u.id
        WHERE u.rdc_id = :rdc_id
        ORDER BY o.created_at DESC
        LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':rdc_id', $rdcId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
