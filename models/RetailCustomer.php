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

    public function getCustomerOrderStatusCounts(int $customerId): array
    {
        if ($customerId <= 0) {
            throw new InvalidArgumentException('Valid customer id is required.');
        }

        $sql = "
        SELECT
            o.status,
            COUNT(*) AS order_count
        FROM orders o
        WHERE o.customer_id = :customer_id
          AND o.status IN ('pending', 'processing', 'delivered', 'in transit', 'cancelled')
        GROUP BY o.status
        ORDER BY FIELD(o.status, 'pending', 'processing', 'in transit', 'delivered', 'cancelled')
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customerId
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [
            'pending_count' => 0,
            'processing_count' => 0,
            'in_transit_count' => 0,
            'delivered_count' => 0,
            'cancelled_count' => 0,
            'total_orders' => 0
        ];

        foreach ($rows as $row) {
            $statusKey = str_replace(' ', '_', strtolower($row['status'])) . '_count';
            $count = (int) ($row['order_count'] ?? 0);

            if (array_key_exists($statusKey, $result)) {
                $result[$statusKey] = $count;
                $result['total_orders'] += $count;
            }
        }

        return $result;
    }



}
?>