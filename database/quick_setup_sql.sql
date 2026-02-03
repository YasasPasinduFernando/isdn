-- =====================================================
-- ISDN Quick Database Setup
-- Run this in phpMyAdmin SQL tab
-- =====================================================

CREATE DATABASE IF NOT EXISTS isdn_db;
USE isdn_db;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'rdc_staff', 'logistics', 'admin') DEFAULT 'customer',
    rdc_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'delivered', 'cancelled') DEFAULT 'pending',
    delivery_date DATE,
    rdc_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- Insert Admin User (email: admin@isdn.lk, password: password)
INSERT INTO users (username, email, password, role) VALUES
('Admin', 'admin@isdn.lk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Products
INSERT INTO products (name, description, category, unit_price, status) VALUES
('Coca Cola 1L', 'Soft drink', 'Beverages', 250.00, 'active'),
('Marie Biscuits 400g', 'Biscuits family pack', 'Food Items', 380.00, 'active'),
('Lux Soap 100g', 'Beauty soap', 'Personal Care', 120.00, 'active'),
('Harpic Cleaner 500ml', 'Toilet cleaner', 'Home Cleaning', 450.00, 'active');

SELECT 'Setup Complete! Login: admin@isdn.lk / password' AS Message;