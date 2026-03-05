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

    public function updateOrderStatus(int $orderId, string $orderStatus): bool
    {
        $orderStatus = strtolower(trim($orderStatus));

        // Normalize common variants coming from UI
        $orderStatus = str_replace('_', ' ', $orderStatus); // in_transit -> in transit

        $allowed = ['pending', 'confirmed', 'processing', 'in transit', 'delivered', 'cancelled'];
        if (!in_array($orderStatus, $allowed, true)) {
            throw new InvalidArgumentException("Invalid order status: {$orderStatus}");
        }

        $startedTxn = false;

        try {
            // Start transaction only if not already inside one
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $startedTxn = true;
            }

            $stmt = $this->pdo->prepare("
            UPDATE orders
            SET status = :status
            WHERE id = :order_id
            LIMIT 1
        ");

            $stmt->execute([
                ':status' => $orderStatus,
                ':order_id' => $orderId,
            ]);

            if ($startedTxn) {
                $this->pdo->commit();
            }

            // Note: rowCount() can be 0 if status is unchanged even though order exists.
            return $stmt->rowCount() > 0;

        } catch (Throwable $e) {
            // Roll back only if we started the transaction and it’s still active
            if ($startedTxn && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }


    public function getCustomerOrders($customer_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                o.id AS order_id,
                o.order_number,
                DATE_FORMAT(o.order_date, '%Y-%m-%d') AS order_date,
                o.total_amount,
                o.status,
                o.estimated_date,
                rc.name AS customer,
                COALESCE(pay.total_paid, 0) AS total_paid,
                CASE
                    WHEN COALESCE(pay.total_paid, 0) > 0 THEN 'PAID'
                    ELSE 'UNPAID'
                END AS payment_status,
                COALESCE(oi.item_count, 0) AS item_count
            FROM
                orders o
                    JOIN
                retail_customers rc ON rc.id = o.customer_id
                    LEFT JOIN
                (SELECT 
                    order_id, SUM(amount) AS total_paid
                FROM
                    payments
                GROUP BY order_id) pay ON pay.order_id = o.id
                    LEFT JOIN
                (SELECT 
                    order_id, COUNT(product_id) AS item_count
                FROM
                    order_items
                GROUP BY order_id) oi ON oi.order_id = o.id
            WHERE
                o.customer_id = :customer_id
            ORDER BY o.order_date DESC;
        ");
        $stmt->execute(['customer_id' => $customer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesRepOrders($rep_user_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                o.id AS order_id,
                o.order_number,
                DATE_FORMAT(o.order_date, '%Y-%m-%d') AS order_date,
                o.total_amount,
                o.status,
                o.estimated_date,
                rc.name AS customer,
                rsr.name AS sales_rep,
                COALESCE(pay.total_paid, 0) AS total_paid,
                CASE
                    WHEN COALESCE(pay.total_paid, 0) > 0 THEN 'PAID'
                    ELSE 'UNPAID'
                END AS payment_status,
                COALESCE(oi.item_count, 0) AS item_count
            FROM
                orders o
                    JOIN
                retail_customers rc ON rc.id = o.customer_id
                    JOIN
                rdc_sales_refs rsr ON rsr.user_id = o.placed_by
                    LEFT JOIN
                (SELECT 
                    order_id, SUM(amount) AS total_paid
                FROM
                    payments
                GROUP BY order_id) pay ON pay.order_id = o.id
                    LEFT JOIN
                (SELECT 
                    order_id, COUNT(product_id) AS item_count
                FROM
                    order_items
                GROUP BY order_id) oi ON oi.order_id = o.id
            WHERE
                rsr.user_id = :rep_user_id
            ORDER BY o.order_date DESC;
        ");
        $stmt->execute(['rep_user_id' => $rep_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRdcOrders(int $rdc_id, ?int $limit = null): array
    {
        $sql = "
        SELECT 
            o.id AS order_id,
            o.order_number,
            DATE_FORMAT(o.order_date, '%Y-%m-%d') AS order_date,
            o.total_amount,
            o.status,
            DATE_FORMAT(o.estimated_date, '%Y-%m-%d') AS estimated_date,
            rc.name AS customer,
            COALESCE(sr.name, 'N/A') AS sales_rep,
            COALESCE(pay.total_paid, 0) AS total_paid,
            CASE
                WHEN COALESCE(pay.total_paid, 0) > 0 THEN 'PAID'
                ELSE 'UNPAID'
            END AS payment_status,
            COALESCE(oi.item_count, 0) AS item_count
        FROM orders o
            JOIN retail_customers rc ON rc.id = o.customer_id
            LEFT JOIN rdc_sales_refs sr ON sr.user_id = o.placed_by
            LEFT JOIN (
                SELECT order_id, SUM(amount) AS total_paid
                FROM payments
                GROUP BY order_id
            ) pay ON pay.order_id = o.id
            LEFT JOIN (
                SELECT order_id, COUNT(product_id) AS item_count
                FROM order_items
                GROUP BY order_id
            ) oi ON oi.order_id = o.id
        WHERE o.rdc_id = :rdc_id
        ORDER BY o.order_date DESC
    ";

        // Add LIMIT only if provided
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':rdc_id', $rdc_id, PDO::PARAM_INT);

        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderbyId($orderId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                o.id,
                o.order_date,
                o.order_number,
                o.total_amount,
                o.status,
                o.estimated_date,
                o.updated_at,
                rc.name AS customer,
                rc.address,
                COALESCE(sr.name, 'N/A') AS sales_ref
            FROM orders o
            JOIN retail_customers rc 
                ON rc.id = o.customer_id
            LEFT JOIN rdc_sales_refs sr 
                ON sr.user_id = o.placed_by
            WHERE o.id = :order_id
            LIMIT 1;
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    }
}
?>