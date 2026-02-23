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
        $sql = "SELECT sml.movement_id, sml.product_id, p.product_code, p.product_name,
                       sml.movement_type AS movement_type, sml.quantity, sml.previous_quantity,
                       sml.new_quantity, sml.created_at AS date, sml.created_by_name AS created_by_name,
                       sml.created_by_role AS created_by_role, sml.note
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
