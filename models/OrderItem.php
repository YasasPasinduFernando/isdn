<?php
class OrderItem
{
    private PDO $pdo;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getOrderItems($orderId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.product_code,
                p.product_name,
                pc.name AS product_category,
                p.unit_price,
                oi.quantity,
                oi.discount,
                ROUND(p.unit_price * oi.quantity, 2) AS line_amount,
                ROUND((p.unit_price * oi.quantity) * (oi.discount / 100),
                        2) AS discount_amount,
                ROUND((p.unit_price * oi.quantity) * (1 - (oi.discount / 100)),
                        2) AS discounted_line_amount
            FROM
                order_items oi
                    JOIN
                products p ON p.product_id = oi.product_id
                    JOIN
                product_categories pc ON pc.category_id = p.category_id
            WHERE
                oi.order_id = :order_id;
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateOrderTotals(int $orderId): array
    {
        $items = $this->getOrderItems($orderId);

        $subtotal = 0.0;
        $discountTotal = 0.0;
        $discountedTotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) $item['line_amount'];
            $discountTotal += (float) $item['discount_amount'];
            $discountedTotal += (float) $item['discounted_line_amount'];
        }

        $taxPercentage = 15;
        $deliveryFee = 1450.00;

        $taxAmount = ($discountedTotal * $taxPercentage) / 100;

        $grandTotal = $discountedTotal + $taxAmount + $deliveryFee;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'grand_total' => round($grandTotal, 2)
        ];
    }
}
?>