<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByGoogleId($googleId) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->execute([$googleId]);
        return $stmt->fetch();
    }

    public function create($data) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password, role, google_id) VALUES (?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['google_id'] ?? null
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create a user from Google OAuth (no password required)
     */
    public function createFromGoogle($data) {
        try {
            // Generate a random password hash (user logs in via Google, never uses this)
            $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, email, password, role, google_id) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $data['username'],
                $data['email'],
                $randomPassword,
                'customer',
                $data['google_id']
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Link Google ID to an existing account
     */
    public function linkGoogleId($userId, $googleId) {
        $stmt = $this->pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
        return $stmt->execute([$googleId, $userId]);
    }

    /**
     * Store password reset token
     */
    public function setPasswordResetToken($email) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $this->pdo->prepare(
            "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?"
        );
        $stmt->execute([$token, $expires, $email]);

        return $stmt->rowCount() > 0 ? $token : false;
    }

    /**
     * Find user by valid (non-expired) reset token
     */
    public function findByResetToken($token) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()"
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    /**
     * Reset password and clear the token
     */
    public function resetPassword($userId, $newPassword) {
        $stmt = $this->pdo->prepare(
            "UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?"
        );
        return $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $userId
        ]);
    }
}
?>
