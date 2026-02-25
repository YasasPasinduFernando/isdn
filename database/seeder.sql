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
TRUNCATE TABLE shopping_carts;
TRUNCATE TABLE stock_movement_logs;
TRUNCATE TABLE product_categories;
TRUNCATE TABLE rdc_districts;
TRUNCATE TABLE audit_logs;
TRUNCATE TABLE email_logs;


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

INSERT INTO rdc_sales_refs (name,email,user_id) VALUES
('North Sales Ref','north_sales_ref@mail.com',12);

INSERT INTO rdc_logistics_officers (name,email,user_id) VALUES
('North Logistics Officer','north_logistics_officer@mail.com',13);

INSERT INTO categories (name, description) VALUES
('Construction', 'Materials used for building and construction purposes.'),
('Finishing', 'Products used for finishing touches in construction.'),
('Plumbing', 'Pipes and fittings for plumbing needs.'),
('Raw Material', 'Basic raw materials for construction.');


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
-- =====================================================
-- PRODUCTS
-- =====================================================

INSERT INTO products (product_code, product_name, category_id, unit_price, minimum_stock_level) VALUES
('P001','Cement 50kg',1,1450.00,100),
('P002','Steel Rod 12mm',2,2200.00,200),
('P003','Sand 1 Cube',3,7500.00,50),
('P004','Paint 5L White',1,3200.00,75),
('P005','PVC Pipe 2inch',2,1200.00,150),
('P006','Bricks Pack 100',2,1800.00,300),
('P007','Tiles Box',1,4500.00,80),
('P008','Water Tank 1000L',5,25000.00,20);

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

INSERT INTO orders (customer_id,order_number,total_amount,status,estimated_date) VALUES
(1,'ORD-1001',14500.00,'confirmed','2026-02-10'),
(2,'ORD-1002',22000.00,'processing','2026-02-12'),
(3,'ORD-1003',7500.00,'pending','2026-02-15');

INSERT INTO order_items (order_id,product_id,quantity,selling_price,discount) VALUES
(1,1,10,1450.00,0),
(2,2,10,2200.00,0),
(3,3,1,7500.00,0);

INSERT INTO order_deliveries (order_id,delivery_date,driver_id) VALUES
(1,'2026-02-10',7),
(2,'2026-02-12',8);

INSERT INTO payments (order_id,amount,payment_method) VALUES
(1,14500.00,'CASH'),
(1,22000.00,'CARD');

-- =====================================================
-- STOCK TRANSFERS
-- =====================================================

INSERT INTO stock_transfers
(transfer_number,source_rdc_id,destination_rdc_id,requested_by,requested_by_role,request_reason,is_urgent,approval_status)
VALUES
('TR-1001',1,2,3,'RDC_MANAGER','Low cement stock',1,'APPROVED'),
('TR-1002',2,3,4,'RDC_MANAGER','Steel requirement',0,'PENDING');

INSERT INTO stock_transfer_items (transfer_id,product_id,requested_quantity) VALUES
(1,1,100),
(2,2,50);

INSERT INTO transfer_status_logs (transfer_id,previous_status,new_status,changed_by, change_by_role, change_by_name) VALUES
(1,'PENDING_APPROVAL','DISPATCHED',3,'RDC_MANAGER','North Manager');


INSERT INTO `stock_movement_logs` (
    rdc_id,
    product_id,
    movement_type,
    quantity,
    previous_quantity,
    new_quantity,
    created_by,
    created_by_role,
    created_by_name,
    note
) VALUES
(
    1,                  
    1,                      
    'STOCK_IN',             
    100,    
    400,                    
    500,                    
    1,                      
    'RDC_MANAGER',          
    'North Manager',        
    'New stock received at RDC'
),
(
    1,                      
    2,                      
    'STOCK_OUT',            
    100,
    900,                    
    800,                    
    1,                      
    'RDC_MANAGER',          
    'North Manager',        
    'Stock issued for customer orders'
);


SET foreign_key_checks = 1;

-- ============================================
-- 🔐 System User Credentials

-- 🛠 System Administrator

-- Email: sysadmin1@mail.com

-- Password: admin@123

-- 🏢 Head Office Manager

-- Email: headoffice1@mail.com

-- Password: head@123

-- 📍 RDC Managers

-- Email: north_manager@mail.com

-- Email: south_manager@mail.com

-- Password: manager@123

-- 🧾 RDC Clerks

-- Email: north_clerk1@mail.com

-- Email: south_clerk1@mail.com

-- Password: clerk@123

-- 🚚 RDC Drivers

-- Email: north_driver1@mail.com

-- Email: south_driver1@mail.com

-- Password: driver@123

-- 🛒 Customers

-- Email: customer1@mail.com

-- Email: customer2@mail.com

-- Email: customer3@mail.com

-- Password: customer@123

-- 📊 RDC Sales Representative

-- Email: north_sales_ref@mail.com

-- Password: ref@123

-- 🚛 RDC Logistics Officer

-- Email: north_logistics_officer@mail.com

-- Password: officer@123

-- ============================================