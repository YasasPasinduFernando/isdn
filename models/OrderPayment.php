<?php
class OrderPayment
{
    private PDO $pdo;


    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }


    public function savePayment($payment_info)
    {
        $order_total = $payment_info['order_total'];
        $payment_method = $payment_info['payment_method'];
        $order_id = $payment_info['order_id'];

        $this->pdo->beginTransaction();

        $paymentStmt = $this->pdo->prepare("
                INSERT INTO payments 
                (order_id, amount, payment_date, payment_method)
                VALUES 
                (:order_id, :amount, NOW(), :payment_method)
            ");

        $paymentStmt->execute([
            'order_id' => $order_id,
            'amount' => $order_total,
            'payment_method' => $payment_method
        ]);

        $paymentId = $this->pdo->lastInsertId();
        $this->pdo->commit();
        return $paymentId;

    }

}
?>