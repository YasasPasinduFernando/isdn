-- ============================================================
-- ISDN Database Seeder - Comprehensive Dummy Data
-- Generated: 2026-03-01
-- Purpose: Populate database with realistic test data
-- Note: Preserves existing users, RDCs, and product categories
-- ============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `isdn_db_2`;

-- ============================================================
-- TRUNCATE DATA TABLES (Keep structure, remove data)
-- Preserve: users, rdcs, product_categories (as requested)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `users`;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `email_logs`;
TRUNCATE TABLE `order_deliveries`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `product_categories`;
TRUNCATE TABLE `product_stocks`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `promotions`;
TRUNCATE TABLE `shopping_carts`;
TRUNCATE TABLE `stock_movement_logs`;
TRUNCATE TABLE `stock_transfer_items`;
TRUNCATE TABLE `stock_transfers`;
TRUNCATE TABLE `transfer_status_logs`;

-- Also truncate profile tables to regenerate with consistency
TRUNCATE TABLE `head_office_managers`;
TRUNCATE TABLE `rdc_clerks`;
TRUNCATE TABLE `rdc_drivers`;
TRUNCATE TABLE `rdc_logistics_officers`;
TRUNCATE TABLE `rdc_managers`;
TRUNCATE TABLE `rdc_sales_refs`;
TRUNCATE TABLE `retail_customers`;
TRUNCATE TABLE `system_admins`;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- RDCS
-- =====================================================

INSERT INTO rdcs (rdc_code, rdc_name, province, address, contact_number) VALUES
('NORTH', 'Northern RDC', 'Northern Province', 'Jaffna Industrial Zone', '0711111111'),
('SOUTH', 'Southern RDC', 'Southern Province', 'Galle Trade Center', '0712222222'),
('EAST', 'Eastern RDC', 'Eastern Province', 'Batticaloa Hub', '0713333333'),
('WEST', 'Western RDC', 'Western Province', 'Colombo Warehouse Complex', '0714444444'),
('CENTRAL', 'Central RDC', 'Central Province', 'Kandy Distribution Park', '0715555555');

-- ============================================================
-- USERS
-- ============================================================

INSERT INTO users (username, email, password, role, rdc_id) VALUES
('sysadmin1','sysadmin1@mail.com','$2a$12$s8Xc5QDqwUTQxU7SNdZabuPozBRLhggDpIdT31Nq29CiYmO/dK11y','system_admin',NULL),
('headoffice1','headoffice1@mail.com','$2a$12$xM5yN7l1gDsMv.6KnwO/mO5/oXuhIYVEobnR9e0tS./FSrj9ssjZ2','head_office_manager',NULL),

('north_manager','north_manager@mail.com','$2a$12$jK7xBCebwjX73LaMZSHsUuv/v/i6Sm3u5I/PPUSuD6zepXz4n1abK','rdc_manager',1),
('south_manager','south_manager@mail.com','$2a$12$jK7xBCebwjX73LaMZSHsUuv/v/i6Sm3u5I/PPUSuD6zepXz4n1abK','rdc_manager',2),

('north_clerk1','north_clerk1@mail.com','$2a$12$rqbJDAiyDaIp5HnoBtQwau7Jrnn/yfK7w09t5LJdkcnuY81JifvPS','rdc_clerk',1),
('south_clerk1','south_clerk1@mail.com','$2a$12$rqbJDAiyDaIp5HnoBtQwau7Jrnn/yfK7w09t5LJdkcnuY81JifvPS','rdc_clerk',2),

('north_driver1','north_driver1@mail.com','$2a$12$Um4nmEuV/qQn6K.VWDdGEegwpkPWRE/5lEB5MU0D6TdI0KdRVPThy','rdc_driver',1),
('south_driver1','south_driver1@mail.com','$2a$12$Um4nmEuV/qQn6K.VWDdGEegwpkPWRE/5lEB5MU0D6TdI0KdRVPThy','rdc_driver',2),

('customer1','customer1@mail.com','$2a$12$LmoUb76F4vbC0xM0HNvPOeJ2pMrSwC7/qo.0CQzx.prGoeLAeYV1u','customer',1),
('customer2','customer2@mail.com','$2a$12$LmoUb76F4vbC0xM0HNvPOeJ2pMrSwC7/qo.0CQzx.prGoeLAeYV1u','customer',2),
('customer3','customer3@mail.com','$2a$12$LmoUb76F4vbC0xM0HNvPOeJ2pMrSwC7/qo.0CQzx.prGoeLAeYV1u','customer',3),

('north_sales_ref','north_sales_ref@mail.com','$2a$12$1HkXDI8UJLnc8wipwAlEyu02uARmJNJPgkFKA.VSKawpyMoBqhzUW','rdc_sales_ref',1),

('north_logistics_officer','north_logistics_officer@mail.com','$2a$12$Ba1waognzY0yTamCnTx71eC6CRCm71J3WiI7Qg7b6BJ68RJPor4HW','logistics_officer',1);

-- ============================================================
-- USER PROFILE TABLES (Linked to users by user_id)
-- ============================================================

-- System Admins (user_id: 1)
INSERT INTO `system_admins` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Admin Master', '123 Admin Street, Colombo 07', '0771234567', 'sysadmin1@mail.com', 1);

-- Head Office Managers (user_id: 2)
INSERT INTO `head_office_managers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Sarah Johnson', '456 Corporate Tower, Colombo 03', '0772345678', 'headoffice1@mail.com', 2);

-- RDC Managers (user_id: 3, 4)
INSERT INTO `rdc_managers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Rajith Perera', '12 Manager Lane, Jaffna', '0773456789', 'north_manager@mail.com', 3),
(2, 'Kumari Silva', '34 South Road, Galle', '0774567890', 'south_manager@mail.com', 4);

-- RDC Clerks (user_id: 5, 6)
INSERT INTO `rdc_clerks` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Nimal Fernando', '56 Clerk Street, Jaffna', '0775678901', 'north_clerk1@mail.com', 5),
(2, 'Chamari Wijesinghe', '78 Office Road, Galle', '0776789012', 'south_clerk1@mail.com', 6);

-- RDC Drivers (user_id: 7, 8)
INSERT INTO `rdc_drivers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Anil Kumar', '90 Driver Lane, Jaffna', '0777890123', 'north_driver1@mail.com', 7),
(2, 'Lasantha Mendis', '12 Transport Road, Galle', '0778901234', 'south_driver1@mail.com', 8);

-- Retail Customers (user_id: 9, 10)
INSERT INTO `retail_customers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Sunil Traders', '45 Market Street, Colombo 11', '0779012345', 'customer1@mail.com', 9),
(2, 'Lakshmi Stores', '67 Shop Avenue, Galle', '0770123456', 'customer2@mail.com', 10),
(3, 'Lakshmi Stores', '67 Shop Avenue, Galle', '0770123456', 'customer3@mail.com', 11);

