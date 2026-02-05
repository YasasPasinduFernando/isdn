# ISDN - IslandLink Sales Distribution Network

## 🚀 XAMPP Installation Guide

### Step 1: Copy Project to XAMPP
1. Copy the isdn folder to: C:\xampp\htdocs\
2. Your project path should be: C:\xampp\htdocs\isdn\

### Step 2: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**

### Step 3: Create Database
1. Open browser: http://localhost/phpmyadmin
2. Click "Import" tab
3. Import files from database/migrations/ folder in this order:
   - create_users_table.sql
   - create_products_table.sql
   - create_orders_table.sql

 ### Optional step for step 3
Create the SQL file with 001,002, 003,... prefix order that you want to run the migration
 Then run
 ```
 php database/migrations/migrate.php
 ```

### Step 4: Access the Application
Open browser: **http://localhost/isdn**

## 📁 Default Login (After DB setup)
- Email: admin@isdn.com
- Password: admin123

## 🛠️ Technology Stack
- PHP 8.0+
- MySQL 8.0
- Tailwind CSS 3.0
- JavaScript (Vanilla)

## 📂 Project Structure
- /config - Database & app configuration
- /controllers - Business logic
- /models - Database operations
- /views - UI pages
- /assets - CSS, JS, images
- /database - SQL migrations

## 🔧 Troubleshooting
1. **404 Error**: Enable mod_rewrite in Apache
2. **Database Error**: Check credentials in config/database.php
3. **Blank Page**: Check PHP error logs in XAMPP

## 📞 Support
Contact your instructor for help!
