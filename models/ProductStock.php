<?php
class ProductStock
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get product stocks for a specific RDC
     * Returns array with keys: product_code, product_name, category, current_stock, minimum_level, unit_price
     */
    public function getStocksByRdc(int $rdcId): array
    {
        $sql = "SELECT p.product_code, p.product_name, pc.name AS category, 
                       COALESCE(ps.available_quantity, 0) AS current_stock, 
                       COALESCE(p.minimum_stock_level, 0) AS minimum_level,
                       COALESCE(p.unit_price, 0) AS unit_price
                FROM product_stocks ps
                JOIN products p ON ps.product_id = p.product_id
                LEFT JOIN product_categories pc ON p.category_id = pc.category_id
                WHERE ps.rdc_id = :rdc_id
                ORDER BY p.product_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['rdc_id' => $rdcId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $status = 'OK';
            $min = (int)$r['minimum_level'];
            $cur = (int)$r['current_stock'];
            if ($min > 0 && $cur < $min) {
                // Mark very low as CRITICAL, otherwise LOW
                $status = $cur <= max(1, (int)floor($min * 0.5)) ? 'CRITICAL' : 'LOW';
            }

            $out[] = [
                'product_code'    => $r['product_code'],
                'product_name'    => $r['product_name'],
                'category'        => $r['category'] ?? 'Uncategorized',
                'current_stock'   => (int)$r['current_stock'],
                'minimum_level'   => (int)$r['minimum_level'],
                'unit_price'      => (float)$r['unit_price'],
                'status'          => $status
            ];
        }

        return $out;
    }
}

?>