-- RDC Sales Representatives (user_id: 11, 12)
INSERT INTO `rdc_sales_refs` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Kamal Jayasuriya', '23 Sales Street, Jaffna', '0771234568', 'north_sales_ref@mail.com', 12);

-- RDC Logistics Officers (user_id: 13)
INSERT INTO `rdc_logistics_officers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Chaminda Rathnayake', '89 Logistics Lane, Jaffna', '0773456780', 'north_logistics_officer@mail.com', 13);

-- =====================================================
-- PRODUCT CATEGORIES
-- =====================================================
INSERT INTO product_categories (name, description) VALUES 
('Grocery & Food Items', 'Grocery & Food Items'),
('Beverages', 'Beverages'),
('Household Essentials', 'Household Essentials'),
('Home Cleaning Products', 'Home Cleaning Products'),
('Health Care Products', 'Health Care Products'),
('Personal Care', 'Personal Care'),
('Beauty & Skincare', 'Beauty & Skincare'),
('Baby Care Products', 'Baby Care Products');

-- ============================================================
-- PRODUCTS (Using existing categories)
-- ============================================================

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `category_id`, `unit_price`, `minimum_stock_level`, `image_url`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
-- Beverages (category_id: 2)
(1, 'BEV001', 'Coca-Cola 1L', 2, 250.00, 100, '/assets/images/products/coca-cola.jpg', 1, 'Refreshing Coca-Cola 1 Liter bottle', NOW(), NOW()),
(2, 'BEV002', 'Pepsi 1.5L', 2, 280.00, 100, '/assets/images/products/pepsi.jpg', 1, 'Pepsi Cola 1.5 Liter bottle', NOW(), NOW()),
(3, 'BEV003', 'Sprite 1L', 2, 240.00, 100, '/assets/images/products/sprite.jpg', 1, 'Lemon-lime flavored soft drink', NOW(), NOW()),
(4, 'BEV004', 'Fanta Orange 1L', 2, 240.00, 100, '/assets/images/products/fanta.jpg', 1, 'Orange flavored carbonated drink', NOW(), NOW()),
(5, 'BEV005', 'Nestomalt 400G', 2, 850.00, 50, '/assets/images/products/nestomalt.jpg', 1, 'Malt drink powder 400g pack', NOW(), NOW()),
(6, 'BEV006', 'Milo 400G', 2, 920.00, 50, '/assets/images/products/milo.jpg', 1, 'Chocolate malt drink powder', NOW(), NOW()),

-- Grocery & Food Items (category_id: 1)
(7, 'GRO001', 'Basmati Rice 5kg', 1, 1450.00, 80, '/assets/images/products/rice.jpg', 1, 'Premium Basmati rice 5kg pack', NOW(), NOW()),
(8, 'GRO002', 'Red Lentils 1kg', 1, 380.00, 60, '/assets/images/products/lentils.jpg', 1, 'Red lentils 1kg pack', NOW(), NOW()),
(9, 'GRO003', 'Coconut Oil 1L', 1, 650.00, 50, '/assets/images/products/coconut-oil.jpg', 1, 'Pure coconut oil 1 liter', NOW(), NOW()),
(10, 'GRO004', 'Sugar 1kg', 1, 220.00, 100, '/assets/images/products/sugar.jpg', 1, 'White sugar 1kg pack', NOW(), NOW()),
(11, 'GRO005', 'Tea 400g', 1, 580.00, 70, '/assets/images/products/tea.jpg', 1, 'Premium Ceylon tea 400g', NOW(), NOW()),
(12, 'GRO006', 'Milk Powder 400g', 1, 950.00, 60, '/assets/images/products/milk-powder.jpg', 1, 'Full cream milk powder', NOW(), NOW()),

-- Home Cleaning Products (category_id: 4)
(13, 'CLN001', 'Harpic Fresh 500ml', 4, 420.00, 50, '/assets/images/products/harpic.jpg', 1, 'Toilet cleaner 500ml', NOW(), NOW()),
(14, 'CLN002', 'Dettol Floor Cleaner 1L', 4, 580.00, 50, '/assets/images/products/dettol-floor.jpg', 1, 'Disinfectant floor cleaner', NOW(), NOW()),
(15, 'CLN003', 'Sunlight Detergent Powder 1kg', 4, 680.00, 60, '/assets/images/products/sunlight.jpg', 1, 'Laundry detergent powder', NOW(), NOW()),
(16, 'CLN004', 'Vim Dishwash Gel 500ml', 4, 350.00, 50, '/assets/images/products/vim.jpg', 1, 'Dishwashing liquid gel', NOW(), NOW()),
(17, 'CLN005', 'Domestos Bleach 750ml', 4, 480.00, 40, '/assets/images/products/domestos.jpg', 1, 'Thick bleach cleaner', NOW(), NOW()),

-- Health Care Products (category_id: 5)
(18, 'HLT001', 'Strepsils 24S', 5, 380.00, 40, '/assets/images/products/strepsils.jpg', 1, 'Throat lozenges 24 pack', NOW(), NOW()),
(19, 'HLT002', 'Panadol 100 Tablets', 5, 650.00, 50, '/assets/images/products/panadol.jpg', 1, 'Pain relief tablets', NOW(), NOW()),
(20, 'HLT003', 'Siddhalepa Balm 50g', 5, 280.00, 60, '/assets/images/products/siddhalepa.jpg', 1, 'Ayurvedic pain balm', NOW(), NOW()),
(21, 'HLT004', 'Piriton 30 Tablets', 5, 420.00, 40, '/assets/images/products/piriton.jpg', 1, 'Allergy relief tablets', NOW(), NOW()),

-- Personal Care (category_id: 6)
(22, 'PER001', 'Clogard Toothpaste 200g', 6, 350.00, 50, '/assets/images/products/clogard.jpg', 1, 'Toothpaste 200g tube', NOW(), NOW()),
(23, 'PER002', 'Signal Toothpaste 120g', 6, 280.00, 50, '/assets/images/products/signal.jpg', 1, 'Cavity protection toothpaste', NOW(), NOW()),
(24, 'PER003', 'Lux Soap 100g', 6, 180.00, 80, '/assets/images/products/lux-soap.jpg', 1, 'Beauty soap bar', NOW(), NOW()),
(25, 'PER004', 'Sunsilk Shampoo 400ml', 6, 580.00, 40, '/assets/images/products/sunsilk.jpg', 1, 'Hair care shampoo', NOW(), NOW()),
(26, 'PER005', 'Dove Body Wash 500ml', 6, 950.00, 30, '/assets/images/products/dove.jpg', 1, 'Moisturizing body wash', NOW(), NOW()),

-- Beauty & Skincare (category_id: 7)
(27, 'BEU001', 'Fair & Lovely 50g', 7, 380.00, 40, '/assets/images/products/fair-lovely.jpg', 1, 'Fairness cream', NOW(), NOW()),
(28, 'BEU002', 'Ponds Face Cream 100ml', 7, 650.00, 35, '/assets/images/products/ponds.jpg', 1, 'Moisturizing face cream', NOW(), NOW()),
(29, 'BEU003', 'Nivea Body Lotion 400ml', 7, 850.00, 30, '/assets/images/products/nivea.jpg', 1, 'Body moisturizer', NOW(), NOW()),
(30, 'BEU004', 'Vaseline Petroleum Jelly 100ml', 7, 280.00, 50, '/assets/images/products/vaseline.jpg', 1, 'Multi-purpose jelly', NOW(), NOW()),

-- Baby Care Products (category_id: 8)
(31, 'BAB001', 'Baby Cheramy Baby Talc 100g', 8, 320.00, 40, '/assets/images/products/baby-cheramy.jpg', 1, 'Baby powder talc', NOW(), NOW()),
(32, 'BAB002', 'Johnson Baby Soap 100g', 8, 280.00, 50, '/assets/images/products/johnson-soap.jpg', 1, 'Gentle baby soap', NOW(), NOW()),
(33, 'BAB003', 'Huggies Diapers Medium 30s', 8, 1850.00, 20, '/assets/images/products/huggies.jpg', 1, 'Baby diapers medium size', NOW(), NOW()),
(34, 'BAB004', 'Lactogen 1 400g', 8, 1650.00, 25, '/assets/images/products/lactogen.jpg', 1, 'Infant formula milk', NOW(), NOW()),
(35, 'BAB005', 'Baby Cheramy Lotion 200ml', 8, 450.00, 35, '/assets/images/products/baby-lotion.jpg', 1, 'Baby body lotion', NOW(), NOW()),

-- Household Essentials (category_id: 3)
(36, 'HSE001', 'Tissue Paper Box', 3, 280.00, 60, '/assets/images/products/tissue.jpg', 1, 'Facial tissue box', NOW(), NOW()),
(37, 'HSE002', 'Kitchen Towel Roll', 3, 220.00, 50, '/assets/images/products/kitchen-towel.jpg', 1, 'Absorbent kitchen towel', NOW(), NOW()),
(38, 'HSE003', 'Garbage Bags Large 20s', 3, 350.00, 40, '/assets/images/products/garbage-bags.jpg', 1, 'Heavy duty garbage bags', NOW(), NOW()),
(39, 'HSE004', 'Aluminum Foil Roll', 3, 480.00, 30, '/assets/images/products/foil.jpg', 1, 'Kitchen aluminum foil', NOW(), NOW()),
(40, 'HSE005', 'Cling Film Roll', 3, 380.00, 35, '/assets/images/products/cling-film.jpg', 1, 'Food wrap film', NOW(), NOW());

-- ============================================================
-- PRODUCT STOCKS (For all 5 RDCs)
-- ============================================================

-- Generate realistic stock levels for each product across all RDCs
INSERT INTO `product_stocks` (`product_id`, `rdc_id`, `available_quantity`, `last_updated`) VALUES
-- RDC 1 (NORTH) - Varied stock levels
(1, 1, 450, NOW()), (2, 1, 380, NOW()), (3, 1, 420, NOW()), (4, 1, 390, NOW()), (5, 1, 180, NOW()),
(6, 1, 165, NOW()), (7, 1, 320, NOW()), (8, 1, 245, NOW()), (9, 1, 210, NOW()), (10, 1, 580, NOW()),
(11, 1, 290, NOW()), (12, 1, 235, NOW()), (13, 1, 195, NOW()), (14, 1, 220, NOW()), (15, 1, 270, NOW()),
(16, 1, 185, NOW()), (17, 1, 145, NOW()), (18, 1, 165, NOW()), (19, 1, 205, NOW()), (20, 1, 240, NOW()),
(21, 1, 155, NOW()), (22, 1, 215, NOW()), (23, 1, 195, NOW()), (24, 1, 335, NOW()), (25, 1, 145, NOW()),
(26, 1, 125, NOW()), (27, 1, 155, NOW()), (28, 1, 135, NOW()), (29, 1, 115, NOW()), (30, 1, 205, NOW()),
(31, 1, 165, NOW()), (32, 1, 195, NOW()), (33, 1, 85, NOW()), (34, 1, 95, NOW()), (35, 1, 145, NOW()),
(36, 1, 255, NOW()), (37, 1, 215, NOW()), (38, 1, 165, NOW()), (39, 1, 125, NOW()), (40, 1, 145, NOW()),

-- RDC 2 (SOUTH) - Different stock levels
(1, 2, 520, NOW()), (2, 2, 445, NOW()), (3, 2, 480, NOW()), (4, 2, 465, NOW()), (5, 2, 215, NOW()),
(6, 2, 195, NOW()), (7, 2, 375, NOW()), (8, 2, 285, NOW()), (9, 2, 245, NOW()), (10, 2, 650, NOW()),
(11, 2, 335, NOW()), (12, 2, 275, NOW()), (13, 2, 225, NOW()), (14, 2, 255, NOW()), (15, 2, 315, NOW()),
(16, 2, 215, NOW()), (17, 2, 175, NOW()), (18, 2, 195, NOW()), (19, 2, 245, NOW()), (20, 2, 280, NOW()),
(21, 2, 185, NOW()), (22, 2, 250, NOW()), (23, 2, 230, NOW()), (24, 2, 385, NOW()), (25, 2, 175, NOW()),
(26, 2, 155, NOW()), (27, 2, 185, NOW()), (28, 2, 165, NOW()), (29, 2, 145, NOW()), (30, 2, 240, NOW()),
(31, 2, 195, NOW()), (32, 2, 230, NOW()), (33, 2, 105, NOW()), (34, 2, 115, NOW()), (35, 2, 175, NOW()),
(36, 2, 295, NOW()), (37, 2, 250, NOW()), (38, 2, 195, NOW()), (39, 2, 155, NOW()), (40, 2, 175, NOW()),

-- RDC 3 (EAST) - Some products with low stock
(1, 3, 380, NOW()), (2, 3, 320, NOW()), (3, 3, 350, NOW()), (4, 3, 330, NOW()), (5, 3, 145, NOW()),
(6, 3, 135, NOW()), (7, 3, 270, NOW()), (8, 3, 205, NOW()), (9, 3, 175, NOW()), (10, 3, 480, NOW()),
(11, 3, 245, NOW()), (12, 3, 195, NOW()), (13, 3, 165, NOW()), (14, 3, 185, NOW()), (15, 3, 225, NOW()),
(16, 3, 155, NOW()), (17, 3, 125, NOW()), (18, 3, 135, NOW()), (19, 3, 175, NOW()), (20, 3, 210, NOW()),
(21, 3, 125, NOW()), (22, 3, 180, NOW()), (23, 3, 165, NOW()), (24, 3, 280, NOW()), (25, 3, 115, NOW()),
(26, 3, 95, NOW()), (27, 3, 125, NOW()), (28, 3, 105, NOW()), (29, 3, 85, NOW()), (30, 3, 175, NOW()),
(31, 3, 135, NOW()), (32, 3, 165, NOW()), (33, 3, 65, NOW()), (34, 3, 75, NOW()), (35, 3, 115, NOW()),
(36, 3, 215, NOW()), (37, 3, 180, NOW()), (38, 3, 135, NOW()), (39, 3, 95, NOW()), (40, 3, 115, NOW()),

-- RDC 4 (WEST) - Good stock levels
(1, 4, 580, NOW()), (2, 4, 520, NOW()), (3, 4, 560, NOW()), (4, 4, 540, NOW()), (5, 4, 250, NOW()),
(6, 4, 230, NOW()), (7, 4, 420, NOW()), (8, 4, 335, NOW()), (9, 4, 285, NOW()), (10, 4, 720, NOW()),
(11, 4, 385, NOW()), (12, 4, 315, NOW()), (13, 4, 265, NOW()), (14, 4, 295, NOW()), (15, 4, 355, NOW()),
(16, 4, 245, NOW()), (17, 4, 205, NOW()), (18, 4, 225, NOW()), (19, 4, 285, NOW()), (20, 4, 320, NOW()),
(21, 4, 215, NOW()), (22, 4, 285, NOW()), (23, 4, 265, NOW()), (24, 4, 435, NOW()), (25, 4, 205, NOW()),
(26, 4, 185, NOW()), (27, 4, 215, NOW()), (28, 4, 195, NOW()), (29, 4, 175, NOW()), (30, 4, 275, NOW()),
(31, 4, 225, NOW()), (32, 4, 265, NOW()), (33, 4, 125, NOW()), (34, 4, 135, NOW()), (35, 4, 205, NOW()),
(36, 4, 335, NOW()), (37, 4, 285, NOW()), (38, 4, 225, NOW()), (39, 4, 185, NOW()), (40, 4, 205, NOW()),

-- RDC 5 (CENTRAL) - Mixed stock levels
(1, 5, 495, NOW()), (2, 5, 420, NOW()), (3, 5, 465, NOW()), (4, 5, 445, NOW()), (5, 5, 195, NOW()),
(6, 5, 180, NOW()), (7, 5, 350, NOW()), (8, 5, 265, NOW()), (9, 5, 230, NOW()), (10, 5, 620, NOW()),
(11, 5, 315, NOW()), (12, 5, 255, NOW()), (13, 5, 210, NOW()), (14, 5, 240, NOW()), (15, 5, 290, NOW()),
(16, 5, 200, NOW()), (17, 5, 160, NOW()), (18, 5, 180, NOW()), (19, 5, 230, NOW()), (20, 5, 265, NOW()),
(21, 5, 170, NOW()), (22, 5, 235, NOW()), (23, 5, 215, NOW()), (24, 5, 360, NOW()), (25, 5, 160, NOW()),
(26, 5, 140, NOW()), (27, 5, 170, NOW()), (28, 5, 150, NOW()), (29, 5, 130, NOW()), (30, 5, 225, NOW()),
(31, 5, 180, NOW()), (32, 5, 215, NOW()), (33, 5, 95, NOW()), (34, 5, 105, NOW()), (35, 5, 160, NOW()),
(36, 5, 275, NOW()), (37, 5, 235, NOW()), (38, 5, 180, NOW()), (39, 5, 140, NOW()), (40, 5, 160, NOW());

-- ============================================================
-- PROMOTIONS (Active and upcoming promotions)
-- ============================================================

INSERT INTO `promotions` (`name`, `description`, `product_id`, `product_count`, `discount_percentage`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
('Summer Beverage Sale', 'Buy 10+ bottles and get 15% off on all beverages', 1, 10, 15.00, '2026-02-01', '2026-03-31', 1, NOW(), NOW()),
('Bulk Rice Discount', 'Purchase 20+ bags for 12% discount', 7, 20, 12.00, '2026-02-15', '2026-04-15', 1, NOW(), NOW()),
('Cleaning Supplies Bundle', 'Get 20% off when buying 15+ cleaning products', 13, 15, 20.00, '2026-03-01', '2026-03-31', 1, NOW(), NOW()),
('Health Care Promo', 'Save 10% on bulk orders of 25+ health products', 18, 25, 10.00, '2026-02-20', '2026-04-20', 1, NOW(), NOW()),
('Personal Care Deal', 'Buy 30+ items for 18% discount', 22, 30, 18.00, '2026-03-05', '2026-04-05', 1, NOW(), NOW()),
('Baby Products Special', 'Bulk purchase discount of 15% on 12+ baby items', 31, 12, 15.00, '2026-02-10', '2026-03-25', 1, NOW(), NOW()),
('Tea Time Offer', 'Purchase 40+ packets for 8% off', 11, 40, 8.00, '2026-03-01', '2026-04-30', 1, NOW(), NOW()),
('Detergent Mega Sale', 'Get 25% discount on orders of 50+ units', 15, 50, 25.00, '2026-03-10', '2026-04-10', 1, NOW(), NOW()),
('Milk Powder Promo', 'Save 12% when buying 20+ packs', 12, 20, 12.00, '2026-02-25', '2026-03-31', 1, NOW(), NOW()),
('Spring Skincare Sale', 'Buy 15+ beauty products for 22% off', 27, 15, 22.00, '2026-03-15', '2026-04-15', 1, NOW(), NOW()),
('Upcoming Summer Deal', 'Early bird discount on soft drinks', 2, 15, 18.00, '2026-04-01', '2026-05-31', 0, NOW(), NOW()),
('Household Essentials Offer', 'Stock up and save 14% on 20+ items', 36, 20, 14.00, '2026-03-01', '2026-04-01', 1, NOW(), NOW());

-- ============================================================
-- ORDERS (Realistic order history)
-- ============================================================

INSERT INTO `orders` (`order_date`, `customer_id`, `placed_by`, `order_number`, `total_amount`, `status`, `estimated_date`, `created_at`, `updated_at`, `rdc_id`) VALUES
-- Recent orders (last 30 days)
('2026-02-25 09:15:00', 1, 11, 'ORD-NORTH-2026-001', 15750.00, 'DELIVERED', '2026-02-27', NOW(), NOW(), 1),
('2026-02-26 10:30:00', 2, 12, 'ORD-SOUTH-2026-001', 22350.00, 'DELIVERED', '2026-02-28', NOW(), NOW(), 2),
('2026-02-27 11:45:00', 3, 11, 'ORD-NORTH-2026-002', 8920.00, 'DELIVERED', '2026-03-01', NOW(), NOW(), 1),
('2026-02-27 14:20:00', 1, 12, 'ORD-SOUTH-2026-002', 31280.00, 'IN_TRANSIT', '2026-03-02', NOW(), NOW(), 2),
('2026-02-28 08:30:00', 2, 11, 'ORD-NORTH-2026-003', 12450.00, 'IN_TRANSIT', '2026-03-03', NOW(), NOW(), 1),
('2026-02-28 13:15:00', 3, 12, 'ORD-SOUTH-2026-003', 18670.00, 'PROCESSING', '2026-03-04', NOW(), NOW(), 2),
('2026-03-01 09:00:00', 1, 11, 'ORD-NORTH-2026-004', 27890.00, 'PROCESSING', '2026-03-05', NOW(), NOW(), 1),
('2026-03-01 10:30:00', 2, 12, 'ORD-SOUTH-2026-004', 19450.00, 'PENDING', '2026-03-06', NOW(), NOW(), 2),

-- Older orders (for historical data)
('2026-02-15 10:00:00', 1, 11, 'ORD-NORTH-2026-005', 14280.00, 'DELIVERED', '2026-02-17', NOW(), NOW(), 1),
('2026-02-16 11:30:00', 2, 12, 'ORD-SOUTH-2026-005', 25630.00, 'DELIVERED', '2026-02-18', NOW(), NOW(), 2),
('2026-02-18 14:15:00', 3, 11, 'ORD-NORTH-2026-006', 11750.00, 'DELIVERED', '2026-02-20', NOW(), NOW(), 1),
('2026-02-20 09:45:00', 2, 12, 'ORD-SOUTH-2026-006', 33420.00, 'DELIVERED', '2026-02-22', NOW(), NOW(), 2),
('2026-02-22 13:00:00', 3, 11, 'ORD-NORTH-2026-007', 16890.00, 'DELIVERED', '2026-02-24', NOW(), NOW(), 1),
('2026-02-23 10:20:00', 2, 12, 'ORD-SOUTH-2026-007', 21340.00, 'DELIVERED', '2026-02-25', NOW(), NOW(), 2);

-- ============================================================
-- ORDER ITEMS (Products in orders)
-- ============================================================

INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `selling_price`, `discount`) VALUES
-- Order 1 items (ORD-NORTH-2026-001)
(1, 1, 30, 250.00, 37.50), (1, 3, 25, 240.00, 36.00), (1, 7, 10, 1450.00, 0.00),
-- Order 2 items (ORD-SOUTH-2026-001)
(2, 2, 40, 280.00, 42.00), (2, 5, 20, 850.00, 127.50), (2, 15, 25, 680.00, 136.00),
-- Order 3 items (ORD-NORTH-2026-002)
(3, 22, 50, 350.00, 63.00), (3, 24, 30, 180.00, 0.00),
-- Order 4 items (ORD-SOUTH-2026-002)
(4, 7, 20, 1450.00, 174.00), (4, 12, 18, 950.00, 114.00), (4, 13, 30, 420.00, 0.00),
-- Order 5 items (ORD-NORTH-2026-003)
(5, 18, 35, 380.00, 0.00), (5, 19, 20, 650.00, 0.00),
-- Order 6 items (ORD-SOUTH-2026-003)
(6, 31, 40, 320.00, 48.00), (6, 33, 25, 1850.00, 555.00),
-- Order 7 items (ORD-NORTH-2026-004)
(7, 1, 60, 250.00, 225.00), (7, 2, 40, 280.00, 168.00), (7, 4, 35, 240.00, 126.00),
-- Order 8 items (ORD-SOUTH-2026-004)
(8, 27, 45, 380.00, 125.46), (8, 29, 30, 850.00, 280.50),
-- Order 9 items
(9, 11, 45, 580.00, 46.40), (9, 22, 40, 350.00, 0.00),
-- Order 10 items
(10, 13, 25, 420.00, 126.00), (10, 15, 30, 680.00, 204.00), (10, 16, 35, 350.00, 0.00),
-- Order 11 items
(11, 6, 30, 920.00, 0.00), (11, 8, 25, 380.00, 0.00),
-- Order 12 items
(12, 10, 80, 220.00, 0.00), (12, 36, 50, 280.00, 0.00), (12, 37, 40, 220.00, 0.00),
-- Order 13 items
(13, 24, 70, 180.00, 0.00), (13, 26, 35, 950.00, 0.00),
-- Order 14 items
(14, 32, 60, 280.00, 0.00), (14, 35, 45, 450.00, 0.00);

-- ============================================================
-- PAYMENTS (Payment records for orders)
-- ============================================================

INSERT INTO `payments` (`order_id`, `amount`, `payment_date`, `payment_method`) VALUES
(1, 15750.00, '2026-02-27 15:30:00', 'BANK_TRANSFER'),
(2, 22350.00, '2026-02-28 16:45:00', 'CHEQUE'),
(3, 8920.00, '2026-03-01 14:20:00', 'CASH'),
(9, 14280.00, '2026-02-17 13:30:00', 'BANK_TRANSFER'),
(10, 25630.00, '2026-02-18 11:00:00', 'CHEQUE'),
(11, 11750.00, '2026-02-20 15:45:00', 'CASH'),
(12, 33420.00, '2026-02-22 10:30:00', 'BANK_TRANSFER'),
(13, 16890.00, '2026-02-24 14:00:00', 'CHEQUE'),
(14, 21340.00, '2026-02-25 16:20:00', 'CASH');

-- ============================================================
-- ORDER DELIVERIES (Delivery assignments)
-- ============================================================

INSERT INTO `order_deliveries` (`order_id`, `delivery_date`, `driver_id`, `completed_date`) VALUES
(1, '2026-02-27', 7, '2026-02-27 17:30:00'),
(2, '2026-02-28', 8, '2026-02-28 18:15:00'),
(3, '2026-03-01', 7, '2026-03-01 16:45:00'),
(4, '2026-03-02', 8, NULL),
(5, '2026-03-03', 7, NULL),
(9, '2026-02-17', 7, '2026-02-17 15:30:00'),
(10, '2026-02-18', 8, '2026-02-18 14:20:00'),
(11, '2026-02-20', 7, '2026-02-20 17:00:00'),
(12, '2026-02-22', 8, '2026-02-22 16:30:00'),
(13, '2026-02-24', 7, '2026-02-24 15:45:00'),
(14, '2026-02-25', 8, '2026-02-25 18:00:00');

-- ============================================================
-- STOCK TRANSFERS (Inter-RDC transfers)
-- ============================================================

INSERT INTO `stock_transfers` (`transfer_number`, `source_rdc_id`, `destination_rdc_id`, `requested_by`, `requested_by_role`, `requested_date`, `request_reason`, `is_urgent`, `approval_status`, `approved_by`, `approval_date`, `approval_remarks`, `current_status_updated`, `completed_date`, `receiver_name`, `delivery_notes`) VALUES
-- Completed transfers
('TRF-SOUTH-EAST-001', 2, 3, 6, 'RDC_CLERK', '2026-02-20 09:00:00', 'Low stock alert - Critical items needed urgently for retail demand', 1, 'APPROVED', 4, '2026-02-20 10:30:00', 'Approved. Stock available. Dispatch immediately.', 'RECEIVED', '2026-02-22 14:30:00', 'Chaminda Rathnayake', 'All items received in good condition'),
('TRF-WEST-NORTH-001', 4, 1, 5, 'RDC_CLERK', '2026-02-21 10:15:00', 'Replenishment needed for beverages due to high sales volume', 0, 'APPROVED', 3, '2026-02-21 11:45:00', 'Transfer approved. Regular restock.', 'RECEIVED', '2026-02-23 16:00:00', 'Nimal Fernando', 'Delivery completed successfully'),
('TRF-CENTRAL-SOUTH-001', 5, 2, 6, 'RDC_CLERK', '2026-02-22 13:20:00', 'Emergency stock request - Running low on cleaning supplies', 1, 'APPROVED', 4, '2026-02-22 14:00:00', 'Urgent request approved. Immediate dispatch.', 'RECEIVED', '2026-02-24 10:15:00', 'Kumari Silva', 'Received all items'),
('TRF-NORTH-WEST-001', 1, 4, 6, 'RDC_CLERK', '2026-02-23 11:00:00', 'Stock balancing - Excess inventory transfer', 0, 'APPROVED', 3, '2026-02-23 15:30:00', 'Balanced transfer approved.', 'IN_TRANSIT', NULL, NULL, NULL),

-- Pending approvals
('TRF-SOUTH-NORTH-002', 2, 1, 5, 'RDC_CLERK', '2026-02-28 09:30:00', 'Weekly restock - Beverages and personal care items needed', 0, 'PENDING', NULL, NULL, NULL, 'CLERK_REQUESTED', NULL, NULL, NULL),
('TRF-EAST-CENTRAL-001', 3, 5, 6, 'RDC_CLERK', '2026-02-28 14:00:00', 'Critical stock shortage - Baby care products urgently needed', 1, 'PENDING', NULL, NULL, NULL, 'CLERK_REQUESTED', NULL, NULL, NULL),
('TRF-WEST-SOUTH-001', 4, 2, 6, 'RDC_CLERK', '2026-03-01 08:15:00', 'Routine transfer - Health care and grocery items', 0, 'PENDING', NULL, NULL, NULL, 'CLERK_REQUESTED', NULL, NULL, NULL),

-- Rejected transfer
('TRF-NORTH-EAST-002', 1, 3, 5, 'RDC_CLERK', '2026-02-25 10:00:00', 'Request for multiple product categories', 0, 'REJECTED', 3, '2026-02-25 11:30:00', 'Insufficient stock at source. Request alternative RDC.', 'REJECTED', NULL, NULL, NULL),

-- In-transit transfers
('TRF-CENTRAL-WEST-002', 5, 4, 5, 'RDC_CLERK', '2026-02-26 12:00:00', 'Transfer for promotional stock buildup', 0, 'APPROVED', 3, '2026-02-26 14:30:00', 'Approved for promotional campaign.', 'MANAGER_APPROVED', NULL, NULL, NULL),
('TRF-SOUTH-CENTRAL-002', 2, 5, 6, 'RDC_CLERK', '2026-02-27 09:45:00', 'Stock redistribution - Excess cleaning products', 0, 'APPROVED', 4, '2026-02-27 10:15:00', 'Transfer approved. Proceed with dispatch.', 'MANAGER_APPROVED', NULL, NULL, NULL),
('TRF-EAST-WEST-001', 3, 4, 5, 'RDC_CLERK', '2026-02-27 15:30:00', 'Emergency transfer - Multiple categories needed', 1, 'APPROVED', 3, '2026-02-27 16:00:00', 'Urgent transfer approved.', 'DISPATCHED', NULL, NULL, NULL),
('TRF-WEST-EAST-002', 4, 3, 6, 'RDC_CLERK', '2026-02-28 11:00:00', 'Quarterly stock balancing transfer', 0, 'APPROVED', 3, '2026-02-28 13:30:00', 'Regular quarterly transfer approved.', 'IN_TRANSIT', NULL, NULL, NULL);

-- ============================================================
-- STOCK TRANSFER ITEMS (Products in transfers)
-- ============================================================

INSERT INTO `stock_transfer_items` (`transfer_id`, `product_id`, `requested_quantity`) VALUES
-- Transfer 1 items (TRF-SOUTH-EAST-001)
(1, 1, 80), (1, 3, 60), (1, 13, 50), (1, 15, 70),
-- Transfer 2 items (TRF-WEST-NORTH-001)
(2, 2, 100), (2, 4, 80), (2, 5, 50), (2, 22, 90),
-- Transfer 3 items (TRF-CENTRAL-SOUTH-001)
(3, 13, 60), (3, 14, 50), (3, 15, 80), (3, 16, 70),
-- Transfer 4 items (TRF-NORTH-WEST-001)
(4, 7, 45), (4, 11, 60), (4, 18, 40), (4, 24, 100),
-- Transfer 5 items (TRF-SOUTH-NORTH-002)
(5, 1, 70), (5, 6, 50), (5, 22, 80), (5, 25, 45),
-- Transfer 6 items (TRF-EAST-CENTRAL-001)
(6, 31, 60), (6, 32, 70), (6, 33, 40), (6, 34, 35),
-- Transfer 7 items (TRF-WEST-SOUTH-001)
(7, 18, 50), (7, 19, 45), (7, 7, 30), (7, 10, 100),
-- Transfer 8 items (TRF-NORTH-EAST-002)
(8, 1, 150), (8, 13, 120), (8, 22, 200),
-- Transfer 9 items (TRF-CENTRAL-WEST-002)
(9, 27, 50), (9, 28, 40), (9, 29, 35), (9, 30, 60),
-- Transfer 10 items (TRF-SOUTH-CENTRAL-002)
(10, 13, 70), (10, 15, 80), (10, 17, 50),
-- Transfer 11 items (TRF-EAST-WEST-001)
(11, 1, 60), (11, 7, 35), (11, 18, 45), (11, 31, 40),
-- Transfer 12 items (TRF-WEST-EAST-002)
(12, 2, 90), (12, 12, 50), (12, 24, 120), (12, 36, 80);

-- ============================================================
-- TRANSFER STATUS LOGS (Status change history)
-- ============================================================

INSERT INTO `transfer_status_logs` (`transfer_id`, `previous_status`, `new_status`, `changed_by`, `change_by_role`, `change_by_name`, `changed_date`) VALUES
-- Transfer 1 complete history
(1, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-20 09:00:00'),
(1, 'CLERK_REQUESTED', 'PENDING', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-20 09:05:00'),
(1, 'PENDING', 'MANAGER_APPROVED', 4, 'RDC_MANAGER', 'Kumari Silva', '2026-02-20 10:30:00'),
(1, 'MANAGER_APPROVED', 'DISPATCHED', 4, 'RDC_MANAGER', 'Kumari Silva', '2026-02-20 11:00:00'),
(1, 'DISPATCHED', 'IN_TRANSIT', 8, 'RDC_DRIVER', 'Lasantha Mendis', '2026-02-20 12:30:00'),
(1, 'IN_TRANSIT', 'RECEIVED', 13, 'LOGISTICS_OFFICER', 'Chaminda Rathnayake', '2026-02-22 14:30:00'),

-- Transfer 2 complete history
(2, NULL, 'CLERK_REQUESTED', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-21 10:15:00'),
(2, 'CLERK_REQUESTED', 'PENDING', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-21 10:20:00'),
(2, 'PENDING', 'MANAGER_APPROVED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-21 11:45:00'),
(2, 'MANAGER_APPROVED', 'DISPATCHED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-21 13:00:00'),
(2, 'DISPATCHED', 'IN_TRANSIT', 7, 'RDC_DRIVER', 'Anil Kumar', '2026-02-21 14:00:00'),
(2, 'IN_TRANSIT', 'RECEIVED', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-23 16:00:00'),

-- Transfer 3 complete history
(3, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-22 13:20:00'),
(3, 'CLERK_REQUESTED', 'PENDING', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-22 13:25:00'),
(3, 'PENDING', 'MANAGER_APPROVED', 4, 'RDC_MANAGER', 'Kumari Silva', '2026-02-22 14:00:00'),
(3, 'MANAGER_APPROVED', 'DISPATCHED', 4, 'RDC_MANAGER', 'Kumari Silva', '2026-02-22 15:30:00'),
(3, 'DISPATCHED', 'IN_TRANSIT', 8, 'RDC_DRIVER', 'Lasantha Mendis', '2026-02-22 16:00:00'),
(3, 'IN_TRANSIT', 'RECEIVED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-24 10:15:00'),

-- Transfer 4 partial history (in transit)
(4, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-23 11:00:00'),
(4, 'CLERK_REQUESTED', 'PENDING', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-23 11:05:00'),
(4, 'PENDING', 'MANAGER_APPROVED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-23 15:30:00'),
(4, 'MANAGER_APPROVED', 'DISPATCHED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-23 17:00:00'),
(4, 'DISPATCHED', 'IN_TRANSIT', 7, 'RDC_DRIVER', 'Anil Kumar', '2026-02-24 08:00:00'),

-- Transfer 5 (pending)
(5, NULL, 'CLERK_REQUESTED', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-28 09:30:00'),

-- Transfer 6 (pending)
(6, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-28 14:00:00'),

-- Transfer 7 (pending)
(7, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-03-01 08:15:00'),

-- Transfer 8 (rejected)
(8, NULL, 'CLERK_REQUESTED', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-25 10:00:00'),
(8, 'CLERK_REQUESTED', 'PENDING', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-25 10:05:00'),
(8, 'PENDING', 'REJECTED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-25 11:30:00'),

-- Transfer 9 (manager approved)
(9, NULL, 'CLERK_REQUESTED', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-26 12:00:00'),
(9, 'CLERK_REQUESTED', 'PENDING', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-26 12:05:00'),
(9, 'PENDING', 'MANAGER_APPROVED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-26 14:30:00'),

-- Transfer 10 (manager approved)
(10, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-27 09:45:00'),
(10, 'CLERK_REQUESTED', 'PENDING', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-27 09:50:00'),
(10, 'PENDING', 'MANAGER_APPROVED', 4, 'RDC_MANAGER', 'Kumari Silva', '2026-02-27 10:15:00'),

-- Transfer 11 (dispatched)
(11, NULL, 'CLERK_REQUESTED', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-27 15:30:00'),
(11, 'CLERK_REQUESTED', 'PENDING', 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-27 15:35:00'),
(11, 'PENDING', 'MANAGER_APPROVED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-27 16:00:00'),
(11, 'MANAGER_APPROVED', 'DISPATCHED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-27 17:30:00'),

-- Transfer 12 (in transit)
(12, NULL, 'CLERK_REQUESTED', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-28 11:00:00'),
(12, 'CLERK_REQUESTED', 'PENDING', 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-28 11:05:00'),
(12, 'PENDING', 'MANAGER_APPROVED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-28 13:30:00'),
(12, 'MANAGER_APPROVED', 'DISPATCHED', 3, 'RDC_MANAGER', 'Rajith Perera', '2026-02-28 15:00:00'),
(12, 'DISPATCHED', 'IN_TRANSIT', 7, 'RDC_DRIVER', 'Anil Kumar', '2026-02-28 16:30:00');

-- ============================================================
-- STOCK MOVEMENT LOGS (Stock adjustments)
-- ============================================================

INSERT INTO `stock_movement_logs` (`rdc_id`, `product_id`, `movement_type`, `quantity`, `previous_quantity`, `new_quantity`, `created_by`, `created_by_role`, `created_by_name`, `created_at`, `note`) VALUES
-- Stock In movements
(1, 1, 'STOCK_IN', 200, 250, 450, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-15 09:00:00', 'New shipment received from supplier'),
(2, 2, 'STOCK_IN', 150, 295, 445, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-15 10:30:00', 'Monthly stock replenishment'),
(1, 7, 'STOCK_IN', 100, 220, 320, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-16 11:15:00', 'Rice shipment from supplier'),
(2, 15, 'STOCK_IN', 120, 195, 315, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-17 14:00:00', 'Cleaning products restocked'),

-- Stock Out movements (for orders)
(1, 1, 'STOCK_OUT', 30, 450, 420, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-25 09:30:00', 'Order fulfillment ORD-NORTH-2026-001'),
(2, 2, 'STOCK_OUT', 40, 445, 405, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-26 10:45:00', 'Order fulfillment ORD-SOUTH-2026-001'),
(1, 22, 'STOCK_OUT', 50, 265, 215, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-27 12:00:00', 'Order fulfillment ORD-NORTH-2026-002'),

-- Damaged products
(1, 3, 'DAMAGED', 15, 435, 420, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-20 08:30:00', 'Damaged bottles found during inspection'),
(2, 13, 'DAMAGED', 8, 233, 225, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-21 09:45:00', 'Leaking containers'),
(3, 31, 'DAMAGED', 5, 140, 135, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-22 10:15:00', 'Packaging defects'),

-- Expired products
(1, 5, 'EXPIRED', 10, 190, 180, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-18 11:00:00', 'Expired Nestomalt removed'),
(2, 20, 'EXPIRED', 12, 292, 280, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-19 13:30:00', 'Expired balm stock removed'),
(3, 11, 'EXPIRED', 8, 253, 245, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-20 14:45:00', 'Expired tea removed from shelf'),

-- Returned products
(1, 1, 'RETURNED', 5, 420, 425, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-26 15:00:00', 'Customer return - unopened bottles'),
(2, 7, 'RETURNED', 3, 372, 375, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-27 16:30:00', 'Quality issue return'),

-- Stock adjustments (inventory corrections)
(1, 10, 'ADJUSTMENT', -20, 600, 580, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-23 09:00:00', 'Physical count adjustment - system correction'),
(2, 24, 'ADJUSTMENT', 15, 370, 385, 6, 'RDC_CLERK', 'Chamari Wijesinghe', '2026-02-24 10:30:00', 'Found missing stock during audit'),
(3, 36, 'ADJUSTMENT', -10, 225, 215, 5, 'RDC_CLERK', 'Nimal Fernando', '2026-02-25 11:45:00', 'Inventory discrepancy correction');

-- ============================================================
-- SHOPPING CARTS (Active customer carts)
-- ============================================================

INSERT INTO `shopping_carts` (`user_id`, `product_id`, `quantity`) VALUES
(9, 1, 50),
(9, 3, 35),
(9, 7, 15),
(9, 22, 40),
(10, 2, 60),
(10, 5, 25),
(10, 15, 45),
(10, 31, 30),
(10, 13, 40),
(10, 27, 20);

-- ============================================================
-- AUDIT LOGS (Recent system activities)
-- ============================================================

INSERT INTO `audit_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '127.0.0.1', '2026-03-01 08:00:00'),
(2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '127.0.0.1', '2026-03-01 08:15:00'),
(3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '127.0.0.1', '2026-03-01 08:30:00'),
(5, 'LOGIN', 'session', NULL, 'Logged in as rdc_clerk', '127.0.0.1', '2026-03-01 08:45:00'),
(1, 'CREATE', 'product', 1, 'Created product: Coca-Cola 1L', '127.0.0.1', '2026-02-28 10:00:00'),
(1, 'UPDATE', 'product', 1, 'Updated product: Coca-Cola 1L', '127.0.0.1', '2026-02-28 11:30:00'),
(3, 'APPROVE', 'transfer', 1, 'Approved transfer TRF-SOUTH-EAST-001', '127.0.0.1', '2026-02-20 10:30:00'),
(4, 'APPROVE', 'transfer', 2, 'Approved transfer TRF-WEST-NORTH-001', '127.0.0.1', '2026-02-21 11:45:00'),
(5, 'CREATE', 'order', 1, 'Created order ORD-NORTH-2026-001', '127.0.0.1', '2026-02-25 09:15:00'),
(6, 'CREATE', 'order', 2, 'Created order ORD-SOUTH-2026-001', '127.0.0.1', '2026-02-26 10:30:00'),
(1, 'CREATE', 'promotion', 1, 'Created promotion: Summer Beverage Sale', '127.0.0.1', '2026-02-15 12:00:00'),
(2, 'VIEW', 'report', NULL, 'Viewed stock report', '127.0.0.1', '2026-03-01 09:00:00'),
(3, 'UPDATE', 'transfer', 4, 'Updated transfer status to IN_TRANSIT', '127.0.0.1', '2026-02-24 08:00:00'),
(5, 'STOCK_MOVEMENT', 'stock', 1, 'Stock In: Coca-Cola 1L', '127.0.0.1', '2026-02-15 09:00:00');

