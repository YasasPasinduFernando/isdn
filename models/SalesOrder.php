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
                $total += $item['unit_price'] * $item['quantity'];
            }

            // Insert order
            $orderStmt = $this->pdo->prepare("
                INSERT INTO orders 
                (date, customer_id, placed_by, amount, status, order_number, last_updated, estimated_date)
                VALUES 
                (NOW(), :customer_id, :placed_by, :amount, :status, :order_number ,NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY))
            ");

            $status = 'PENDING_RDC_CLERK';
            $orderNumber = 'ORD' .'-'. 'RDC-'.date('ymd') .'-'. rand(100, 99999);

            $orderStmt->execute([
                'customer_id' => $customerId,
                'placed_by'   => $placedBy,
                'amount'      => $total,
                'status'      => $status,
                'order_number'=> $orderNumber,
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
                    'quantity'  => $item['quantity'],
                    'selling_price'=> $item['unit_price'],
                    'discount'=> 0
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
    public function getUserOrders($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                *
            FROM orders
            WHERE customer_id = :customer_id
        ");
        $stmt->execute(['customer_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>