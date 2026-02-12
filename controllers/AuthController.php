<?php
/**
 * AuthController - Handles login, registration, Google OAuth, forgot/reset password
 *
 * Features:
 *  - Email/password login with is_active check
 *  - Customer-only self-registration with welcome email
 *  - Google OAuth (Continue with Google)
 *  - Login notification emails
 *  - Forgot password with secure token + email
 *  - Password reset
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../includes/mail_helper.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($pdo);

// ══════════════════════════════════════════════════════════════
// POST ACTIONS
// ══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── REGISTER (Customers only) ────────────────────────────
    if ($action === 'register') {
        $username        = sanitize_input($_POST['username']);
        $email           = sanitize_input($_POST['email']);
        $password        = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Only customers can self-register
        $role = 'customer';

        if (empty($username) || empty($email) || empty($password)) {
            flash_message('Please fill in all required fields.', 'error');
            redirect('/index.php?page=register');
        }

        if (strlen($password) < 6) {
            flash_message('Password must be at least 6 characters.', 'error');
            redirect('/index.php?page=register');
        }

        if ($password !== $confirmPassword) {
            flash_message('Passwords do not match.', 'error');
            redirect('/index.php?page=register');
        }

        if ($userModel->findByEmail($email)) {
            flash_message('Email already exists! Please login instead.', 'error');
            redirect('/index.php?page=register');
        }

        if ($userModel->create([
            'username' => $username,
            'email'    => $email,
            'password' => $password,
            'role'     => $role
        ])) {
            // Send welcome email
            send_welcome_email($email, $username);

            flash_message('Registration successful! A welcome email has been sent. Please login.', 'success');
            redirect('/index.php?page=login');
        } else {
            flash_message('Registration failed! Please try again.', 'error');
            redirect('/index.php?page=register');
        }
    }

    // ── LOGIN ────────────────────────────────────────────────
    if ($action === 'login') {
        $email    = sanitize_input($_POST['email']);
        $password = $_POST['password'];

        $user = $userModel->findByEmail($email);

        if (!$user) {
            flash_message('Invalid email or password!', 'error');
            redirect('/index.php?page=login');
        }

        // Check if account is active
        if (isset($user['is_active']) && !$user['is_active']) {
            flash_message('Your account has been deactivated. Please contact the administrator.', 'error');
            redirect('/index.php?page=login');
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['rdc_id']   = $user['rdc_id'] ?? null;

            // Send login notification email
            send_login_notification($user['email'], $user['username'], $user['role']);

            flash_message('Login successful!', 'success');
            $dashboard = dashboard_page_for_role($user['role']);
            redirect('/index.php?page=' . $dashboard);
        } else {
            flash_message('Invalid email or password!', 'error');
            redirect('/index.php?page=login');
        }
    }

    // ── FORGOT PASSWORD ──────────────────────────────────────
    if ($action === 'forgot_password') {
        $email = sanitize_input($_POST['email']);

        if (empty($email)) {
            flash_message('Please enter your email address.', 'error');
            redirect('/index.php?page=forgot-password');
        }

        $user = $userModel->findByEmail($email);

        if ($user) {
            $token = $userModel->setPasswordResetToken($email);

            if ($token) {
                send_password_reset_email($email, $user['username'], $token);
            }
        }

        // Always show success to prevent email enumeration
        flash_message('If an account with that email exists, a password reset link has been sent.', 'success');
        redirect('/index.php?page=forgot-password');
    }

    // ── RESET PASSWORD ───────────────────────────────────────
    if ($action === 'reset_password') {
        $token           = $_POST['token'] ?? '';
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password)) {
            flash_message('Invalid request.', 'error');
            redirect('/index.php?page=login');
        }

        if (strlen($password) < 6) {
            flash_message('Password must be at least 6 characters.', 'error');
            redirect('/index.php?page=reset-password&token=' . urlencode($token));
        }

        if ($password !== $confirmPassword) {
            flash_message('Passwords do not match.', 'error');
            redirect('/index.php?page=reset-password&token=' . urlencode($token));
        }

        $user = $userModel->findByResetToken($token);

        if (!$user) {
            flash_message('Invalid or expired reset link. Please request a new one.', 'error');
            redirect('/index.php?page=forgot-password');
        }

        if ($userModel->resetPassword($user['id'], $password)) {
            flash_message('Password reset successfully! Please login with your new password.', 'success');
            redirect('/index.php?page=login');
        } else {
            flash_message('Failed to reset password. Please try again.', 'error');
            redirect('/index.php?page=reset-password&token=' . urlencode($token));
        }
    }
}

// ══════════════════════════════════════════════════════════════
// GET ACTIONS
// ══════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    // ── LOGOUT ───────────────────────────────────────────────
    if ($action === 'logout') {
        session_destroy();
        redirect('/index.php?page=login');
    }

    // ── GOOGLE OAUTH: Redirect to Google ─────────────────────
    if ($action === 'google_login') {
        $params = [
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => bin2hex(random_bytes(16)),
        ];

        $_SESSION['google_oauth_state'] = $params['state'];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $authUrl);
        exit();
    }

    // ── GOOGLE OAUTH: Callback ───────────────────────────────
    if ($action === 'google_callback') {
        $code = $_GET['code'] ?? '';

        if (empty($code)) {
            flash_message('Google login was cancelled or failed.', 'error');
            redirect('/index.php?page=login');
        }

        // Exchange code for access token
        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $tokenData = [
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $tokenResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            flash_message('Failed to authenticate with Google. Please try again.', 'error');
            redirect('/index.php?page=login');
        }

        $tokenResult = json_decode($tokenResponse, true);
        $accessToken = $tokenResult['access_token'] ?? '';

        if (empty($accessToken)) {
            flash_message('Failed to get access token from Google.', 'error');
            redirect('/index.php?page=login');
        }

        // Get user info from Google
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $userInfoResponse = curl_exec($ch);
        curl_close($ch);

        $googleUser = json_decode($userInfoResponse, true);

        if (empty($googleUser['id']) || empty($googleUser['email'])) {
            flash_message('Could not retrieve your Google account information.', 'error');
            redirect('/index.php?page=login');
        }

        $googleId    = $googleUser['id'];
        $googleEmail = $googleUser['email'];
        $googleName  = $googleUser['name'] ?? $googleUser['email'];

        // Check if user exists by Google ID
        $user = $userModel->findByGoogleId($googleId);

        if (!$user) {
            // Check if user exists by email
            $user = $userModel->findByEmail($googleEmail);

            if ($user) {
                // Link Google ID to existing account
                $userModel->linkGoogleId($user['id'], $googleId);
            } else {
                // Create new customer account
                $newUserId = $userModel->createFromGoogle([
                    'username'  => $googleName,
                    'email'     => $googleEmail,
                    'google_id' => $googleId,
                ]);

                if (!$newUserId) {
                    flash_message('Failed to create account. Please try again.', 'error');
                    redirect('/index.php?page=login');
                }

                $user = $userModel->findById($newUserId);

                // Send welcome email for Google-registered user
                send_google_welcome_email($googleEmail, $googleName);
            }
        }

        // Check if account is active
        if (isset($user['is_active']) && !$user['is_active']) {
            flash_message('Your account has been deactivated. Please contact the administrator.', 'error');
            redirect('/index.php?page=login');
        }

        // Set session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['rdc_id']   = $user['rdc_id'] ?? null;

        // Send login notification
        send_login_notification($user['email'], $user['username'], $user['role']);

        flash_message('Logged in with Google successfully!', 'success');
        $dashboard = dashboard_page_for_role($user['role']);
        redirect('/index.php?page=' . $dashboard);
    }
}
?>
