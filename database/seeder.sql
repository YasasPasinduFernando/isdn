SET foreign_key_checks = 0;
TRUNCATE TABLE transfer_status_logs;
TRUNCATE TABLE stock_transfer_items;
TRUNCATE TABLE stock_transfers;
TRUNCATE TABLE order_items;
TRUNCATE TABLE order_deliveries;
TRUNCATE TABLE payments;
TRUNCATE TABLE orders;
TRUNCATE TABLE product_stocks;
TRUNCATE TABLE promotions;
TRUNCATE TABLE retail_customers;
TRUNCATE TABLE rdc_sales_refs;
TRUNCATE TABLE rdc_logistics_officers;
TRUNCATE TABLE rdc_drivers;
TRUNCATE TABLE rdc_clerks;
TRUNCATE TABLE rdc_managers;
TRUNCATE TABLE head_office_managers;
TRUNCATE TABLE system_admins;
TRUNCATE TABLE products;
TRUNCATE TABLE rdcs;
TRUNCATE TABLE users;
SET foreign_key_checks = 1;

-- =====================================================
-- RDCS
-- =====================================================

INSERT INTO rdcs (rdc_code, rdc_name, province, address, contact_number) VALUES
('NORTH', 'Northern RDC', 'Northern Province', 'Jaffna Industrial Zone', '0711111111'),
('SOUTH', 'Southern RDC', 'Southern Province', 'Galle Trade Center', '0712222222'),
('EAST', 'Eastern RDC', 'Eastern Province', 'Batticaloa Hub', '0713333333'),
('WEST', 'Western RDC', 'Western Province', 'Colombo Warehouse Complex', '0714444444'),
('CENTRAL', 'Central RDC', 'Central Province', 'Kandy Distribution Park', '0715555555');

-- =====================================================
-- USERS
-- =====================================================

INSERT INTO users (username, email, password, role, rdc_id) VALUES
('sysadmin1','sysadmin1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','system_admin',NULL),
('headoffice1','headoffice1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','head_office_manager',NULL),

('north_manager','north_manager@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_manager',1),
('south_manager','south_manager@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_manager',2),

('north_clerk1','north_clerk1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_clerk',1),
('south_clerk1','south_clerk1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_clerk',2),

('north_driver1','north_driver1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_driver',1),
('south_driver1','south_driver1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_driver',2),

('customer1','customer1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','customer',1),
('customer2','customer2@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','customer',2),
('customer3','customer3@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','customer',3);

-- =====================================================
-- ROLE TABLES
-- =====================================================

INSERT INTO system_admins (name,email,user_id) VALUES
('System Admin','sysadmin1@mail.com',1);

INSERT INTO head_office_managers (name,email,user_id) VALUES
('Head Office Manager','headoffice1@mail.com',2);

INSERT INTO rdc_managers (name,email,user_id) VALUES
('North Manager','north_manager@mail.com',3),
('South Manager','south_manager@mail.com',4);

INSERT INTO rdc_clerks (name,email,user_id) VALUES
('North Clerk','north_clerk1@mail.com',5),
('South Clerk','south_clerk1@mail.com',6);

INSERT INTO rdc_drivers (name,email,user_id) VALUES
('North Driver','north_driver1@mail.com',7),
('South Driver','south_driver1@mail.com',8);

INSERT INTO retail_customers (name,email,user_id) VALUES
('Customer One','customer1@mail.com',9),
('Customer Two','customer2@mail.com',10),
('Customer Three','customer3@mail.com',11);

-- =====================================================
-- PRODUCTS
-- =====================================================

INSERT INTO products (product_code, product_name, category, unit_price, minimum_stock_level) VALUES
('P001','Cement 50kg','Construction',1450.00,100),
('P002','Steel Rod 12mm','Construction',2200.00,200),
('P003','Sand 1 Cube','Raw Material',7500.00,50),
('P004','Paint 5L White','Finishing',3200.00,75),
('P005','PVC Pipe 2inch','Plumbing',1200.00,150),
('P006','Bricks Pack 100','Construction',1800.00,300),
('P007','Tiles Box','Finishing',4500.00,80),
('P008','Water Tank 1000L','Plumbing',25000.00,20);

-- =====================================================
-- PRODUCT STOCKS
-- =====================================================

INSERT INTO product_stocks (product_id, rdc_id, available_quantity, last_updated) VALUES
(1,1,500,NOW()),(2,1,800,NOW()),(3,1,200,NOW()),
(1,2,400,NOW()),(4,2,150,NOW()),(5,2,600,NOW()),
(6,3,1000,NOW()),(7,4,300,NOW()),(8,5,50,NOW());

-- =====================================================
-- PROMOTIONS
-- =====================================================

INSERT INTO promotions (name,product_id,product_count,discount_percentage,start_date,end_date) VALUES
('Cement Bulk Offer',1,10,5.00,'2026-01-01','2026-03-31'),
('Paint Festival',4,5,10.00,'2026-02-01','2026-04-01');

-- =====================================================
-- ORDERS
-- =====================================================

INSERT INTO orders (customer_id,order_number,total_amount,status,delivery_date,rdc_id) VALUES
(9,'ORD-1001',14500.00,'confirmed','2026-02-10',1),
(10,'ORD-1002',22000.00,'processing','2026-02-12',2),
(11,'ORD-1003',7500.00,'pending','2026-02-15',3);

INSERT INTO order_items (order_id,product_id,quantity,selling_price,discount) VALUES
(1,1,10,1450.00,0),
(2,2,10,2200.00,0),
(3,3,1,7500.00,0);

INSERT INTO order_deliveries (order_id,delivery_date,driver_id) VALUES
(1,'2026-02-10',7),
(2,'2026-02-12',8);

INSERT INTO payments (order_id,amount,payment_date,payment_method) VALUES
('ORD-1001','14500.00','2026-02-05','CASH'),
('ORD-1002','22000.00','2026-02-05','CARD');

-- =====================================================
-- STOCK TRANSFERS
-- =====================================================

INSERT INTO stock_transfers
(transfer_number,source_rdc_id,destination_rdc_id,requested_by,request_reason,is_urgent,approval_status,transfer_status)
VALUES
('TR-1001',1,2,3,'Low cement stock',1,'APPROVED','DISPATCHED'),
('TR-1002',2,3,4,'Steel requirement',0,'PENDING','PENDING_APPROVAL');

INSERT INTO stock_transfer_items (transfer_id,product_id,requested_quantity,remarks) VALUES
(1,1,100,'Urgent transfer'),
(2,2,50,'Normal stock transfer');

INSERT INTO transfer_status_logs (transfer_id,previous_status,new_status,changed_by,remarks) VALUES
(1,'PENDING_APPROVAL','DISPATCHED',3,'Approved and dispatched');

SET foreign_key_checks = 1;