<?php
/**
 * Shared profile get/update for any role (except system_admin who use SystemAdmin + system-admin-profile).
 */
class Profile {
    private $pdo;

    /** Role => profile table (has user_id, name, contact_number, address, email) */
    private static $roleTable = [
        'customer'           => 'retail_customers',
        'rdc_manager'        => 'rdc_managers',
        'rdc_clerk'          => 'rdc_clerks',
        'rdc_sales_ref'      => 'rdc_sales_refs',
        'logistics_officer'  => 'rdc_logistics_officers',
        'rdc_driver'         => 'rdc_drivers',
        'head_office_manager'=> 'head_office_managers',
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get profile for a user: users row + role-specific row (name, contact_number, address).
     */
    public function getProfile(int $userId, string $role): ?array {
        $stmt = $this->pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return null;

        $table = self::$roleTable[$role] ?? null;
        if ($table) {
            $stmt = $this->pdo->prepare("SELECT name, contact_number, address FROM {$table} WHERE user_id = ?");
            $stmt->execute([$userId]);
            $extra = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($extra) {
                $user = array_merge($user, $extra);
            } else {
                $user['name'] = $user['address'] = $user['contact_number'] = null;
            }
        } else {
            $user['name'] = $user['address'] = $user['contact_number'] = null;
        }
        return $user;
    }

    /**
     * Update username/email in users and name/contact_number/address in role table.
     */
    public function updateProfile(int $userId, string $role, array $data): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->execute([
            'id'       => $userId,
            'username' => trim($data['username'] ?? ''),
            'email'    => trim($data['email'] ?? ''),
        ]);

        $table = self::$roleTable[$role] ?? null;
        if (!$table) return true;

        $name = trim($data['name'] ?? '');
        $phone = trim($data['contact_number'] ?? '');
        $address = trim($data['address'] ?? '');

        $check = $this->pdo->prepare("SELECT id FROM {$table} WHERE user_id = ?");
        $check->execute([$userId]);
        if ($check->fetch()) {
            $stmt = $this->pdo->prepare(
                "UPDATE {$table} SET name = :name, contact_number = :phone, address = :address WHERE user_id = :uid"
            );
        } else {
            $stmt = $this->pdo->prepare(
                "INSERT INTO {$table} (user_id, name, contact_number, address) VALUES (:uid, :name, :phone, :address)"
            );
        }
        $stmt->execute(['uid' => $userId, 'name' => $name, 'phone' => $phone, 'address' => $address]);
        return true;
    }

    /**
     * Change password. Returns true on success, or error string.
     */
    public function changePassword(int $userId, string $currentPw, string $newPw): bool|string {
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($currentPw, $hash)) {
            return 'Current password is incorrect.';
        }
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([password_hash($newPw, PASSWORD_DEFAULT), $userId]);
        return true;
    }
}
