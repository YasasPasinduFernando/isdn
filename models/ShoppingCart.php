<?php
class ShoppingCart
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Load cart with product data

    public function getUserCart($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
            pc.category_id,
            pc.name AS category_name,
            p.product_id,
            p.product_code,
            p.product_name,
            p.unit_price,
            p.image_url,
            p.description,
            pr.name AS promotion_name,
            pr.description AS promotion_description,
            pr.product_count,
            pr.discount_percentage,
            sc.id AS cart_id,
            sc.quantity AS quantity,
            ROUND((p.unit_price * sc.quantity), 2) AS line_amount,
            CASE
                WHEN
                    pr.id IS NOT NULL
                        AND pr.product_count <= sc.quantity
                THEN
                    1
                ELSE 0
            END AS is_promotional,
            CASE
                WHEN
                    pr.id IS NOT NULL
                        AND pr.product_count <= sc.quantity
                THEN
                    ROUND((p.unit_price * sc.quantity) - ((p.unit_price * sc.quantity) * pr.discount_percentage / 100),
                            2)
                ELSE (p.unit_price * sc.quantity)
            END AS discounted_line_amount
        FROM
            shopping_carts sc
                INNER JOIN
            products p ON p.product_id = sc.product_id
                AND p.is_active = 1
                INNER JOIN
            product_categories pc ON pc.category_id = p.category_id
                LEFT JOIN
            promotions pr ON pr.product_id = p.product_id
                AND pr.is_active = 1
                AND CURDATE() BETWEEN pr.start_date AND pr.end_date
        WHERE
            sc.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserCartCount($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(id) AS product_count
            FROM
                isdn_db3.shopping_carts
            WHERE
                user_id = :user_id
            GROUP BY user_id; 
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserCartAmount($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                ROUND(SUM(
                    CASE
                        WHEN pr.id IS NOT NULL 
                            AND sc.quantity >= pr.product_count
                        THEN (p.unit_price * sc.quantity) * 
                            (1 - pr.discount_percentage / 100)
                        ELSE (p.unit_price * sc.quantity)
                    END
                ), 2) AS cart_total
            FROM shopping_carts sc
            JOIN products p 
                ON p.product_id = sc.product_id
                AND p.is_active = 1
            LEFT JOIN promotions pr 
                ON pr.product_id = p.product_id
                AND pr.is_active = 1
                AND CURDATE() BETWEEN pr.start_date AND pr.end_date
            WHERE sc.user_id = :user_id;
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function addToCart($userId, $productId, $qty)
    {

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
                'id' => $existing['id']
            ]);
        } else {
            // Insert new row
            $insertStmt = $this->pdo->prepare(
                "INSERT INTO shopping_carts (user_id, product_id, quantity)
                 VALUES (:user_id, :product_id, :quantity)"
            );
            //$product_count = $this->getUserCartCount($userId);
            return $insertStmt->execute([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $qty,
            ]);
        }
    }

    public function updateQty($userId, $productId, $qty)
    {
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

    public function removeItem($userId, $productId)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM shopping_carts 
            WHERE user_id = :user_id AND product_id = :product_id
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
    }

    public function clearCart($userId)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM shopping_carts WHERE user_id = :user_id
        ");
        return $stmt->execute(['user_id' => $userId]);
    }
}

?>