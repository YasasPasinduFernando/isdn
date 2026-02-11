<?php
class Product
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllProducts()
    {
        $stmt = $this->pdo->prepare("SELECT 
                    P.*, PS.available_quantity
                    FROM
                    products P
                    INNER JOIN
                    product_stocks PS ON P.product_id = PS.product_id
                    WHERE
                    PS.rdc_id = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>