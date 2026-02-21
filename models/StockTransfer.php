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

    /**
     * Get detailed pending transfers for a destination RDC including items and stock at source/destination
     */
    public function getPendingTransfersDetailedForRdc(int $rdcId, int $limit = 10): array
    {
        // First fetch transfers
        $sql = "SELECT st.transfer_id, st.transfer_number, st.is_urgent, st.approval_status AS status, st.requested_date,
                       r1.rdc_name AS source_rdc, r2.rdc_name AS destination_rdc,
                       u.username AS requested_by_name, st.requested_by_role, st.request_reason
                FROM stock_transfers st
                JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
                JOIN rdcs r2 ON st.destination_rdc_id = r2.rdc_id
                JOIN users u ON st.requested_by = u.id
                WHERE st.destination_rdc_id = :rdc_id
                  AND st.approval_status IN ('PENDING', 'CLERK_REQUESTED')
                ORDER BY st.is_urgent DESC, st.requested_date DESC
                LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':rdc_id', $rdcId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($transfers)) return [];

        // Load items for these transfers
        $transferIds = array_column($transfers, 'transfer_id');
        $in = implode(',', array_fill(0, count($transferIds), '?'));
       
        $sqlItems = "SELECT sti.transfer_id, sti.item_id, sti.product_id, sti.requested_quantity,
                            p.product_name, p.product_code, pc.name AS category,
                            COALESCE(ps_src.available_quantity, 0) AS source_stock,
                            COALESCE(ps_dst.available_quantity, 0) AS destination_stock
                     FROM stock_transfer_items sti
                     JOIN products p ON sti.product_id = p.product_id
                     LEFT JOIN product_categories pc ON p.category_id = pc.category_id
                     LEFT JOIN product_stocks ps_src ON ps_src.product_id = p.product_id AND ps_src.rdc_id = (
                         SELECT source_rdc_id FROM stock_transfers WHERE transfer_id = sti.transfer_id
                     )
                     LEFT JOIN product_stocks ps_dst ON ps_dst.product_id = p.product_id AND ps_dst.rdc_id = ?
                     WHERE sti.transfer_id IN ($in)";

        $stmt2 = $this->pdo->prepare($sqlItems);
        $i = 1;

            // First bind rdc_id (because its ? appears first in SQL)
            $stmt2->bindValue($i, $rdcId, PDO::PARAM_INT);
            $i++;

            // Then bind transfer IDs
            foreach ($transferIds as $id) {
                $stmt2->bindValue($i, $id, PDO::PARAM_INT);
                $i++;
            }
        $stmt2->execute();
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // group items by transfer_id
        $grouped = [];
        foreach ($items as $it) {
            $tid = $it['transfer_id'];
            if (!isset($grouped[$tid])) $grouped[$tid] = [];
            $grouped[$tid][] = $it;
        }

        // Attach items and compute counts
        $out = [];
        foreach ($transfers as $t) {
            $tid = $t['transfer_id'];
            $titems = $grouped[$tid] ?? [];
            $product_count = count($titems);
            $total_items = array_sum(array_map(fn($x) => (int)$x['requested_quantity'], $titems));

            // Normalize item fields for view
            $normItems = array_map(function ($it) {
                return [
                    'item_id' => (int)$it['item_id'],
                    'product_id' => (int)$it['product_id'],
                    'product_code' => $it['product_code'],
                    'product_name' => $it['product_name'],
                    'category' => $it['category'] ?? 'Uncategorized',
                    'requested_quantity' => (int)$it['requested_quantity'],
                    'source_stock' => (int)$it['source_stock'],
                    'destination_stock' => (int)$it['destination_stock']
                ];
            }, $titems);

            $out[] = array_merge($t, [
                'items' => $normItems,
                'product_count' => $product_count,
                'total_items' => $total_items
            ]);
        }

        return $out;
    }

    /**
     * Get detailed pending transfers where the provided RDC is the SOURCE RDC.
     * This returns transfers initiated from the given RDC (source_rdc_id = :rdc_id)
     * and includes item details plus stock at source (this RDC) and destination.
     */
    public function getPendingOutgoingTransfersForSourceRdc(int $rdcId, int $limit = 10): array
    {
        // First fetch transfers where current RDC is the source
        $sql = "SELECT st.transfer_id, st.transfer_number, st.is_urgent, st.approval_status AS status, st.requested_date,
                       r1.rdc_name AS source_rdc, r2.rdc_name AS destination_rdc,
                       u.username AS requested_by_name, st.requested_by_role, st.request_reason, st.destination_rdc_id
                FROM stock_transfers st
                JOIN rdcs r1 ON st.source_rdc_id = r1.rdc_id
                JOIN rdcs r2 ON st.destination_rdc_id = r2.rdc_id
                JOIN users u ON st.requested_by = u.id
                WHERE st.source_rdc_id = :rdc_id
                  AND st.approval_status IN ('PENDING', 'CLERK_REQUESTED')
                ORDER BY st.is_urgent DESC, st.requested_date DESC
                LIMIT :lim";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':rdc_id', $rdcId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $transfers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($transfers)) return [];

        // Load items for these transfers
        $transferIds = array_column($transfers, 'transfer_id');
        $in = implode(',', array_fill(0, count($transferIds), '?'));

        $sqlItems = "SELECT sti.transfer_id, sti.item_id, sti.product_id, sti.requested_quantity,
                            p.product_name, p.product_code, p.unit_price, pc.name AS category,
                            COALESCE(ps_src.available_quantity, 0) AS source_stock,
                            COALESCE(ps_dst.available_quantity, 0) AS destination_stock
                     FROM stock_transfer_items sti
                     JOIN products p ON sti.product_id = p.product_id
                     LEFT JOIN product_categories pc ON p.category_id = pc.category_id
                     LEFT JOIN product_stocks ps_src ON ps_src.product_id = p.product_id AND ps_src.rdc_id = ?
                     LEFT JOIN product_stocks ps_dst ON ps_dst.product_id = p.product_id AND ps_dst.rdc_id = (
                         SELECT destination_rdc_id FROM stock_transfers WHERE transfer_id = sti.transfer_id
                     )
                     WHERE sti.transfer_id IN ($in)";

        $stmt2 = $this->pdo->prepare($sqlItems);
        $i = 1;

        // bind source rdc id first (this RDC)
        $stmt2->bindValue($i, $rdcId, PDO::PARAM_INT);
        $i++;

        // Then bind transfer IDs
        foreach ($transferIds as $id) {
            $stmt2->bindValue($i, $id, PDO::PARAM_INT);
            $i++;
        }
        $stmt2->execute();
        $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // group items by transfer_id
        $grouped = [];
        foreach ($items as $it) {
            $tid = $it['transfer_id'];
            if (!isset($grouped[$tid])) $grouped[$tid] = [];
            $grouped[$tid][] = $it;
        }

        // Attach items and compute counts
        $out = [];
        foreach ($transfers as $t) {
            $tid = $t['transfer_id'];
            $titems = $grouped[$tid] ?? [];
            $product_count = count($titems);
            $total_items = array_sum(array_map(fn($x) => (int)$x['requested_quantity'], $titems));

            $normItems = array_map(function ($it) {
                return [
                    'item_id' => (int)$it['item_id'],
                    'product_id' => (int)$it['product_id'],
                    'product_code' => $it['product_code'],
                    'product_name' => $it['product_name'],
                    'category' => $it['category'] ?? 'Uncategorized',
                    'requested_quantity' => (int)$it['requested_quantity'],
                    'source_stock' => (int)$it['source_stock'],
                    'destination_stock' => (int)$it['destination_stock'],
                    'unit_price' => (float)$it['unit_price']
                ];
            }, $titems);

            $out[] = array_merge($t, [
                'items' => $normItems,
                'product_count' => $product_count,
                'total_items' => $total_items
            ]);
        }

        return $out;
    }
}

?>
