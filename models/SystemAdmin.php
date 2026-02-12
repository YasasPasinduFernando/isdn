<?php

/**
 * SystemAdmin Model
 *
 * Handles all data operations for the System Administrator dashboard:
 *  - Dashboard KPIs & charts
 *  - User CRUD + role management
 *  - Product CRUD + image handling
 *  - Admin profile management
 *  - Audit logging
 *
 * Uses PDO prepared statements throughout.
 */
class SystemAdmin
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ================================================================
     * DASHBOARD
     * ================================================================ */

    public function getDashboardStats(): array
    {
        $stats = [];

        $stats['total_users']    = (int) $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['active_users']   = (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
        $stats['total_products'] = (int) $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $stats['total_orders']   = (int) $this->pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $stats['total_revenue']  = (float) $this->pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
        $stats['pending_orders'] = (int) $this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

        return $stats;
    }

    public function getUsersByRole(): array
    {
        return $this->pdo->query(
            "SELECT role, COUNT(*) AS count FROM users GROUP BY role ORDER BY count DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdersByMonth(int $months = 6): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    DATE_FORMAT(created_at, '%b %Y')  AS month_label,
                    COUNT(*) AS order_count,
                    COALESCE(SUM(total_amount), 0) AS revenue
             FROM orders
             WHERE status != 'cancelled'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY month_key, month_label
             ORDER BY month_key ASC"
        );
        $stmt->bindValue(':months', $months, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentOrders(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at,
                    u.username AS customer_name, r.rdc_name
             FROM orders o
             LEFT JOIN users u ON o.customer_id = u.id
             LEFT JOIN rdcs r  ON o.rdc_id = r.rdc_id
             ORDER BY o.created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentAuditLogs(int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, u.username
             FROM audit_logs a
             JOIN users u ON a.user_id = u.id
             ORDER BY a.created_at DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================================
     * USER MANAGEMENT
     * ================================================================ */

    public function getUsers(int $page = 1, int $perPage = 10, string $search = '', string $roleFilter = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(u.username LIKE :search OR u.email LIKE :search2)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }
        if ($roleFilter !== '') {
            $conditions[] = "u.role = :role";
            $params['role'] = $roleFilter;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT u.*, r.rdc_name
                FROM users u
                LEFT JOIN rdcs r ON u.rdc_id = r.rdc_id
                {$where}
                ORDER BY u.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers(string $search = '', string $roleFilter = ''): int
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(username LIKE :search OR email LIKE :search2)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }
        if ($roleFilter !== '') {
            $conditions[] = "role = :role";
            $params['role'] = $roleFilter;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.*, r.rdc_name FROM users u LEFT JOIN rdcs r ON u.rdc_id = r.rdc_id WHERE u.id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createUser(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (username, email, password, role, rdc_id, is_active, created_at)
             VALUES (:username, :email, :password, :role, :rdc_id, :is_active, NOW())"
        );
        $stmt->execute([
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
            'role'      => $data['role'],
            'rdc_id'    => !empty($data['rdc_id']) ? (int) $data['rdc_id'] : null,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateUser(int $id, array $data): bool
    {
        $fields = ['username = :username', 'email = :email', 'role = :role', 'rdc_id = :rdc_id', 'is_active = :is_active'];
        $params = [
            'id'        => $id,
            'username'  => $data['username'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'rdc_id'    => !empty($data['rdc_id']) ? (int) $data['rdc_id'] : null,
            'is_active' => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleUserActive(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getAllRdcs(): array
    {
        return $this->pdo->query("SELECT rdc_id, rdc_name, rdc_code FROM rdcs ORDER BY rdc_name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRoles(): array
    {
        return [
            'customer'             => 'Customer',
            'rdc_manager'          => 'RDC Manager',
            'rdc_clerk'            => 'RDC Clerk',
            'rdc_sales_ref'        => 'Sales Representative',
            'logistics_officer'    => 'Logistics Officer',
            'rdc_driver'           => 'RDC Driver',
            'head_office_manager'  => 'Head Office Manager',
            'system_admin'         => 'System Administrator',
        ];
    }

    /* ================================================================
     * PRODUCT MANAGEMENT
     * ================================================================ */

    public function getProducts(int $page = 1, int $perPage = 10, string $search = '', string $category = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(p.product_name LIKE :search OR p.product_code LIKE :search2)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }
        if ($category !== '') {
            $conditions[] = "p.category = :category";
            $params['category'] = $category;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT p.*,
                       COALESCE((SELECT SUM(ps.available_quantity) FROM product_stocks ps WHERE ps.product_id = p.product_id), 0) AS total_stock
                FROM products p
                {$where}
                ORDER BY p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countProducts(string $search = '', string $category = ''): int
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(product_name LIKE :search OR product_code LIKE :search2)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
        }
        if ($category !== '') {
            $conditions[] = "category = :category";
            $params['category'] = $category;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getProductById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE product_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createProduct(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (product_code, product_name, category, unit_price, minimum_stock_level, image_url, is_active)
             VALUES (:code, :name, :category, :price, :min_stock, :image, :active)"
        );
        $stmt->execute([
            'code'      => $data['product_code'],
            'name'      => $data['product_name'],
            'category'  => $data['category'],
            'price'     => $data['unit_price'],
            'min_stock' => $data['minimum_stock_level'] ?? 100,
            'image'     => $data['image_url'] ?? null,
            'active'    => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateProduct(int $id, array $data): bool
    {
        $fields = [
            'product_code = :code',
            'product_name = :name',
            'category = :category',
            'unit_price = :price',
            'minimum_stock_level = :min_stock',
            'is_active = :active',
        ];
        $params = [
            'id'        => $id,
            'code'      => $data['product_code'],
            'name'      => $data['product_name'],
            'category'  => $data['category'],
            'price'     => $data['unit_price'],
            'min_stock' => $data['minimum_stock_level'] ?? 100,
            'active'    => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ];

        if (!empty($data['image_url'])) {
            $fields[] = 'image_url = :image';
            $params['image'] = $data['image_url'];
        }

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE product_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleProductActive(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE product_id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function deleteProduct(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE product_id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getProductCategories(): array
    {
        return $this->pdo->query(
            "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category"
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    /* ================================================================
     * ADMIN PROFILE
     * ================================================================ */

    public function getAdminProfile(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.username, u.email, u.role, u.created_at,
                    sa.name, sa.address, sa.contact_number
             FROM users u
             LEFT JOIN system_admins sa ON sa.user_id = u.id
             WHERE u.id = :id"
        );
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateProfile(int $userId, array $data): bool
    {
        // Update users table
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->execute(['id' => $userId, 'username' => $data['username'], 'email' => $data['email']]);

        // Upsert system_admins detail record
        $check = $this->pdo->prepare("SELECT id FROM system_admins WHERE user_id = :uid");
        $check->execute(['uid' => $userId]);

        if ($check->fetch()) {
            $stmt = $this->pdo->prepare(
                "UPDATE system_admins SET name = :name, contact_number = :phone, address = :address WHERE user_id = :uid"
            );
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO system_admins (user_id, name, email, contact_number, address) VALUES (:uid, :name, :email, :phone, :address)"
            );
            $stmt->bindValue(':email', $data['email']);
        }
        $stmt->bindValue(':uid', $userId);
        $stmt->bindValue(':name', $data['name'] ?? '');
        $stmt->bindValue(':phone', $data['contact_number'] ?? '');
        $stmt->bindValue(':address', $data['address'] ?? '');

        return $stmt->execute();
    }

    /**
     * @return true on success, or error string on failure
     */
    public function changePassword(int $userId, string $currentPw, string $newPw): bool|string
    {
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($currentPw, $hash)) {
            return 'Current password is incorrect.';
        }

        $stmt = $this->pdo->prepare("UPDATE users SET password = :pw WHERE id = :id");
        $stmt->execute(['id' => $userId, 'pw' => password_hash($newPw, PASSWORD_DEFAULT)]);
        return true;
    }

    public function getLoginHistory(int $userId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM audit_logs WHERE user_id = :uid AND action = 'LOGIN' ORDER BY created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ================================================================
     * AUDIT LOGGING
     * ================================================================ */

    public function logAction(int $userId, string $action, string $entityType, ?int $entityId = null, string $details = ''): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $stmt = $this->pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address)
             VALUES (:uid, :action, :entity_type, :entity_id, :details, :ip)"
        );
        $stmt->execute([
            'uid'         => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
            'ip'          => $ip,
        ]);
    }

    public function getAuditLogs(int $page = 1, int $perPage = 20, string $search = '', string $actionFilter = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(u.username LIKE :search OR a.details LIKE :search2 OR a.entity_type LIKE :search3)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }
        if ($actionFilter !== '') {
            $conditions[] = "a.action = :action";
            $params['action'] = $actionFilter;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT a.*, u.username
                FROM audit_logs a
                JOIN users u ON a.user_id = u.id
                {$where}
                ORDER BY a.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAuditLogs(string $search = '', string $actionFilter = ''): int
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $conditions[] = "(u.username LIKE :search OR a.details LIKE :search2 OR a.entity_type LIKE :search3)";
            $params['search']  = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }
        if ($actionFilter !== '') {
            $conditions[] = "a.action = :action";
            $params['action'] = $actionFilter;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM audit_logs a JOIN users u ON a.user_id = u.id {$where}");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ================================================================
     * IMAGE UPLOAD HELPER
     * ================================================================ */

    public function handleImageUpload(array $file, string $uploadDir = 'uploads/'): ?string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if ($file['error'] !== UPLOAD_ERR_OK || !in_array($file['type'], $allowed)) {
            return null;
        }
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . uniqid() . '.' . strtolower($ext);
        $destDir = __DIR__ . '/../' . $uploadDir;

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $dest = $destDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $uploadDir . $filename;
        }
        return null;
    }
}
