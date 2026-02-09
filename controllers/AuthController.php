<?php
// Auth update: role-aware login redirects, registration role validation,
// and dashboard routing per user role.
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $role = sanitize_input($_POST['role']);

        $allowedRoles = [
            'customer',
            'rdc_manager',
            'rdc_clerk',
            'rdc_sales_ref',
            'logistics_officer',
            'rdc_driver',
            'head_office_manager',
            'system_admin'
        ];
        if (!in_array($role, $allowedRoles, true)) {
            flash_message('Invalid user role selected.', 'error');
            redirect('/index.php?page=register');
        }

        $userModel = new User($pdo);
        
        if ($userModel->findByEmail($email)) {
            flash_message('Email already exists!', 'error');
            redirect('/index.php?page=register');
        }

        if ($userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ])) {
            flash_message('Registration successful! Please login.', 'success');
            redirect('/index.php?page=login');
        } else {
            flash_message('Registration failed!', 'error');
            redirect('/index.php?page=register');
        }
    }

    if ($action === 'login') {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];

        $userModel = new User($pdo);
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['rdc_id'] = $user['rdc_id'] ?? null;
            
            flash_message('Login successful!', 'success');

            $dashboard = dashboard_page_for_role($user['role']);
            redirect('/index.php?page=' . $dashboard);
        } else {
            flash_message('Invalid email or password!', 'error');
            redirect('/index.php?page=login');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'logout') {
        session_destroy();
        redirect('/index.php?page=login');
    }
}
?>
