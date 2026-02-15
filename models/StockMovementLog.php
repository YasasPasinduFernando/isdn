<?php
class StockMovementLog
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Recent stock movements for RDC
     * returns product, type, quantity, date, user
     */
    public function getRecentMovementsByRdc(int $rdcId, int $limit = 6): array
    {
        $sql = "SELECT p.product_name AS product, sml.movement_type AS type,
                       sml.quantity, sml.created_at AS date, sml.created_by_name AS user
                FROM stock_movement_logs sml
                LEFT JOIN products p ON sml.product_id = p.product_id
                WHERE sml.rdc_id = :rdc_id
                ORDER BY sml.created_at DESC
                LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':rdc_id', $rdcId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
