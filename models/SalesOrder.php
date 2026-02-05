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
                (date, customer_id, placed_by, amount, status, order_number, last_updated)
                VALUES 
                (NOW(), :customer_id, :placed_by, :amount, 'PENDING', :order_number, NOW())
            ");

            $orderNumber = 'ORD' . date('ymd') . rand(100, 999);

            $orderStmt->execute([
                'customer_id' => $customerId,
                'placed_by'   => $placedBy,
                'amount'      => $total,
                'order_number'=> $orderNumber
            ]);

            $orderId = $this->pdo->lastInsertId();

            // Insert order items
            $itemStmt = $this->pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (:order_id, :product_id, :quantity, :unit_price)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id'   => $orderId,
                    'product_id'=> $item['product_id'],
                    'quantity'  => $item['qty'],
                    'unit_price'=> $item['price']
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