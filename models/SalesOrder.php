<?php
class SalesOrder
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    public function placeOrder(int $customerId, int $placedBy, array $items)
    {
        $taxPercentage = 15.0;
        $deliveryCharges = 1450.0;

        try {
            $this->pdo->beginTransaction();

            $subTotal = 0.0;        // sum of lineAmount
            $discountTotal = 0.0;   // sum of discount amounts

            foreach ($items as $item) {
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);

                if ($qty <= 0 || $unitPrice <= 0) {
                    continue; // skip invalid rows (or throw exception if you prefer)
                }

                $lineAmount = $unitPrice * $qty;
                $subTotal += $lineAmount;

                $isPromo = !empty($item['is_promotional']); // accepts 1/0, true/false, "1"
                $discountP = (float) ($item['discount_percentage'] ?? 0);

                if ($isPromo && $discountP > 0) {
                    $discountTotal += $lineAmount * ($discountP / 100);
                }
            }

            // Amount after discount
            $netTotal = $subTotal - $discountTotal;

            // Tax only on net (common approach). If you want tax on subtotal, change $netTotal -> $subTotal
            $taxAmount = $netTotal * ($taxPercentage / 100);

            $grandTotal = $netTotal + $taxAmount + $deliveryCharges;

            // round currency values to 2 decimals
            $subTotal = round($subTotal, 2);
            $discountTotal = round($discountTotal, 2);
            $netTotal = round($netTotal, 2);
            $taxAmount = round($taxAmount, 2);
            $grandTotal = round($grandTotal, 2);
            $orderStmt = $this->pdo->prepare("
                INSERT INTO orders 
                (order_date, customer_id, placed_by, total_amount, status, order_number, estimated_date, rdc_id)
                VALUES 
                (NOW(), :customer_id, :placed_by, :amount, :status, :order_number , DATE_ADD(NOW(), INTERVAL 2 DAY), :rdc_id)
            ");

            $status = 'Pending';
            $orderNumber = 'ORD' . '-' . 'RDC-' . date('ymd') . '-' . rand(100, 99999);

            $orderStmt->execute([
                'customer_id' => $customerId,
                'placed_by' => $placedBy,
                'amount' => $grandTotal,
                'status' => $status,
                'order_number' => $orderNumber,
                'rdc_id' => $_SESSION['rdc_id'],
            ]);

            $orderId = $this->pdo->lastInsertId();

            // Insert order items
            $itemStmt = $this->pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, selling_price, discount)
                VALUES (:order_id, :product_id, :quantity, :selling_price, :discount)
            ");

            foreach ($items as $item) {
                $isPromo = $item['is_promotional'];
                $discount = 0;
                if ($isPromo)
                    $discount = $item['discount_percentage'] ?? 0;
                $item["discount_percentage"];
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['unit_price'],
                    'discount' => $discount
                ]);
            }

            // Clear shopping cart
            $clearStmt = $this->pdo->prepare(
                "DELETE FROM shopping_carts WHERE user_id = :user_id"
            );
            $clearStmt->execute(['user_id' => $customerId]);

            $this->pdo->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function getUserOrders($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                *
            FROM orders
            WHERE customer_id = :customer_id
        ");
        $stmt->execute(['customer_id' => 1]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderbyId($orderId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                *
            FROM
                `orders` o
                    INNER JOIN
                retail_customers rc ON o.customer_id = rc.id
            WHERE
                o.id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
}
?>