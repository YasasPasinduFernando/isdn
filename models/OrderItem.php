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
                p.product_id,
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

    public function getOrderItemsWithStocks($orderId, $rdc_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.product_code,
                p.product_name,
                pc.name AS product_category,
                p.unit_price,
                oi.quantity,
                oi.discount,
                COALESCE(ps.available_quantity, 0) AS available_quantity,
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
                    LEFT JOIN
                product_stocks ps ON ps.product_id = oi.product_id
                    AND ps.rdc_id = :rdc_id
            WHERE
                oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId, 'rdc_id' => $rdc_id]);
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

    public function validateStockAvailability(int $orderId, int $rdcId): bool
    {
        $sql = "
        SELECT 1
        FROM order_items oi
        LEFT JOIN product_stocks ps
            ON ps.product_id = oi.product_id
           AND ps.rdc_id = :rdc_id
        WHERE oi.order_id = :order_id
          AND oi.quantity > COALESCE(ps.available_quantity, 0)
        LIMIT 1
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindValue(':rdc_id', $rdcId, PDO::PARAM_INT);
        $stmt->execute();

        // If any row exists → stock insufficient
        return $stmt->fetch() === false;
    }

    public function updateProductStocks(array $orderItems, int $rdcId, string $action): bool
    {
        if (empty($orderItems)) {
            return true;
        }

        $action = strtolower(trim($action));
        if (!in_array($action, ['deduct', 'add'], true)) {
            throw new InvalidArgumentException("Invalid action '{$action}'. Use 'deduct' or 'add'.");
        }

        // Only start/commit/rollback if we are not already inside a transaction
        $startedTxn = false;

        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $startedTxn = true;
            }

            if ($action === 'deduct') {
                // Fix HY093: use unique placeholders (qty used twice)
                $sql = "
                UPDATE product_stocks
                SET 
                    available_quantity = available_quantity - :qty_deduct,
                    last_updated = NOW()
                WHERE product_id = :product_id
                  AND rdc_id = :rdc_id
                  AND available_quantity >= :qty_check
                LIMIT 1
            ";
            } else { // add
                $sql = "
                UPDATE product_stocks
                SET 
                    available_quantity = available_quantity + :qty_add,
                    last_updated = NOW()
                WHERE product_id = :product_id
                  AND rdc_id = :rdc_id
                LIMIT 1
            ";
            }

            $stmt = $this->pdo->prepare($sql);

            foreach ($orderItems as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);

                if ($productId <= 0 || $qty <= 0) {
                    throw new InvalidArgumentException('Invalid order item data (product_id/quantity missing).');
                }

                $params = [
                    ':product_id' => $productId,
                    ':rdc_id' => $rdcId,
                ];

                if ($action === 'deduct') {
                    $params[':qty_deduct'] = $qty;
                    $params[':qty_check'] = $qty;
                } else {
                    $params[':qty_add'] = $qty;
                }

                $stmt->execute($params);

                if ($stmt->rowCount() === 0) {
                    if ($action === 'deduct') {
                        throw new RuntimeException(
                            "Insufficient/missing stock for product_id={$productId} at rdc_id={$rdcId}"
                        );
                    }
                    throw new RuntimeException(
                        "Missing stock row for product_id={$productId} at rdc_id={$rdcId}"
                    );
                }
            }

            if ($startedTxn) {
                $this->pdo->commit();
            }

            return true;

        } catch (Throwable $e) {
            // Rollback only if we started the transaction and it is still active
            if ($startedTxn && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

}
?>