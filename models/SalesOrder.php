<?php
class SalesOrder {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function placeOrder(int $customerId, int $placedBy, array $items) {

        try {
            $this->pdo->beginTransaction();

            // Calculate order total
            $total = 0;
            foreach ($items as $item) {
                $total += $item['price'] * $item['qty'];
            }

            // Insert order
            $orderStmt = $this->pdo->prepare("
                INSERT INTO orders 
                (date, customer_id, placed_by, amount, status, last_updated, estimated_date)
                VALUES 
                (NOW(), :customer_id, :placed_by, :amount, 'PENDING_RDC_CLERK', NOW(), NOW())
            ");

            $orderNumber = 'ORD' .'-'. 'RDC'.date('ymd') . rand(100, 999);

            $orderStmt->execute([
                'customer_id' => $customerId,
                'placed_by'   => $placedBy,
                'amount'      => $total,
                'status'      => $total,
                'estimated_date'=> $orderNumber,
            ]);

            $orderId = $this->pdo->lastInsertId();

            // Insert order items
            $itemStmt = $this->pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, selling_price, discount)
                VALUES (:order_id, :product_id, :quantity, :selling_price, :discount)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id'   => $orderId,
                    'product_id'=> $item['product_id'],
                    'quantity'  => $item['qty'],
                    'selling_price'=> $item['price'],
                    'discount'=> $item['price']
                ]);
            }

            // Clear shopping cart
            $clearStmt = $this->pdo->prepare(
                "DELETE FROM shopping_cart WHERE user_id = :user_id"
            );
            $clearStmt->execute(['user_id' => $customerId]);

            $this->pdo->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>