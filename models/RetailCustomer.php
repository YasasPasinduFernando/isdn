<?php
class RetailCustomer
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByUserId($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM retail_customers WHERE user_id  = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

     public function findByCustomerId($customer_id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM retail_customers WHERE id  = ?");
        $stmt->execute([$customer_id]);
        return $stmt->fetch();
    }

    public function findByRdcId($rdcId)
    {
        $stmt = $this->pdo->prepare("SELECT 
                rc.id,
                rc.name AS customer_name,
                rc.address
            FROM retail_customers AS rc
            INNER JOIN users AS u 
                ON u.id = rc.user_id
            WHERE u.rdc_id = :rdc_id
        ");
        $stmt->execute(['rdc_id' => $rdcId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}
?>