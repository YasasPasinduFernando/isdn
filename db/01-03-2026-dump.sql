-- Adminer 4.7.6 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `isdn_db_2`;
CREATE DATABASE `isdn_db_2` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `isdn_db_2`;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int DEFAULT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `idx_audit_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1,	1,	'LOGIN',	'session',	NULL,	'Logged in as system_admin',	'127.0.0.1',	'2026-03-01 08:00:00'),
(2,	2,	'LOGIN',	'session',	NULL,	'Logged in as head_office_manager',	'127.0.0.1',	'2026-03-01 08:15:00'),
(3,	3,	'LOGIN',	'session',	NULL,	'Logged in as rdc_manager',	'127.0.0.1',	'2026-03-01 08:30:00'),
(4,	5,	'LOGIN',	'session',	NULL,	'Logged in as rdc_clerk',	'127.0.0.1',	'2026-03-01 08:45:00'),
(5,	1,	'CREATE',	'product',	1,	'Created product: Coca-Cola 1L',	'127.0.0.1',	'2026-02-28 10:00:00'),
(6,	1,	'UPDATE',	'product',	1,	'Updated product: Coca-Cola 1L',	'127.0.0.1',	'2026-02-28 11:30:00'),
(7,	3,	'APPROVE',	'transfer',	1,	'Approved transfer TRF-SOUTH-EAST-001',	'127.0.0.1',	'2026-02-20 10:30:00'),
(8,	4,	'APPROVE',	'transfer',	2,	'Approved transfer TRF-WEST-NORTH-001',	'127.0.0.1',	'2026-02-21 11:45:00'),
(9,	5,	'CREATE',	'order',	1,	'Created order ORD-NORTH-2026-001',	'127.0.0.1',	'2026-02-25 09:15:00'),
(10,	6,	'CREATE',	'order',	2,	'Created order ORD-SOUTH-2026-001',	'127.0.0.1',	'2026-02-26 10:30:00'),
(11,	1,	'CREATE',	'promotion',	1,	'Created promotion: Summer Beverage Sale',	'127.0.0.1',	'2026-02-15 12:00:00'),
(12,	2,	'VIEW',	'report',	NULL,	'Viewed stock report',	'127.0.0.1',	'2026-03-01 09:00:00'),
(13,	3,	'UPDATE',	'transfer',	4,	'Updated transfer status to IN_TRANSIT',	'127.0.0.1',	'2026-02-24 08:00:00'),
(14,	5,	'STOCK_MOVEMENT',	'stock',	1,	'Stock In: Coca-Cola 1L',	'127.0.0.1',	'2026-02-15 09:00:00'),
(15,	3,	'LOGIN',	'session',	NULL,	'Logged in as rdc_manager',	'127.0.0.1',	'2026-03-01 05:02:12');

DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE `email_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `status` enum('sent','failed','logged') DEFAULT 'logged',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_type` (`email_type`),
  KEY `idx_email_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `email_logs` (`id`, `recipient_email`, `subject`, `email_type`, `status`, `created_at`) VALUES
(1,	'sysadmin1@mail.com',	'ISDN - New Login to Your Account',	'login_notification',	'sent',	'2026-03-01 08:00:15'),
(2,	'headoffice1@mail.com',	'ISDN - New Login to Your Account',	'login_notification',	'sent',	'2026-03-01 08:15:20'),
(3,	'north_manager@mail.com',	'ISDN - New Login to Your Account',	'login_notification',	'sent',	'2026-03-01 08:30:25'),
(4,	'north_clerk1@mail.com',	'ISDN - New Login to Your Account',	'login_notification',	'sent',	'2026-03-01 08:45:30'),
(5,	'customer1@mail.com',	'Order Confirmation - ORD-NORTH-2026-001',	'order_confirmation',	'sent',	'2026-02-25 09:20:00'),
(6,	'customer2@mail.com',	'Order Confirmation - ORD-SOUTH-2026-001',	'order_confirmation',	'sent',	'2026-02-26 10:35:00'),
(7,	'south_manager@mail.com',	'Transfer Request Pending - TRF-SOUTH-EAST-001',	'transfer_notification',	'sent',	'2026-02-20 09:05:00'),
(8,	'north_clerk1@mail.com',	'Transfer Approved - TRF-WEST-NORTH-001',	'transfer_notification',	'sent',	'2026-02-21 11:50:00'),
(9,	'customer1@mail.com',	'Delivery Update - Order Shipped',	'delivery_notification',	'sent',	'2026-02-27 12:00:00'),
(10,	'customer2@mail.com',	'Payment Received - ORD-SOUTH-2026-001',	'payment_notification',	'sent',	'2026-02-28 16:50:00'),
(11,	'headoffice1@mail.com',	'Low Stock Alert - Multiple Products',	'stock_alert',	'sent',	'2026-02-28 10:00:00'),
(12,	'north_manager@mail.com',	'Weekly Stock Report',	'report_notification',	'sent',	'2026-02-28 17:00:00'),
(13,	'south_clerk1@mail.com',	'Transfer Received - TRF-SOUTH-EAST-001',	'transfer_notification',	'sent',	'2026-02-22 14:35:00'),
(14,	'north_manager@mail.com',	'ISDN - New Login to Your Account',	'login_notification',	'failed',	'2026-03-01 05:02:14');

DROP TABLE IF EXISTS `head_office_managers`;
CREATE TABLE `head_office_managers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `head_office_managers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `head_office_managers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Sarah Johnson',	'456 Corporate Tower, Colombo 03',	'0772345678',	'headoffice1@mail.com',	2);

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `migrations` (`id`, `filename`, `applied_at`) VALUES
(1,	'001_create_users_table.sql',	'2026-03-01 05:01:24'),
(2,	'002_create_rdcs_table.sql',	'2026-03-01 05:01:24'),
(3,	'003_create_product_categories_table.sql',	'2026-03-01 05:01:24'),
(4,	'004_create_products_table.sql',	'2026-03-01 05:01:24'),
(5,	'005_create_retail_customer_table.sql',	'2026-03-01 05:01:24'),
(6,	'006_create_orders_table.sql',	'2026-03-01 05:01:24'),
(7,	'007_create_stock_transfer_table.sql',	'2026-03-01 05:01:24'),
(8,	'008_create_stock_transfer_items.sql',	'2026-03-01 05:01:24'),
(9,	'009_transfer_status_logs.sql',	'2026-03-01 05:01:25'),
(10,	'010_create_order_delivery_table.sql',	'2026-03-01 05:01:25'),
(11,	'011_create_order_item_table.sql',	'2026-03-01 05:01:25'),
(12,	'012_create_payment_table.sql',	'2026-03-01 05:01:25'),
(13,	'013_create_product_stock_table.sql',	'2026-03-01 05:01:25'),
(14,	'014_create_promotion_table.sql',	'2026-03-01 05:01:25'),
(15,	'015_create_rdc_district_table.sql',	'2026-03-01 05:01:25'),
(16,	'016_create_rdc_sales_ref_table.sql',	'2026-03-01 05:01:25'),
(17,	'017_create_rdc_manager_table copy.sql',	'2026-03-01 05:01:25'),
(18,	'018_create_logistics_officers_table.sql',	'2026-03-01 05:01:25'),
(19,	'019_create_rdc_clerks_table.sql',	'2026-03-01 05:01:25'),
(20,	'020_create_rdc_drivers_table.sql',	'2026-03-01 05:01:25'),
(21,	'021_create_head_office_managers_table.sql',	'2026-03-01 05:01:25'),
(22,	'022_create_system_admins_table.sql',	'2026-03-01 05:01:25'),
(23,	'023_create_stock_movement_logs_table.sql',	'2026-03-01 05:01:25'),
(24,	'024_create_shopping_carts_table.sql',	'2026-03-01 05:01:25'),
(25,	'025_audit_logs_and_user_active.sql',	'2026-03-01 05:01:25'),
(26,	'026_auth_enhancements.sql',	'2026-03-01 05:01:25'),
(27,	'029_ensure_product_categories_table.sql',	'2026-03-01 05:01:25'),
(28,	'030_add_orders_rdc_id_for_admin_dashboard.sql',	'2026-03-01 05:01:25');

DROP TABLE IF EXISTS `order_deliveries`;
CREATE TABLE `order_deliveries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `driver_id` int DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_od_order_id` (`order_id`),
  KEY `idx_od_completed` (`completed_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `order_deliveries` (`id`, `order_id`, `delivery_date`, `driver_id`, `completed_date`) VALUES
