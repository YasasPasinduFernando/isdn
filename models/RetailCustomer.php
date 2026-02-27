<?php
class RetailCustomer {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM retail_customers WHERE user_id  = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    
}
?>
