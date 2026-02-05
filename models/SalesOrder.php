<?php
class SalesOrder {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* ===========================
       Fetch Orders
    ============================ */

    public function findAllOrders() {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM orders
            ORDER BY date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM orders
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByCustomer(int $customerId) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM orders
            WHERE customer_id = :customer_id
            ORDER BY date DESC
        ");
        $stmt->execute(['customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===========================
       Create Order
    ============================ */

    public function createOrder(
        int $customerId,
        int $placedBy,
        float $amount,
        string $status = 'PENDING'
    ) {
        $stmt = $this->pdo->prepare("
            INSERT INTO orders 
            (date, customer_id, placed_by, amount, status, order_number, last_updated)
            VALUES 
            (NOW(), :customer_id, :placed_by, :amount, :status, :order_number, NOW())
        ");

        return $stmt->execute([
            'customer_id'  => $customerId,
            'placed_by'    => $placedBy,
            'amount'       => $amount,
            'status'       => $status,
            'order_number' => $this->generateOrderNumber()
        ]);
    }

    /* ===========================
       Update Operations
    ============================ */

    public function updateStatus(int $orderId, string $status) {
        $stmt = $this->pdo->prepare("
            UPDATE orders
            SET status = :status,
                last_updated = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'status' => $status,
            'id'     => $orderId
        ]);
    }

    public function updateEstimatedDate(int $orderId, string $date) {
        $stmt = $this->pdo->prepare("
            UPDATE orders
            SET estimated_date = :estimated_date,
                last_updated = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'estimated_date' => $date,
            'id'             => $orderId
        ]);
    }

    /* ===========================
       Delete Order (optional)
    ============================ */

    public function deleteOrder(int $orderId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM orders WHERE id = :id
        ");
        return $stmt->execute(['id' => $orderId]);
    }

    /* ===========================
       Helpers
    ============================ */

    private function generateOrderNumber(): string {
        return 'ORD' . date('ymd') . rand(100, 999);
    }
}
?>