(1,	1,	'2026-02-27 00:00:00',	7,	'2026-02-27 17:30:00'),
(2,	2,	'2026-02-28 00:00:00',	8,	'2026-02-28 18:15:00'),
(3,	3,	'2026-03-01 00:00:00',	7,	'2026-03-01 16:45:00'),
(4,	4,	'2026-03-02 00:00:00',	8,	NULL),
(5,	5,	'2026-03-03 00:00:00',	7,	NULL),
(6,	9,	'2026-02-17 00:00:00',	7,	'2026-02-17 15:30:00'),
(7,	10,	'2026-02-18 00:00:00',	8,	'2026-02-18 14:20:00'),
(8,	11,	'2026-02-20 00:00:00',	7,	'2026-02-20 17:00:00'),
(9,	12,	'2026-02-22 00:00:00',	8,	'2026-02-22 16:30:00'),
(10,	13,	'2026-02-24 00:00:00',	7,	'2026-02-24 15:45:00'),
(11,	14,	'2026-02-25 00:00:00',	8,	'2026-02-25 18:00:00');

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` bigint NOT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_item_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `selling_price`, `discount`) VALUES
(1,	1,	1,	30,	250.00,	37.50),
(2,	1,	3,	25,	240.00,	36.00),
(3,	1,	7,	10,	1450.00,	0.00),
(4,	2,	2,	40,	280.00,	42.00),
(5,	2,	5,	20,	850.00,	127.50),
(6,	2,	15,	25,	680.00,	136.00),
(7,	3,	22,	50,	350.00,	63.00),
(8,	3,	24,	30,	180.00,	0.00),
(9,	4,	7,	20,	1450.00,	174.00),
(10,	4,	12,	18,	950.00,	114.00),
(11,	4,	13,	30,	420.00,	0.00),
(12,	5,	18,	35,	380.00,	0.00),
(13,	5,	19,	20,	650.00,	0.00),
(14,	6,	31,	40,	320.00,	48.00),
(15,	6,	33,	25,	1850.00,	555.00),
(16,	7,	1,	60,	250.00,	225.00),
(17,	7,	2,	40,	280.00,	168.00),
(18,	7,	4,	35,	240.00,	126.00),
(19,	8,	27,	45,	380.00,	125.46),
(20,	8,	29,	30,	850.00,	280.50),
(21,	9,	11,	45,	580.00,	46.40),
(22,	9,	22,	40,	350.00,	0.00),
(23,	10,	13,	25,	420.00,	126.00),
(24,	10,	15,	30,	680.00,	204.00),
(25,	10,	16,	35,	350.00,	0.00),
(26,	11,	6,	30,	920.00,	0.00),
(27,	11,	8,	25,	380.00,	0.00),
(28,	12,	10,	80,	220.00,	0.00),
(29,	12,	36,	50,	280.00,	0.00),
(30,	12,	37,	40,	220.00,	0.00),
(31,	13,	24,	70,	180.00,	0.00),
(32,	13,	26,	35,	950.00,	0.00),
(33,	14,	32,	60,	280.00,	0.00),
(34,	14,	35,	45,	450.00,	0.00);

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int NOT NULL,
  `rdc_id` int DEFAULT NULL,
  `placed_by` int DEFAULT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','delivered','cancelled') DEFAULT 'pending',
  `estimated_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_orders_created` (`created_at`),
  KEY `idx_orders_rdc_id` (`rdc_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `retail_customers` (`id`),
  CONSTRAINT `orders_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `orders` (`id`, `order_date`, `customer_id`, `rdc_id`, `placed_by`, `order_number`, `total_amount`, `status`, `estimated_date`, `created_at`, `updated_at`) VALUES
(1,	'2026-02-25 09:15:00',	1,	1,	11,	'ORD-NORTH-2026-001',	15750.00,	'delivered',	'2026-02-27',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(2,	'2026-02-26 10:30:00',	2,	2,	12,	'ORD-SOUTH-2026-001',	22350.00,	'delivered',	'2026-02-28',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(3,	'2026-02-27 11:45:00',	3,	1,	11,	'ORD-NORTH-2026-002',	8920.00,	'delivered',	'2026-03-01',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(4,	'2026-02-27 14:20:00',	1,	2,	12,	'ORD-SOUTH-2026-002',	31280.00,	'confirmed',	'2026-03-02',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(5,	'2026-02-28 08:30:00',	2,	1,	11,	'ORD-NORTH-2026-003',	12450.00,	'pending',	'2026-03-03',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(6,	'2026-02-28 13:15:00',	3,	2,	12,	'ORD-SOUTH-2026-003',	18670.00,	'processing',	'2026-03-04',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(7,	'2026-03-01 09:00:00',	1,	1,	11,	'ORD-NORTH-2026-004',	27890.00,	'processing',	'2026-03-05',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(8,	'2026-03-01 10:30:00',	2,	2,	12,	'ORD-SOUTH-2026-004',	19450.00,	'pending',	'2026-03-06',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(9,	'2026-02-15 10:00:00',	1,	1,	11,	'ORD-NORTH-2026-005',	14280.00,	'delivered',	'2026-02-17',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(10,	'2026-02-16 11:30:00',	2,	2,	12,	'ORD-SOUTH-2026-005',	25630.00,	'delivered',	'2026-02-18',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(11,	'2026-02-18 14:15:00',	3,	1,	11,	'ORD-NORTH-2026-006',	11750.00,	'delivered',	'2026-02-20',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(12,	'2026-02-20 09:45:00',	2,	2,	12,	'ORD-SOUTH-2026-006',	33420.00,	'delivered',	'2026-02-22',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(13,	'2026-02-22 13:00:00',	3,	1,	11,	'ORD-NORTH-2026-007',	16890.00,	'delivered',	'2026-02-24',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(14,	'2026-02-23 10:20:00',	2,	2,	12,	'ORD-SOUTH-2026-007',	21340.00,	'delivered',	'2026-02-25',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27');

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_method` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_payments_order_id` FOREIGN KEY (`id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `payments` (`id`, `order_id`, `amount`, `payment_date`, `payment_method`) VALUES
(1,	1,	15750.00,	'2026-02-27 15:30:00',	'BANK_TRANSFER'),
(2,	2,	22350.00,	'2026-02-28 16:45:00',	'CHEQUE'),
(3,	3,	8920.00,	'2026-03-01 14:20:00',	'CASH'),
(4,	9,	14280.00,	'2026-02-17 13:30:00',	'BANK_TRANSFER'),
(5,	10,	25630.00,	'2026-02-18 11:00:00',	'CHEQUE'),
(6,	11,	11750.00,	'2026-02-20 15:45:00',	'CASH'),
(7,	12,	33420.00,	'2026-02-22 10:30:00',	'BANK_TRANSFER'),
(8,	13,	16890.00,	'2026-02-24 14:00:00',	'CHEQUE'),
(9,	14,	21340.00,	'2026-02-25 16:20:00',	'CASH');

DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE `product_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `product_categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1,	'Grocery & Food Items',	'Grocery & Food Items',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(2,	'Beverages',	'Beverages',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(3,	'Household Essentials',	'Household Essentials',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(4,	'Home Cleaning Products',	'Home Cleaning Products',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(5,	'Health Care Products',	'Health Care Products',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(6,	'Personal Care',	'Personal Care',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(7,	'Beauty & Skincare',	'Beauty & Skincare',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(8,	'Baby Care Products',	'Baby Care Products',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27');

DROP TABLE IF EXISTS `product_stocks`;
CREATE TABLE `product_stocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `rdc_id` int DEFAULT NULL,
  `available_quantity` bigint DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rdc_product` (`rdc_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `rdc_id` (`rdc_id`),
  CONSTRAINT `product_stock_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `product_stock_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `product_stocks` (`id`, `product_id`, `rdc_id`, `available_quantity`, `last_updated`) VALUES
(1,	1,	1,	450,	'2026-03-01 05:01:27'),
(2,	2,	1,	380,	'2026-03-01 05:01:27'),
(3,	3,	1,	420,	'2026-03-01 05:01:27'),
(4,	4,	1,	390,	'2026-03-01 05:01:27'),
(5,	5,	1,	180,	'2026-03-01 05:01:27'),
(6,	6,	1,	165,	'2026-03-01 05:01:27'),
(7,	7,	1,	320,	'2026-03-01 05:01:27'),
(8,	8,	1,	245,	'2026-03-01 05:01:27'),
(9,	9,	1,	210,	'2026-03-01 05:01:27'),
(10,	10,	1,	580,	'2026-03-01 05:01:27'),
(11,	11,	1,	290,	'2026-03-01 05:01:27'),
(12,	12,	1,	235,	'2026-03-01 05:01:27'),
(13,	13,	1,	195,	'2026-03-01 05:01:27'),
(14,	14,	1,	220,	'2026-03-01 05:01:27'),
(15,	15,	1,	270,	'2026-03-01 05:01:27'),
(16,	16,	1,	185,	'2026-03-01 05:01:27'),
(17,	17,	1,	145,	'2026-03-01 05:01:27'),
(18,	18,	1,	165,	'2026-03-01 05:01:27'),
(19,	19,	1,	205,	'2026-03-01 05:01:27'),
(20,	20,	1,	240,	'2026-03-01 05:01:27'),
(21,	21,	1,	155,	'2026-03-01 05:01:27'),
(22,	22,	1,	215,	'2026-03-01 05:01:27'),
(23,	23,	1,	195,	'2026-03-01 05:01:27'),
(24,	24,	1,	335,	'2026-03-01 05:01:27'),
(25,	25,	1,	145,	'2026-03-01 05:01:27'),
(26,	26,	1,	125,	'2026-03-01 05:01:27'),
(27,	27,	1,	155,	'2026-03-01 05:01:27'),
(28,	28,	1,	135,	'2026-03-01 05:01:27'),
(29,	29,	1,	115,	'2026-03-01 05:01:27'),
(30,	30,	1,	205,	'2026-03-01 05:01:27'),
(31,	31,	1,	165,	'2026-03-01 05:01:27'),
(32,	32,	1,	195,	'2026-03-01 05:01:27'),
(33,	33,	1,	85,	'2026-03-01 05:01:27'),
(34,	34,	1,	95,	'2026-03-01 05:01:27'),
(35,	35,	1,	25,	'2026-03-01 10:33:44'),
(36,	36,	1,	255,	'2026-03-01 05:01:27'),
(37,	37,	1,	215,	'2026-03-01 05:01:27'),
(38,	38,	1,	165,	'2026-03-01 05:01:27'),
(39,	39,	1,	125,	'2026-03-01 05:01:27'),
(40,	40,	1,	145,	'2026-03-01 05:01:27'),
(41,	1,	2,	520,	'2026-03-01 05:01:27'),
(42,	2,	2,	445,	'2026-03-01 05:01:27'),
(43,	3,	2,	480,	'2026-03-01 05:01:27'),
(44,	4,	2,	465,	'2026-03-01 05:01:27'),
(45,	5,	2,	215,	'2026-03-01 05:01:27'),
(46,	6,	2,	195,	'2026-03-01 05:01:27'),
(47,	7,	2,	375,	'2026-03-01 05:01:27'),
(48,	8,	2,	285,	'2026-03-01 05:01:27'),
(49,	9,	2,	245,	'2026-03-01 05:01:27'),
(50,	10,	2,	650,	'2026-03-01 05:01:27'),
(51,	11,	2,	335,	'2026-03-01 05:01:27'),
(52,	12,	2,	275,	'2026-03-01 05:01:27'),
(53,	13,	2,	225,	'2026-03-01 05:01:27'),
(54,	14,	2,	255,	'2026-03-01 05:01:27'),
(55,	15,	2,	315,	'2026-03-01 05:01:27'),
(56,	16,	2,	215,	'2026-03-01 05:01:27'),
(57,	17,	2,	175,	'2026-03-01 05:01:27'),
(58,	18,	2,	195,	'2026-03-01 05:01:27'),
(59,	19,	2,	245,	'2026-03-01 05:01:27'),
(60,	20,	2,	280,	'2026-03-01 05:01:27'),
(61,	21,	2,	185,	'2026-03-01 05:01:27'),
(62,	22,	2,	250,	'2026-03-01 05:01:27'),
(63,	23,	2,	230,	'2026-03-01 05:01:27'),
(64,	24,	2,	385,	'2026-03-01 05:01:27'),
(65,	25,	2,	175,	'2026-03-01 05:01:27'),
(66,	26,	2,	155,	'2026-03-01 05:01:27'),
(67,	27,	2,	185,	'2026-03-01 05:01:27'),
(68,	28,	2,	165,	'2026-03-01 05:01:27'),
(69,	29,	2,	145,	'2026-03-01 05:01:27'),
(70,	30,	2,	240,	'2026-03-01 05:01:27'),
(71,	31,	2,	195,	'2026-03-01 05:01:27'),
(72,	32,	2,	230,	'2026-03-01 05:01:27'),
(73,	33,	2,	105,	'2026-03-01 05:01:27'),
(74,	34,	2,	115,	'2026-03-01 05:01:27'),
(75,	35,	2,	175,	'2026-03-01 05:01:27'),
(76,	36,	2,	295,	'2026-03-01 05:01:27'),
(77,	37,	2,	250,	'2026-03-01 05:01:27'),
(78,	38,	2,	195,	'2026-03-01 05:01:27'),
(79,	39,	2,	155,	'2026-03-01 05:01:27'),
(80,	40,	2,	175,	'2026-03-01 05:01:27'),
(81,	1,	3,	380,	'2026-03-01 05:01:27'),
(82,	2,	3,	320,	'2026-03-01 05:01:27'),
(83,	3,	3,	350,	'2026-03-01 05:01:27'),
(84,	4,	3,	330,	'2026-03-01 05:01:27'),
(85,	5,	3,	145,	'2026-03-01 05:01:27'),
(86,	6,	3,	135,	'2026-03-01 05:01:27'),
(87,	7,	3,	270,	'2026-03-01 05:01:27'),
(88,	8,	3,	205,	'2026-03-01 05:01:27'),
(89,	9,	3,	175,	'2026-03-01 05:01:27'),
(90,	10,	3,	480,	'2026-03-01 05:01:27'),
(91,	11,	3,	245,	'2026-03-01 05:01:27'),
(92,	12,	3,	195,	'2026-03-01 05:01:27'),
(93,	13,	3,	165,	'2026-03-01 05:01:27'),
(94,	14,	3,	185,	'2026-03-01 05:01:27'),
(95,	15,	3,	225,	'2026-03-01 05:01:27'),
(96,	16,	3,	155,	'2026-03-01 05:01:27'),
(97,	17,	3,	125,	'2026-03-01 05:01:27'),
(98,	18,	3,	135,	'2026-03-01 05:01:27'),
(99,	19,	3,	175,	'2026-03-01 05:01:27'),
(100,	20,	3,	210,	'2026-03-01 05:01:27'),
(101,	21,	3,	125,	'2026-03-01 05:01:27'),
(102,	22,	3,	180,	'2026-03-01 05:01:27'),
(103,	23,	3,	165,	'2026-03-01 05:01:27'),
(104,	24,	3,	280,	'2026-03-01 05:01:27'),
(105,	25,	3,	115,	'2026-03-01 05:01:27'),
(106,	26,	3,	95,	'2026-03-01 05:01:27'),
(107,	27,	3,	125,	'2026-03-01 05:01:27'),
(108,	28,	3,	105,	'2026-03-01 05:01:27'),
(109,	29,	3,	85,	'2026-03-01 05:01:27'),
(110,	30,	3,	175,	'2026-03-01 05:01:27'),
(111,	31,	3,	135,	'2026-03-01 05:01:27'),
(112,	32,	3,	165,	'2026-03-01 05:01:27'),
(113,	33,	3,	65,	'2026-03-01 05:01:27'),
(114,	34,	3,	75,	'2026-03-01 05:01:27'),
(115,	35,	3,	115,	'2026-03-01 05:01:27'),
(116,	36,	3,	215,	'2026-03-01 05:01:27'),
(117,	37,	3,	180,	'2026-03-01 05:01:27'),
(118,	38,	3,	135,	'2026-03-01 05:01:27'),
(119,	39,	3,	95,	'2026-03-01 05:01:27'),
(120,	40,	3,	115,	'2026-03-01 05:01:27'),
(121,	1,	4,	580,	'2026-03-01 05:01:27'),
(122,	2,	4,	520,	'2026-03-01 05:01:27'),
(123,	3,	4,	560,	'2026-03-01 05:01:27'),
(124,	4,	4,	540,	'2026-03-01 05:01:27'),
(125,	5,	4,	250,	'2026-03-01 05:01:27'),
(126,	6,	4,	230,	'2026-03-01 05:01:27'),
(127,	7,	4,	420,	'2026-03-01 05:01:27'),
(128,	8,	4,	335,	'2026-03-01 05:01:27'),
(129,	9,	4,	285,	'2026-03-01 05:01:27'),
(130,	10,	4,	720,	'2026-03-01 05:01:27'),
(131,	11,	4,	385,	'2026-03-01 05:01:27'),
(132,	12,	4,	315,	'2026-03-01 05:01:27'),
(133,	13,	4,	265,	'2026-03-01 05:01:27'),
(134,	14,	4,	295,	'2026-03-01 05:01:27'),
(135,	15,	4,	355,	'2026-03-01 05:01:27'),
(136,	16,	4,	245,	'2026-03-01 05:01:27'),
(137,	17,	4,	205,	'2026-03-01 05:01:27'),
(138,	18,	4,	225,	'2026-03-01 05:01:27'),
(139,	19,	4,	285,	'2026-03-01 05:01:27'),
(140,	20,	4,	320,	'2026-03-01 05:01:27'),
(141,	21,	4,	215,	'2026-03-01 05:01:27'),
(142,	22,	4,	285,	'2026-03-01 05:01:27'),
(143,	23,	4,	265,	'2026-03-01 05:01:27'),
(144,	24,	4,	435,	'2026-03-01 05:01:27'),
(145,	25,	4,	205,	'2026-03-01 05:01:27'),
(146,	26,	4,	185,	'2026-03-01 05:01:27'),
(147,	27,	4,	215,	'2026-03-01 05:01:27'),
(148,	28,	4,	195,	'2026-03-01 05:01:27'),
(149,	29,	4,	175,	'2026-03-01 05:01:27'),
(150,	30,	4,	275,	'2026-03-01 05:01:27'),
(151,	31,	4,	225,	'2026-03-01 05:01:27'),
(152,	32,	4,	265,	'2026-03-01 05:01:27'),
(153,	33,	4,	125,	'2026-03-01 05:01:27'),
(154,	34,	4,	135,	'2026-03-01 05:01:27'),
(155,	35,	4,	205,	'2026-03-01 05:01:27'),
(156,	36,	4,	335,	'2026-03-01 05:01:27'),
(157,	37,	4,	285,	'2026-03-01 05:01:27'),
(158,	38,	4,	225,	'2026-03-01 05:01:27'),
(159,	39,	4,	185,	'2026-03-01 05:01:27'),
(160,	40,	4,	205,	'2026-03-01 05:01:27'),
(161,	1,	5,	495,	'2026-03-01 05:01:27'),
(162,	2,	5,	420,	'2026-03-01 05:01:27'),
(163,	3,	5,	465,	'2026-03-01 05:01:27'),
(164,	4,	5,	445,	'2026-03-01 05:01:27'),
(165,	5,	5,	195,	'2026-03-01 05:01:27'),
(166,	6,	5,	180,	'2026-03-01 05:01:27'),
(167,	7,	5,	350,	'2026-03-01 05:01:27'),
(168,	8,	5,	265,	'2026-03-01 05:01:27'),
(169,	9,	5,	230,	'2026-03-01 05:01:27'),
(170,	10,	5,	620,	'2026-03-01 05:01:27'),
(171,	11,	5,	315,	'2026-03-01 05:01:27'),
(172,	12,	5,	255,	'2026-03-01 05:01:27'),
(173,	13,	5,	210,	'2026-03-01 05:01:27'),
(174,	14,	5,	240,	'2026-03-01 05:01:27'),
(175,	15,	5,	290,	'2026-03-01 05:01:27'),
(176,	16,	5,	200,	'2026-03-01 05:01:27'),
(177,	17,	5,	160,	'2026-03-01 05:01:27'),
(178,	18,	5,	180,	'2026-03-01 05:01:27'),
(179,	19,	5,	230,	'2026-03-01 05:01:27'),
(180,	20,	5,	265,	'2026-03-01 05:01:27'),
(181,	21,	5,	170,	'2026-03-01 05:01:27'),
(182,	22,	5,	235,	'2026-03-01 05:01:27'),
(183,	23,	5,	215,	'2026-03-01 05:01:27'),
(184,	24,	5,	360,	'2026-03-01 05:01:27'),
(185,	25,	5,	160,	'2026-03-01 05:01:27'),
(186,	26,	5,	140,	'2026-03-01 05:01:27'),
(187,	27,	5,	170,	'2026-03-01 05:01:27'),
(188,	28,	5,	150,	'2026-03-01 05:01:27'),
(189,	29,	5,	130,	'2026-03-01 05:01:27'),
(190,	30,	5,	225,	'2026-03-01 05:01:27'),
(191,	31,	5,	180,	'2026-03-01 05:01:27'),
(192,	32,	5,	215,	'2026-03-01 05:01:27'),
(193,	33,	5,	95,	'2026-03-01 05:01:27'),
(194,	34,	5,	105,	'2026-03-01 05:01:27'),
(195,	35,	5,	160,	'2026-03-01 05:01:27'),
(196,	36,	5,	275,	'2026-03-01 05:01:27'),
(197,	37,	5,	235,	'2026-03-01 05:01:27'),
(198,	38,	5,	180,	'2026-03-01 05:01:27'),
(199,	39,	5,	140,	'2026-03-01 05:01:27'),
(200,	40,	5,	160,	'2026-03-01 05:01:27');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `minimum_stock_level` int DEFAULT '100',
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `category_id`, `unit_price`, `minimum_stock_level`, `image_url`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1,	'BEV001',	'Coca-Cola 1L',	2,	250.00,	100,	'/assets/images/products/coca-cola.jpg',	1,	'Refreshing Coca-Cola 1 Liter bottle',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(2,	'BEV002',	'Pepsi 1.5L',	2,	280.00,	100,	'/assets/images/products/pepsi.jpg',	1,	'Pepsi Cola 1.5 Liter bottle',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(3,	'BEV003',	'Sprite 1L',	2,	240.00,	100,	'/assets/images/products/sprite.jpg',	1,	'Lemon-lime flavored soft drink',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(4,	'BEV004',	'Fanta Orange 1L',	2,	240.00,	100,	'/assets/images/products/fanta.jpg',	1,	'Orange flavored carbonated drink',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(5,	'BEV005',	'Nestomalt 400G',	2,	850.00,	50,	'/assets/images/products/nestomalt.jpg',	1,	'Malt drink powder 400g pack',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(6,	'BEV006',	'Milo 400G',	2,	920.00,	50,	'/assets/images/products/milo.jpg',	1,	'Chocolate malt drink powder',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(7,	'GRO001',	'Basmati Rice 5kg',	1,	1450.00,	80,	'/assets/images/products/rice.jpg',	1,	'Premium Basmati rice 5kg pack',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(8,	'GRO002',	'Red Lentils 1kg',	1,	380.00,	60,	'/assets/images/products/lentils.jpg',	1,	'Red lentils 1kg pack',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(9,	'GRO003',	'Coconut Oil 1L',	1,	650.00,	50,	'/assets/images/products/coconut-oil.jpg',	1,	'Pure coconut oil 1 liter',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(10,	'GRO004',	'Sugar 1kg',	1,	220.00,	100,	'/assets/images/products/sugar.jpg',	1,	'White sugar 1kg pack',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(11,	'GRO005',	'Tea 400g',	1,	580.00,	70,	'/assets/images/products/tea.jpg',	1,	'Premium Ceylon tea 400g',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(12,	'GRO006',	'Milk Powder 400g',	1,	950.00,	60,	'/assets/images/products/milk-powder.jpg',	1,	'Full cream milk powder',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(13,	'CLN001',	'Harpic Fresh 500ml',	4,	420.00,	50,	'/assets/images/products/harpic.jpg',	1,	'Toilet cleaner 500ml',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(14,	'CLN002',	'Dettol Floor Cleaner 1L',	4,	580.00,	50,	'/assets/images/products/dettol-floor.jpg',	1,	'Disinfectant floor cleaner',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(15,	'CLN003',	'Sunlight Detergent Powder 1kg',	4,	680.00,	60,	'/assets/images/products/sunlight.jpg',	1,	'Laundry detergent powder',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(16,	'CLN004',	'Vim Dishwash Gel 500ml',	4,	350.00,	50,	'/assets/images/products/vim.jpg',	1,	'Dishwashing liquid gel',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(17,	'CLN005',	'Domestos Bleach 750ml',	4,	480.00,	40,	'/assets/images/products/domestos.jpg',	1,	'Thick bleach cleaner',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(18,	'HLT001',	'Strepsils 24S',	5,	380.00,	40,	'/assets/images/products/strepsils.jpg',	1,	'Throat lozenges 24 pack',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(19,	'HLT002',	'Panadol 100 Tablets',	5,	650.00,	50,	'/assets/images/products/panadol.jpg',	1,	'Pain relief tablets',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(20,	'HLT003',	'Siddhalepa Balm 50g',	5,	280.00,	60,	'/assets/images/products/siddhalepa.jpg',	1,	'Ayurvedic pain balm',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(21,	'HLT004',	'Piriton 30 Tablets',	5,	420.00,	40,	'/assets/images/products/piriton.jpg',	1,	'Allergy relief tablets',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(22,	'PER001',	'Clogard Toothpaste 200g',	6,	350.00,	50,	'/assets/images/products/clogard.jpg',	1,	'Toothpaste 200g tube',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(23,	'PER002',	'Signal Toothpaste 120g',	6,	280.00,	50,	'/assets/images/products/signal.jpg',	1,	'Cavity protection toothpaste',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(24,	'PER003',	'Lux Soap 100g',	6,	180.00,	80,	'/assets/images/products/lux-soap.jpg',	1,	'Beauty soap bar',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(25,	'PER004',	'Sunsilk Shampoo 400ml',	6,	580.00,	40,	'/assets/images/products/sunsilk.jpg',	1,	'Hair care shampoo',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(26,	'PER005',	'Dove Body Wash 500ml',	6,	950.00,	30,	'/assets/images/products/dove.jpg',	1,	'Moisturizing body wash',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(27,	'BEU001',	'Fair & Lovely 50g',	7,	380.00,	40,	'/assets/images/products/fair-lovely.jpg',	1,	'Fairness cream',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(28,	'BEU002',	'Ponds Face Cream 100ml',	7,	650.00,	35,	'/assets/images/products/ponds.jpg',	1,	'Moisturizing face cream',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(29,	'BEU003',	'Nivea Body Lotion 400ml',	7,	850.00,	30,	'/assets/images/products/nivea.jpg',	1,	'Body moisturizer',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(30,	'BEU004',	'Vaseline Petroleum Jelly 100ml',	7,	280.00,	50,	'/assets/images/products/vaseline.jpg',	1,	'Multi-purpose jelly',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(31,	'BAB001',	'Baby Cheramy Baby Talc 100g',	8,	320.00,	40,	'/assets/images/products/baby-cheramy.jpg',	1,	'Baby powder talc',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(32,	'BAB002',	'Johnson Baby Soap 100g',	8,	280.00,	50,	'/assets/images/products/johnson-soap.jpg',	1,	'Gentle baby soap',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(33,	'BAB003',	'Huggies Diapers Medium 30s',	8,	1850.00,	20,	'/assets/images/products/huggies.jpg',	1,	'Baby diapers medium size',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(34,	'BAB004',	'Lactogen 1 400g',	8,	1650.00,	25,	'/assets/images/products/lactogen.jpg',	1,	'Infant formula milk',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(35,	'BAB005',	'Baby Cheramy Lotion 200ml',	8,	450.00,	35,	'/assets/images/products/baby-lotion.jpg',	1,	'Baby body lotion',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(36,	'HSE001',	'Tissue Paper Box',	3,	280.00,	60,	'/assets/images/products/tissue.jpg',	1,	'Facial tissue box',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(37,	'HSE002',	'Kitchen Towel Roll',	3,	220.00,	50,	'/assets/images/products/kitchen-towel.jpg',	1,	'Absorbent kitchen towel',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(38,	'HSE003',	'Garbage Bags Large 20s',	3,	350.00,	40,	'/assets/images/products/garbage-bags.jpg',	1,	'Heavy duty garbage bags',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(39,	'HSE004',	'Aluminum Foil Roll',	3,	480.00,	30,	'/assets/images/products/foil.jpg',	1,	'Kitchen aluminum foil',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(40,	'HSE005',	'Cling Film Roll',	3,	380.00,	35,	'/assets/images/products/cling-film.jpg',	1,	'Food wrap film',	'2026-03-01 05:01:27',	'2026-03-01 05:01:27');

DROP TABLE IF EXISTS `promotions`;
CREATE TABLE `promotions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `description` text,
  `product_id` int DEFAULT NULL,
  `product_count` int DEFAULT NULL,
  `discount_percentage` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `promotion_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `promotions` (`id`, `name`, `description`, `product_id`, `product_count`, `discount_percentage`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1,	'Summer Beverage Sale',	'Buy 10+ bottles and get 15% off on all beverages',	1,	10,	15.00,	'2026-02-01',	'2026-03-31',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(2,	'Bulk Rice Discount',	'Purchase 20+ bags for 12% discount',	7,	20,	12.00,	'2026-02-15',	'2026-04-15',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(3,	'Cleaning Supplies Bundle',	'Get 20% off when buying 15+ cleaning products',	13,	15,	20.00,	'2026-03-01',	'2026-03-31',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(4,	'Health Care Promo',	'Save 10% on bulk orders of 25+ health products',	18,	25,	10.00,	'2026-02-20',	'2026-04-20',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(5,	'Personal Care Deal',	'Buy 30+ items for 18% discount',	22,	30,	18.00,	'2026-03-05',	'2026-04-05',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(6,	'Baby Products Special',	'Bulk purchase discount of 15% on 12+ baby items',	31,	12,	15.00,	'2026-02-10',	'2026-03-25',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(7,	'Tea Time Offer',	'Purchase 40+ packets for 8% off',	11,	40,	8.00,	'2026-03-01',	'2026-04-30',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(8,	'Detergent Mega Sale',	'Get 25% discount on orders of 50+ units',	15,	50,	25.00,	'2026-03-10',	'2026-04-10',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(9,	'Milk Powder Promo',	'Save 12% when buying 20+ packs',	12,	20,	12.00,	'2026-02-25',	'2026-03-31',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(10,	'Spring Skincare Sale',	'Buy 15+ beauty products for 22% off',	27,	15,	22.00,	'2026-03-15',	'2026-04-15',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(11,	'Upcoming Summer Deal',	'Early bird discount on soft drinks',	2,	15,	18.00,	'2026-04-01',	'2026-05-31',	0,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27'),
(12,	'Household Essentials Offer',	'Stock up and save 14% on 20+ items',	36,	20,	14.00,	'2026-03-01',	'2026-04-01',	1,	'2026-03-01 05:01:27',	'2026-03-01 05:01:27');

DROP TABLE IF EXISTS `rdc_clerks`;
CREATE TABLE `rdc_clerks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rdc_clerks_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rdc_clerks` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Nimal Fernando',	'56 Clerk Street, Jaffna',	'0775678901',	'north_clerk1@mail.com',	5),
(2,	'Chamari Wijesinghe',	'78 Office Road, Galle',	'0776789012',	'south_clerk1@mail.com',	6);

DROP TABLE IF EXISTS `rdc_districts`;
CREATE TABLE `rdc_districts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `rdc_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdc_id` (`rdc_id`),
  CONSTRAINT `rdc_district_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `rdc_drivers`;
CREATE TABLE `rdc_drivers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rdc_drivers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rdc_drivers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Anil Kumar',	'90 Driver Lane, Jaffna',	'0777890123',	'north_driver1@mail.com',	7),
(2,	'Lasantha Mendis',	'12 Transport Road, Galle',	'0778901234',	'south_driver1@mail.com',	8);

DROP TABLE IF EXISTS `rdc_logistics_officers`;
CREATE TABLE `rdc_logistics_officers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rdc_logistics_officers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rdc_logistics_officers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Chaminda Rathnayake',	'89 Logistics Lane, Jaffna',	'0773456780',	'north_logistics_officer@mail.com',	13);

DROP TABLE IF EXISTS `rdc_managers`;
CREATE TABLE `rdc_managers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rdc_managers_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rdc_managers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Rajith Perera',	'12 Manager Lane, Jaffna',	'0773456789',	'north_manager@mail.com',	3),
(2,	'Kumari Silva',	'34 South Road, Galle',	'0774567890',	'south_manager@mail.com',	4);

DROP TABLE IF EXISTS `rdc_sales_refs`;
CREATE TABLE `rdc_sales_refs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `rdc_sales_refs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rdc_sales_refs` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Kamal Jayasuriya',	'23 Sales Street, Jaffna',	'0771234568',	'north_sales_ref@mail.com',	12);

DROP TABLE IF EXISTS `rdcs`;
CREATE TABLE `rdcs` (
  `rdc_id` int NOT NULL AUTO_INCREMENT,
  `rdc_code` enum('NORTH','SOUTH','EAST','WEST','CENTRAL') DEFAULT 'NORTH',
  `rdc_name` varchar(100) NOT NULL,
  `province` varchar(50) DEFAULT NULL,
  `address` text,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rdc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `rdcs` (`rdc_id`, `rdc_code`, `rdc_name`, `province`, `address`, `contact_number`, `created_at`) VALUES
(1,	'NORTH',	'Northern RDC',	'Northern Province',	'Jaffna Industrial Zone',	'0711111111',	'2026-03-01 05:01:26'),
(2,	'SOUTH',	'Southern RDC',	'Southern Province',	'Galle Trade Center',	'0712222222',	'2026-03-01 05:01:26'),
(3,	'EAST',	'Eastern RDC',	'Eastern Province',	'Batticaloa Hub',	'0713333333',	'2026-03-01 05:01:26'),
(4,	'WEST',	'Western RDC',	'Western Province',	'Colombo Warehouse Complex',	'0714444444',	'2026-03-01 05:01:26'),
(5,	'CENTRAL',	'Central RDC',	'Central Province',	'Kandy Distribution Park',	'0715555555',	'2026-03-01 05:01:26');

DROP TABLE IF EXISTS `retail_customers`;
CREATE TABLE `retail_customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `retail_customer_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `retail_customers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Sunil Traders',	'45 Market Street, Colombo 11',	'0779012345',	'customer1@mail.com',	9),
(2,	'Lakshmi Stores',	'67 Shop Avenue, Galle',	'0770123456',	'customer2@mail.com',	10),
(3,	'Lakshmi Stores',	'67 Shop Avenue, Galle',	'0770123456',	'customer3@mail.com',	11);

DROP TABLE IF EXISTS `shopping_carts`;
CREATE TABLE `shopping_carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sc_user_id_idx` (`user_id`),
  KEY `fk_sc_product_id_idx` (`product_id`),
  CONSTRAINT `fk_sc_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `fk_sc_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `shopping_carts` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(1,	9,	1,	50),
(2,	9,	3,	35),
(3,	9,	7,	15),
(4,	9,	22,	40),
(5,	10,	2,	60),
(6,	10,	5,	25),
(7,	10,	15,	45),
(8,	10,	31,	30),
(9,	10,	13,	40),
(10,	10,	27,	20);

DROP TABLE IF EXISTS `stock_movement_logs`;
CREATE TABLE `stock_movement_logs` (
  `movement_id` int NOT NULL AUTO_INCREMENT,
  `rdc_id` int NOT NULL,
  `product_id` int NOT NULL,
  `movement_type` enum('STOCK_IN','STOCK_OUT','TRANSFER_OUT','TRANSFER_IN','ADJUSTMENT','DAMAGED','RETURNED','EXPIRED') NOT NULL,
  `quantity` int NOT NULL COMMENT 'Positive or negative',
  `previous_quantity` int DEFAULT NULL,
  `new_quantity` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_by_role` varchar(50) NOT NULL,
  `created_by_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `note` text,
  PRIMARY KEY (`movement_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_movement_type` (`movement_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_rdc_product` (`rdc_id`,`product_id`),
  CONSTRAINT `stock_movement_logs_ibfk_1` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`),
  CONSTRAINT `stock_movement_logs_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `stock_movement_logs` (`movement_id`, `rdc_id`, `product_id`, `movement_type`, `quantity`, `previous_quantity`, `new_quantity`, `created_by`, `created_by_role`, `created_by_name`, `created_at`, `note`) VALUES
(1,	1,	1,	'STOCK_IN',	200,	250,	450,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-15 09:00:00',	'New shipment received from supplier'),
(2,	2,	2,	'STOCK_IN',	150,	295,	445,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-15 10:30:00',	'Monthly stock replenishment'),
(3,	1,	7,	'STOCK_IN',	100,	220,	320,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-16 11:15:00',	'Rice shipment from supplier'),
(4,	2,	15,	'STOCK_IN',	120,	195,	315,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-17 14:00:00',	'Cleaning products restocked'),
(5,	1,	1,	'STOCK_OUT',	30,	450,	420,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-25 09:30:00',	'Order fulfillment ORD-NORTH-2026-001'),
(6,	2,	2,	'STOCK_OUT',	40,	445,	405,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-26 10:45:00',	'Order fulfillment ORD-SOUTH-2026-001'),
(7,	1,	22,	'STOCK_OUT',	50,	265,	215,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-27 12:00:00',	'Order fulfillment ORD-NORTH-2026-002'),
(8,	1,	3,	'DAMAGED',	15,	435,	420,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-20 08:30:00',	'Damaged bottles found during inspection'),
(9,	2,	13,	'DAMAGED',	8,	233,	225,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-21 09:45:00',	'Leaking containers'),
(10,	3,	31,	'DAMAGED',	5,	140,	135,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-22 10:15:00',	'Packaging defects'),
(11,	1,	5,	'EXPIRED',	10,	190,	180,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-18 11:00:00',	'Expired Nestomalt removed'),
(12,	2,	20,	'EXPIRED',	12,	292,	280,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-19 13:30:00',	'Expired balm stock removed'),
(13,	3,	11,	'EXPIRED',	8,	253,	245,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-20 14:45:00',	'Expired tea removed from shelf'),
(14,	1,	1,	'RETURNED',	5,	420,	425,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-26 15:00:00',	'Customer return - unopened bottles'),
(15,	2,	7,	'RETURNED',	3,	372,	375,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-27 16:30:00',	'Quality issue return'),
(16,	1,	10,	'ADJUSTMENT',	-20,	600,	580,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-23 09:00:00',	'Physical count adjustment - system correction'),
(17,	2,	24,	'ADJUSTMENT',	15,	370,	385,	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-24 10:30:00',	'Found missing stock during audit'),
(18,	3,	36,	'ADJUSTMENT',	-10,	225,	215,	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-25 11:45:00',	'Inventory discrepancy correction'),
(19,	1,	35,	'STOCK_OUT',	-120,	145,	25,	3,	'RDC_MANAGER',	'north_manager',	'2026-03-01 05:03:44',	'removed');

DROP TABLE IF EXISTS `stock_transfer_items`;
CREATE TABLE `stock_transfer_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `transfer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `requested_quantity` int NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `transfer_id` (`transfer_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `stock_transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers` (`transfer_id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfer_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `stock_transfer_items` (`item_id`, `transfer_id`, `product_id`, `requested_quantity`) VALUES
(1,	1,	1,	80),
(2,	1,	3,	60),
(3,	1,	13,	50),
(4,	1,	15,	70),
(5,	2,	2,	100),
(6,	2,	4,	80),
(7,	2,	5,	50),
(8,	2,	22,	90),
(9,	3,	13,	60),
(10,	3,	14,	50),
(11,	3,	15,	80),
(12,	3,	16,	70),
(13,	4,	7,	45),
(14,	4,	11,	60),
(15,	4,	18,	40),
(16,	4,	24,	100),
(17,	5,	1,	70),
(18,	5,	6,	50),
(19,	5,	22,	80),
(20,	5,	25,	45),
(21,	6,	31,	60),
(22,	6,	32,	70),
(23,	6,	33,	40),
(24,	6,	34,	35),
(25,	7,	18,	50),
(26,	7,	19,	45),
(27,	7,	7,	30),
(28,	7,	10,	100),
(29,	8,	1,	150),
(30,	8,	13,	120),
(31,	8,	22,	200),
(32,	9,	27,	50),
(33,	9,	28,	40),
(34,	9,	29,	35),
(35,	9,	30,	60),
(36,	10,	13,	70),
(37,	10,	15,	80),
(38,	10,	17,	50),
(39,	11,	1,	60),
(40,	11,	7,	35),
(41,	11,	18,	45),
(42,	11,	31,	40),
(43,	12,	2,	90),
(44,	12,	12,	50),
(45,	12,	24,	120),
(46,	12,	36,	80);

DROP TABLE IF EXISTS `stock_transfers`;
CREATE TABLE `stock_transfers` (
  `transfer_id` int NOT NULL AUTO_INCREMENT,
  `transfer_number` varchar(50) NOT NULL,
  `source_rdc_id` int NOT NULL,
  `destination_rdc_id` int NOT NULL,
  `requested_by` int NOT NULL,
  `requested_by_role` enum('RDC_CLERK','RDC_MANAGER') NOT NULL,
  `requested_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `request_reason` text,
  `is_urgent` tinyint(1) DEFAULT '0',
  `approval_status` enum('CLERK_REQUESTED','PENDING','APPROVED','REJECTED','CANCELLED','RECEIVED') DEFAULT 'CLERK_REQUESTED',
  `approved_by` int DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `approval_remarks` text,
  `current_status_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_date` timestamp NULL DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `delivery_notes` text,
  PRIMARY KEY (`transfer_id`),
  UNIQUE KEY `transfer_number` (`transfer_number`),
  KEY `source_rdc_id` (`source_rdc_id`),
  KEY `destination_rdc_id` (`destination_rdc_id`),
  CONSTRAINT `stock_transfers_ibfk_1` FOREIGN KEY (`source_rdc_id`) REFERENCES `rdcs` (`rdc_id`),
  CONSTRAINT `stock_transfers_ibfk_2` FOREIGN KEY (`destination_rdc_id`) REFERENCES `rdcs` (`rdc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `stock_transfers` (`transfer_id`, `transfer_number`, `source_rdc_id`, `destination_rdc_id`, `requested_by`, `requested_by_role`, `requested_date`, `request_reason`, `is_urgent`, `approval_status`, `approved_by`, `approval_date`, `approval_remarks`, `current_status_updated`, `completed_date`, `receiver_name`, `delivery_notes`) VALUES
(1,	'TRF-SOUTH-EAST-001',	2,	3,	6,	'RDC_CLERK',	'2026-02-20 09:00:00',	'Low stock alert - Critical items needed urgently for retail demand',	1,	'APPROVED',	4,	'2026-02-20 10:30:00',	'Approved. Stock available. Dispatch immediately.',	'0000-00-00 00:00:00',	'2026-02-22 14:30:00',	'Chaminda Rathnayake',	'All items received in good condition'),
(2,	'TRF-WEST-NORTH-001',	4,	1,	5,	'RDC_CLERK',	'2026-02-21 10:15:00',	'Replenishment needed for beverages due to high sales volume',	0,	'APPROVED',	3,	'2026-02-21 11:45:00',	'Transfer approved. Regular restock.',	'0000-00-00 00:00:00',	'2026-02-23 16:00:00',	'Nimal Fernando',	'Delivery completed successfully'),
(3,	'TRF-CENTRAL-SOUTH-001',	5,	2,	6,	'RDC_CLERK',	'2026-02-22 13:20:00',	'Emergency stock request - Running low on cleaning supplies',	1,	'APPROVED',	4,	'2026-02-22 14:00:00',	'Urgent request approved. Immediate dispatch.',	'0000-00-00 00:00:00',	'2026-02-24 10:15:00',	'Kumari Silva',	'Received all items'),
(4,	'TRF-NORTH-WEST-001',	1,	4,	6,	'RDC_CLERK',	'2026-02-23 11:00:00',	'Stock balancing - Excess inventory transfer',	0,	'APPROVED',	3,	'2026-02-23 15:30:00',	'Balanced transfer approved.',	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(5,	'TRF-SOUTH-NORTH-002',	2,	1,	5,	'RDC_CLERK',	'2026-02-28 09:30:00',	'Weekly restock - Beverages and personal care items needed',	0,	'PENDING',	NULL,	NULL,	NULL,	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(6,	'TRF-EAST-CENTRAL-001',	3,	5,	6,	'RDC_CLERK',	'2026-02-28 14:00:00',	'Critical stock shortage - Baby care products urgently needed',	1,	'PENDING',	NULL,	NULL,	NULL,	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(7,	'TRF-WEST-SOUTH-001',	4,	2,	6,	'RDC_CLERK',	'2026-03-01 08:15:00',	'Routine transfer - Health care and grocery items',	0,	'PENDING',	NULL,	NULL,	NULL,	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(8,	'TRF-NORTH-EAST-002',	1,	3,	5,	'RDC_CLERK',	'2026-02-25 10:00:00',	'Request for multiple product categories',	0,	'REJECTED',	3,	'2026-02-25 11:30:00',	'Insufficient stock at source. Request alternative RDC.',	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(9,	'TRF-CENTRAL-WEST-002',	5,	4,	5,	'RDC_CLERK',	'2026-02-26 12:00:00',	'Transfer for promotional stock buildup',	0,	'APPROVED',	3,	'2026-02-26 14:30:00',	'Approved for promotional campaign.',	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(10,	'TRF-SOUTH-CENTRAL-002',	2,	5,	6,	'RDC_CLERK',	'2026-02-27 09:45:00',	'Stock redistribution - Excess cleaning products',	0,	'APPROVED',	4,	'2026-02-27 10:15:00',	'Transfer approved. Proceed with dispatch.',	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(11,	'TRF-EAST-WEST-001',	3,	4,	5,	'RDC_CLERK',	'2026-02-27 15:30:00',	'Emergency transfer - Multiple categories needed',	1,	'APPROVED',	3,	'2026-02-27 16:00:00',	'Urgent transfer approved.',	'0000-00-00 00:00:00',	NULL,	NULL,	NULL),
(12,	'TRF-WEST-EAST-002',	4,	3,	6,	'RDC_CLERK',	'2026-02-28 11:00:00',	'Quarterly stock balancing transfer',	0,	'APPROVED',	3,	'2026-02-28 13:30:00',	'Regular quarterly transfer approved.',	'0000-00-00 00:00:00',	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `system_admins`;
CREATE TABLE `system_admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `system_admins_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `system_admins` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1,	'Admin Master',	'123 Admin Street, Colombo 07',	'0771234567',	'sysadmin1@mail.com',	1);

DROP TABLE IF EXISTS `transfer_status_logs`;
CREATE TABLE `transfer_status_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `transfer_id` int NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `changed_by` int NOT NULL,
  `change_by_role` varchar(50) NOT NULL,
  `change_by_name` varchar(255) DEFAULT NULL,
  `changed_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `transfer_id` (`transfer_id`),
  CONSTRAINT `transfer_status_logs_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers` (`transfer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `transfer_status_logs` (`log_id`, `transfer_id`, `previous_status`, `new_status`, `changed_by`, `change_by_role`, `change_by_name`, `changed_date`) VALUES
(1,	1,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-20 09:00:00'),
(2,	1,	'CLERK_REQUESTED',	'PENDING',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-20 09:05:00'),
(3,	1,	'PENDING',	'MANAGER_APPROVED',	4,	'RDC_MANAGER',	'Kumari Silva',	'2026-02-20 10:30:00'),
(4,	1,	'MANAGER_APPROVED',	'DISPATCHED',	4,	'RDC_MANAGER',	'Kumari Silva',	'2026-02-20 11:00:00'),
(5,	1,	'DISPATCHED',	'IN_TRANSIT',	8,	'RDC_DRIVER',	'Lasantha Mendis',	'2026-02-20 12:30:00'),
(6,	1,	'IN_TRANSIT',	'RECEIVED',	13,	'LOGISTICS_OFFICER',	'Chaminda Rathnayake',	'2026-02-22 14:30:00'),
(7,	2,	NULL,	'CLERK_REQUESTED',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-21 10:15:00'),
(8,	2,	'CLERK_REQUESTED',	'PENDING',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-21 10:20:00'),
(9,	2,	'PENDING',	'MANAGER_APPROVED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-21 11:45:00'),
(10,	2,	'MANAGER_APPROVED',	'DISPATCHED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-21 13:00:00'),
(11,	2,	'DISPATCHED',	'IN_TRANSIT',	7,	'RDC_DRIVER',	'Anil Kumar',	'2026-02-21 14:00:00'),
(12,	2,	'IN_TRANSIT',	'RECEIVED',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-23 16:00:00'),
(13,	3,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-22 13:20:00'),
(14,	3,	'CLERK_REQUESTED',	'PENDING',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-22 13:25:00'),
(15,	3,	'PENDING',	'MANAGER_APPROVED',	4,	'RDC_MANAGER',	'Kumari Silva',	'2026-02-22 14:00:00'),
(16,	3,	'MANAGER_APPROVED',	'DISPATCHED',	4,	'RDC_MANAGER',	'Kumari Silva',	'2026-02-22 15:30:00'),
(17,	3,	'DISPATCHED',	'IN_TRANSIT',	8,	'RDC_DRIVER',	'Lasantha Mendis',	'2026-02-22 16:00:00'),
(18,	3,	'IN_TRANSIT',	'RECEIVED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-24 10:15:00'),
(19,	4,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-23 11:00:00'),
(20,	4,	'CLERK_REQUESTED',	'PENDING',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-23 11:05:00'),
(21,	4,	'PENDING',	'MANAGER_APPROVED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-23 15:30:00'),
(22,	4,	'MANAGER_APPROVED',	'DISPATCHED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-23 17:00:00'),
(23,	4,	'DISPATCHED',	'IN_TRANSIT',	7,	'RDC_DRIVER',	'Anil Kumar',	'2026-02-24 08:00:00'),
(24,	5,	NULL,	'CLERK_REQUESTED',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-28 09:30:00'),
(25,	6,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-28 14:00:00'),
(26,	7,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-03-01 08:15:00'),
(27,	8,	NULL,	'CLERK_REQUESTED',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-25 10:00:00'),
(28,	8,	'CLERK_REQUESTED',	'PENDING',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-25 10:05:00'),
(29,	8,	'PENDING',	'REJECTED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-25 11:30:00'),
(30,	9,	NULL,	'CLERK_REQUESTED',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-26 12:00:00'),
(31,	9,	'CLERK_REQUESTED',	'PENDING',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-26 12:05:00'),
(32,	9,	'PENDING',	'MANAGER_APPROVED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-26 14:30:00'),
(33,	10,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-27 09:45:00'),
(34,	10,	'CLERK_REQUESTED',	'PENDING',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-27 09:50:00'),
(35,	10,	'PENDING',	'MANAGER_APPROVED',	4,	'RDC_MANAGER',	'Kumari Silva',	'2026-02-27 10:15:00'),
(36,	11,	NULL,	'CLERK_REQUESTED',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-27 15:30:00'),
(37,	11,	'CLERK_REQUESTED',	'PENDING',	5,	'RDC_CLERK',	'Nimal Fernando',	'2026-02-27 15:35:00'),
(38,	11,	'PENDING',	'MANAGER_APPROVED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-27 16:00:00'),
(39,	11,	'MANAGER_APPROVED',	'DISPATCHED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-27 17:30:00'),
(40,	12,	NULL,	'CLERK_REQUESTED',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-28 11:00:00'),
(41,	12,	'CLERK_REQUESTED',	'PENDING',	6,	'RDC_CLERK',	'Chamari Wijesinghe',	'2026-02-28 11:05:00'),
(42,	12,	'PENDING',	'MANAGER_APPROVED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-28 13:30:00'),
(43,	12,	'MANAGER_APPROVED',	'DISPATCHED',	3,	'RDC_MANAGER',	'Rajith Perera',	'2026-02-28 15:00:00'),
(44,	12,	'DISPATCHED',	'IN_TRANSIT',	7,	'RDC_DRIVER',	'Anil Kumar',	'2026-02-28 16:30:00');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','rdc_manager','rdc_clerk','rdc_sales_ref','logistics_officer','rdc_driver','head_office_manager','system_admin') DEFAULT 'customer',
  `rdc_id` int DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_google_id` (`google_id`),
  KEY `idx_users_reset_token` (`password_reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `rdc_id`, `google_id`, `password_reset_token`, `password_reset_expires`, `is_active`, `created_at`, `updated_at`) VALUES
(1,	'sysadmin1',	'sysadmin1@mail.com',	'$2a$12$s8Xc5QDqwUTQxU7SNdZabuPozBRLhggDpIdT31Nq29CiYmO/dK11y',	'system_admin',	NULL,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(2,	'headoffice1',	'headoffice1@mail.com',	'$2a$12$xM5yN7l1gDsMv.6KnwO/mO5/oXuhIYVEobnR9e0tS./FSrj9ssjZ2',	'head_office_manager',	NULL,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(3,	'north_manager',	'north_manager@mail.com',	'$2a$12$jK7xBCebwjX73LaMZSHsUuv/v/i6Sm3u5I/PPUSuD6zepXz4n1abK',	'rdc_manager',	1,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(4,	'south_manager',	'south_manager@mail.com',	'$2a$12$jK7xBCebwjX73LaMZSHsUuv/v/i6Sm3u5I/PPUSuD6zepXz4n1abK',	'rdc_manager',	2,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(5,	'north_clerk1',	'north_clerk1@mail.com',	'$2a$12$rqbJDAiyDaIp5HnoBtQwau7Jrnn/yfK7w09t5LJdkcnuY81JifvPS',	'rdc_clerk',	1,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(6,	'south_clerk1',	'south_clerk1@mail.com',	'$2a$12$rqbJDAiyDaIp5HnoBtQwau7Jrnn/yfK7w09t5LJdkcnuY81JifvPS',	'rdc_clerk',	2,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(7,	'north_driver1',	'north_driver1@mail.com',	'$2a$12$Um4nmEuV/qQn6K.VWDdGEegwpkPWRE/5lEB5MU0D6TdI0KdRVPThy',	'rdc_driver',	1,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(8,	'south_driver1',	'south_driver1@mail.com',	'$2a$12$Um4nmEuV/qQn6K.VWDdGEegwpkPWRE/5lEB5MU0D6TdI0KdRVPThy',	'rdc_driver',	2,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(9,	'customer1',	'customer1@mail.com',	'$2a$12$LmoUb76F4vbC0xM0HNvPOeJ2pMrSwC7/qo.0CQzx.prGoeLAeYV1u',	'customer',	1,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(10,	'customer2',	'customer2@mail.com',	'$2a$12$LmoUb76F4vbC0xM0HNvPOeJ2pMrSwC7/qo.0CQzx.prGoeLAeYV1u',	'customer',	2,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(11,	'customer3',	'customer3@mail.com',	'$2a$12$LmoUb76F4vbC0xM0HNvPOeJ2pMrSwC7/qo.0CQzx.prGoeLAeYV1u',	'customer',	3,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(12,	'north_sales_ref',	'north_sales_ref@mail.com',	'$2a$12$1HkXDI8UJLnc8wipwAlEyu02uARmJNJPgkFKA.VSKawpyMoBqhzUW',	'rdc_sales_ref',	1,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26'),
(13,	'north_logistics_officer',	'north_logistics_officer@mail.com',	'$2a$12$Ba1waognzY0yTamCnTx71eC6CRCm71J3WiI7Qg7b6BJ68RJPor4HW',	'logistics_officer',	1,	NULL,	NULL,	NULL,	1,	'2026-03-01 05:01:26',	'2026-03-01 05:01:26');

-- 2026-03-01 05:05:16
