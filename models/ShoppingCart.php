<?php
class ShoppingCart {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Load cart with product data
    public function getUserCart($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                sc.id AS cart_id,
                sc.product_id,
                sc.quantity,
                p.product_name,
                p.unit_price
            FROM shopping_carts sc
            JOIN products p ON p.product_id = sc.product_id
            WHERE sc.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addToCart($userId, $productId, $qty) {

        // Check if product already exists in cart
        $checkStmt = $this->pdo->prepare(
            "SELECT id, quantity FROM shopping_carts 
             WHERE user_id = :user_id AND product_id = :product_id"
        );
        $checkStmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update quantity
            $updateStmt = $this->pdo->prepare(
                "UPDATE shopping_carts 
                 SET quantity = quantity + :qty 
                 WHERE id = :id"
            );
            return $updateStmt->execute([
                'qty' => $qty,
                'id'  => $existing['id']
            ]);
        } else {
            // Insert new row
            $insertStmt = $this->pdo->prepare(
                "INSERT INTO shopping_carts (user_id, product_id, quantity)
                 VALUES (:user_id, :product_id, :quantity)"
            );
            return $insertStmt->execute([
                'user_id'   => $userId,
                'product_id'=> $productId,
                'quantity'  => $qty
            ]);
        }
    }

    public function updateQty($userId, $productId, $qty) {
        if ($qty <= 0) {
            return $this->removeItem($userId, $productId);
        }

        $stmt = $this->pdo->prepare("
            UPDATE shopping_carts 
            SET quantity = :qty
            WHERE user_id = :user_id AND product_id = :product_id
        ");
        return $stmt->execute([
            'qty' => $qty,
            'user_id' => $userId,
            'product_id' => $productId
        ]);
    }

    public function removeItem($userId, $productId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM shopping_carts 
            WHERE user_id = :user_id AND product_id = :product_id
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
    }

    public function clearCart($userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM shopping_carts WHERE user_id = :user_id
        ");
        return $stmt->execute(['user_id' => $userId]);
    }
}

?>
