<?php
class StockTransfer
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get pending transfers for a destination RDC
     * returns transfer_number, source_rdc, items, status, is_urgent, requested_date
     */
    public function getPendingTransfersForRdc(int $rdcId, int $limit = 10): array
    {
        $sql = "SELECT st.transfer_id, st.transfer_number, st.is_urgent, st.approval_status AS status, st.requested_date,
                       r1.rdc_name AS source_rdc,
                       COALESCE(COUNT(sti.item_id), 0) AS items
                FROM stock_transfers st
                JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
                LEFT JOIN stock_transfer_items sti ON st.transfer_id = sti.transfer_id
                WHERE st.destination_rdc_id = :rdc_id
                  AND st.approval_status IN ('PENDING', 'CLERK_REQUESTED')
                GROUP BY st.transfer_id
                ORDER BY st.is_urgent DESC, st.requested_date DESC
                LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':rdc_id', $rdcId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