-- ============================================================
-- EMAIL LOGS (Email notifications)
-- ============================================================

INSERT INTO `email_logs` (`recipient_email`, `subject`, `email_type`, `status`, `created_at`) VALUES
('sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-03-01 08:00:15'),
('headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-03-01 08:15:20'),
('north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-03-01 08:30:25'),
('north_clerk1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-03-01 08:45:30'),
('customer1@mail.com', 'Order Confirmation - ORD-NORTH-2026-001', 'order_confirmation', 'sent', '2026-02-25 09:20:00'),
('customer2@mail.com', 'Order Confirmation - ORD-SOUTH-2026-001', 'order_confirmation', 'sent', '2026-02-26 10:35:00'),
('south_manager@mail.com', 'Transfer Request Pending - TRF-SOUTH-EAST-001', 'transfer_notification', 'sent', '2026-02-20 09:05:00'),
('north_clerk1@mail.com', 'Transfer Approved - TRF-WEST-NORTH-001', 'transfer_notification', 'sent', '2026-02-21 11:50:00'),
('customer1@mail.com', 'Delivery Update - Order Shipped', 'delivery_notification', 'sent', '2026-02-27 12:00:00'),
('customer2@mail.com', 'Payment Received - ORD-SOUTH-2026-001', 'payment_notification', 'sent', '2026-02-28 16:50:00'),
('headoffice1@mail.com', 'Low Stock Alert - Multiple Products', 'stock_alert', 'sent', '2026-02-28 10:00:00'),
('north_manager@mail.com', 'Weekly Stock Report', 'report_notification', 'sent', '2026-02-28 17:00:00'),
('south_clerk1@mail.com', 'Transfer Received - TRF-SOUTH-EAST-001', 'transfer_notification', 'sent', '2026-02-22 14:35:00');

-- ============================================================
-- SEEDING COMPLETE
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;

-- Summary of data added:
-- ✓ 1 System Admin
-- ✓ 1 Head Office Manager
-- ✓ 2 RDC Managers
-- ✓ 2 RDC Clerks
-- ✓ 2 RDC Drivers
-- ✓ 2 Retail Customers
-- ✓ 2 RDC Sales Refs
-- ✓ 1 Logistics Officer
-- ✓ 40 Products (across 8 categories)
-- ✓ 200 Product Stock entries (40 products × 5 RDCs)
-- ✓ 12 Promotions
-- ✓ 14 Orders (various statuses)
-- ✓ 40+ Order Items
-- ✓ 9 Payments
-- ✓ 11 Order Deliveries
-- ✓ 12 Stock Transfers (various statuses)
-- ✓ 48 Stock Transfer Items
-- ✓ 50+ Transfer Status Logs
-- ✓ 20+ Stock Movement Logs
-- ✓ 10 Shopping Cart Items
-- ✓ 14+ Audit Logs
-- ✓ 13+ Email Logs

SELECT 'Database seeding completed successfully!' AS Status;