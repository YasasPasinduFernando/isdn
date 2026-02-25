-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: isdn
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'LOGIN','session',NULL,'System admin logged in','127.0.0.1','2026-02-09 19:23:24'),(2,1,'CREATE','user',9,'Created user customer1','127.0.0.1','2026-02-09 19:23:24'),(3,2,'LOGIN','session',NULL,'Head office manager logged in','127.0.0.1','2026-02-10 19:23:24'),(4,1,'UPDATE','product',1,'Updated Cement 50kg price','127.0.0.1','2026-02-10 19:23:24'),(5,1,'TOGGLE','user',12,'Toggled user active status','127.0.0.1','2026-02-11 07:23:24'),(6,1,'LOGIN','session',NULL,'System admin logged in','127.0.0.1','2026-02-11 19:23:24'),(7,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 08:08:30'),(8,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-12 08:13:39'),(9,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 09:19:15'),(10,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 09:19:21'),(11,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 11:12:30'),(12,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-12 11:12:37'),(13,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 11:36:02'),(14,14,'LOGIN','session',NULL,'Logged in via Google as customer','::1','2026-02-12 12:16:34'),(15,14,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 12:24:41'),(16,14,'LOGIN','session',NULL,'Logged in via Google as customer','::1','2026-02-12 12:25:21'),(17,14,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 12:25:30'),(18,14,'LOGIN','session',NULL,'Logged in via Google as customer','::1','2026-02-12 12:50:16'),(19,14,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 12:51:44'),(20,14,'LOGIN','session',NULL,'Logged in via Google as customer','::1','2026-02-12 17:00:35'),(21,14,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 17:02:59'),(22,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 17:03:06'),(23,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 17:05:33'),(24,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-12 17:05:39'),(25,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 17:12:08'),(26,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 17:12:15'),(27,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 17:37:16'),(28,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-12 17:37:22'),(29,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 17:49:55'),(30,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 17:50:00'),(31,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 17:50:06'),(32,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 17:50:09'),(33,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 17:50:12'),(34,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-12 18:41:29'),(35,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-12 18:46:03'),(36,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 07:42:45'),(37,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-14 07:57:38'),(38,14,'LOGIN','session',NULL,'Logged in via Google as customer','::1','2026-02-14 08:03:25'),(39,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 08:14:19'),(40,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-14 08:14:46'),(41,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-14 08:15:00'),(42,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-14 08:37:32'),(43,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-14 08:37:39'),(44,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-14 08:37:52'),(45,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-14 09:25:06'),(46,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:25:15'),(47,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:32:07'),(48,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-14 09:35:03'),(49,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-14 09:35:08'),(50,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-14 09:35:23'),(51,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:39:17'),(52,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:22'),(53,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:27'),(54,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:30'),(55,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:34'),(56,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:36'),(57,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:42'),(58,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:44'),(59,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-14 09:40:46'),(60,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-14 13:49:40'),(61,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-15 05:56:59'),(62,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-15 05:57:27'),(63,13,'LOGOUT','session',NULL,'Logged out','::1','2026-02-15 07:01:32'),(64,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-15 16:09:42'),(65,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-15 16:11:37'),(66,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-17 16:05:28'),(67,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-17 16:05:39'),(68,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-17 16:56:26'),(69,1,'LOGOUT','session',NULL,'Logged out','::1','2026-02-17 16:56:39'),(70,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-17 17:18:07'),(71,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-18 04:08:20'),(72,1,'LOGIN','session',NULL,'Logged in as system_admin','::1','2026-02-18 04:08:23'),(73,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:31:02'),(74,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:31:05'),(75,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:31:08'),(76,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:31:11'),(77,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:31:14'),(78,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:31:17'),(79,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:33:19'),(80,13,'LOGIN','session',NULL,'Logged in as head_office_manager','::1','2026-02-19 02:38:13');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
INSERT INTO `email_logs` VALUES (1,'nayanakumarimama@gmail.com','Welcome to ISDN - Google Account Linked!','welcome_google','failed','2026-02-12 04:02:04'),(2,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 04:02:06'),(3,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 04:08:01'),(4,'nayanakumarimama@gmail.com','ISDN - Password Reset Request','password_reset','sent','2026-02-12 04:09:55'),(5,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 04:18:10'),(6,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 04:20:29'),(7,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 04:22:43'),(8,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 04:42:09'),(9,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 05:10:52'),(10,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 05:12:36'),(11,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 07:25:36'),(12,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 07:27:21'),(13,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 07:28:35'),(14,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 07:46:25'),(15,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 08:13:44'),(16,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 09:19:24'),(17,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 11:12:41'),(18,'nayanakumarimama@gmail.com','ISDN - Password Reset Request','password_reset','failed','2026-02-12 11:47:40'),(19,'nayanakumarimama@gmail.com','ISDN - Password Reset Request','password_reset','failed','2026-02-12 11:47:46'),(20,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 12:16:38'),(21,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 12:25:25'),(22,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 12:50:21'),(23,'yasaspasindufernando@gmail.com','Welcome to ISDN - Your Account is Ready!','welcome','sent','2026-02-12 12:52:52'),(24,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 17:00:43'),(25,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 17:03:14'),(26,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 17:05:45'),(27,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 17:12:21'),(28,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 17:37:29'),(29,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 17:50:06'),(30,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 17:50:09'),(31,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 17:50:12'),(32,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-12 17:50:15'),(33,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-12 18:46:08'),(34,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 07:42:45'),(35,'nayanakumarimama@gmail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 08:03:35'),(36,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 08:14:25'),(37,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 08:16:42'),(38,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 08:37:52'),(39,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 08:37:57'),(40,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 09:25:19'),(41,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 09:32:11'),(42,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 09:35:12'),(43,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 09:39:22'),(44,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 09:40:27'),(45,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:30'),(46,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:34'),(47,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:36'),(48,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:42'),(49,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:44'),(50,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:46'),(51,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-14 09:40:50'),(52,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-14 13:49:45'),(53,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-15 05:57:32'),(54,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','sent','2026-02-15 16:09:51'),(55,'nayanakumarimama@gmail.com','ISDN - Password Reset Request','password_reset','sent','2026-02-15 16:15:37'),(56,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-17 16:05:31'),(57,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-17 16:56:29'),(58,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-17 17:18:10'),(59,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-18 04:08:23'),(60,'sysadmin1@mail.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-18 04:08:26'),(61,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:31:05'),(62,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:31:08'),(63,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:31:11'),(64,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:31:14'),(65,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:31:16'),(66,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:31:19'),(67,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:33:22'),(68,'hom@isdn.com','ISDN - New Login to Your Account','login_notification','failed','2026-02-19 02:38:16');
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `head_office_managers`
--

DROP TABLE IF EXISTS `head_office_managers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `head_office_managers`
--

LOCK TABLES `head_office_managers` WRITE;
/*!40000 ALTER TABLE `head_office_managers` DISABLE KEYS */;
INSERT INTO `head_office_managers` VALUES (1,'Head Office Manager',NULL,NULL,'headoffice1@mail.com',2);
/*!40000 ALTER TABLE `head_office_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'001_create_users_table.sql','2026-02-06 15:00:15'),(2,'002_create_rdcs_table.sql','2026-02-06 15:00:15'),(3,'003_create_products_table.sql','2026-02-06 15:00:15'),(4,'004_create_orders_table.sql','2026-02-06 15:00:15'),(5,'005_create_stock_transfer_table.sql','2026-02-06 15:00:15'),(6,'006_create_stock_transfer_items.sql','2026-02-06 15:00:15'),(7,'007_transfer_status_logs.sql','2026-02-06 15:00:15'),(8,'008_create_order_delivery_table.sql','2026-02-06 15:00:15'),(9,'009_create_order_item_table.sql','2026-02-06 15:00:15'),(10,'010_create_payment_table.sql','2026-02-06 15:00:15'),(11,'011_create_product_stock_table.sql','2026-02-06 15:00:15'),(12,'012_create_promotion_table.sql','2026-02-06 15:00:15'),(13,'013_create_rdc_district_table.sql','2026-02-06 15:00:15'),(14,'014_create_retail_customer_table.sql','2026-02-06 15:00:15'),(15,'015_create_rdc_sales_ref_table.sql','2026-02-06 15:00:15'),(16,'016_create_rdc_manager_table copy.sql','2026-02-06 15:00:15'),(17,'017_create_logistics_officers_table.sql','2026-02-06 15:00:15'),(18,'018_create_rdc_clerks_table.sql','2026-02-06 15:00:15'),(19,'019_create_rdc_drivers_table.sql','2026-02-06 15:00:16'),(20,'020_create_head_office_managers_table.sql','2026-02-06 15:00:16'),(21,'021_create_system_admins_table.sql','2026-02-06 15:00:16');
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_deliveries`
--

DROP TABLE IF EXISTS `order_deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_deliveries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `driver_id` int DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_deliveries`
--

LOCK TABLES `order_deliveries` WRITE;
/*!40000 ALTER TABLE `order_deliveries` DISABLE KEYS */;
INSERT INTO `order_deliveries` VALUES (1,1,'2026-02-10 00:00:00',7,'2026-02-10 18:00:00'),(2,2,'2026-02-12 00:00:00',8,'2026-02-13 00:00:00'),(3,4,'2025-08-28 00:47:38',7,'2025-08-28 04:47:38'),(4,5,'2025-09-08 00:47:38',8,'2025-09-07 20:47:38'),(5,6,'2025-09-22 00:47:38',7,'2025-09-21 16:47:38'),(6,7,'2025-10-01 00:47:38',7,'2025-10-01 06:47:38'),(7,8,'2025-10-22 00:47:38',8,'2025-10-22 00:47:38'),(8,9,'2025-11-02 00:47:38',7,'2025-11-01 12:47:38'),(9,11,'2025-11-21 00:47:38',8,'2025-11-20 12:47:38'),(10,12,'2025-12-01 00:47:38',7,'2025-11-30 20:47:38'),(11,13,'2025-12-20 00:47:38',7,'2025-12-20 04:47:38'),(12,15,'2026-01-01 00:47:38',8,'2026-01-01 00:47:38'),(13,16,'2026-01-20 00:47:38',8,'2026-01-19 18:47:38');
/*!40000 ALTER TABLE `order_deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` bigint DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_item_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,10,1450.00,0.00),(2,2,2,10,2200.00,0.00),(3,3,3,1,7500.00,0.00),(4,4,1,25,1450.00,0.00),(5,5,2,20,2200.00,0.00),(6,6,3,2,7500.00,0.00),(7,7,1,10,1450.00,0.00),(8,7,6,8,1800.00,0.00),(9,8,4,10,3200.00,0.00),(10,8,7,5,4500.00,0.00),(11,8,5,5,1200.00,0.00),(12,9,6,5,1800.00,0.00),(13,9,1,6,1200.00,200.00),(14,10,1,10,1250.00,0.00),(15,11,8,2,25000.00,0.00),(16,11,7,5,4400.00,0.00),(17,12,3,3,7500.00,0.00),(18,12,5,10,1200.00,100.00),(19,13,4,8,3200.00,0.00),(20,13,6,12,1500.00,0.00),(21,14,2,5,2200.00,0.00),(22,14,1,8,1450.00,200.00),(23,15,8,3,25000.00,0.00),(24,15,5,8,1200.00,0.00),(25,15,7,2,4500.00,500.00),(26,16,2,15,2200.00,0.00),(27,16,3,2,7500.00,0.00),(28,16,6,10,1800.00,500.00),(29,17,4,3,3200.00,0.00),(30,17,7,2,4500.00,0.00),(31,18,1,15,1450.00,0.00),(32,18,2,8,2200.00,200.00),(33,18,5,5,1200.00,0.00),(34,19,8,2,25000.00,0.00),(35,19,4,1,2000.00,0.00),(36,20,1,10,1450.00,0.00),(37,20,6,5,1800.00,0.00),(38,20,3,1,7500.00,500.00),(39,21,2,20,2200.00,0.00),(40,21,8,1,25000.00,0.00),(41,21,7,4,4500.00,0.00),(42,21,4,2,3200.00,200.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','delivered','cancelled') DEFAULT 'pending',
  `delivery_date` date DEFAULT NULL,
  `rdc_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,9,'ORD-1001',14500.00,'confirmed','2026-02-10',1,'2026-02-06 19:17:38','2026-02-11 19:17:38'),(2,10,'ORD-1002',22000.00,'processing','2026-02-12',2,'2026-01-30 19:17:38','2026-02-11 19:17:38'),(3,11,'ORD-1003',7500.00,'pending','2026-02-15',3,'2026-01-22 19:17:38','2026-02-11 19:17:38'),(4,9,'ORD-D001',36250.00,'delivered',NULL,1,'2025-08-25 19:17:38','2026-02-11 19:17:38'),(5,10,'ORD-D002',44000.00,'delivered',NULL,2,'2025-09-04 19:17:38','2026-02-11 19:17:38'),(6,11,'ORD-D003',15000.00,'delivered',NULL,3,'2025-09-19 19:17:38','2026-02-11 19:17:38'),(7,9,'ORD-D004',29000.00,'delivered',NULL,1,'2025-09-29 19:17:38','2026-02-11 19:17:38'),(8,10,'ORD-D005',58500.00,'delivered',NULL,2,'2025-10-19 19:17:38','2026-02-11 19:17:38'),(9,11,'ORD-D006',18200.00,'delivered',NULL,4,'2025-10-29 19:17:38','2026-02-11 19:17:38'),(10,9,'ORD-D007',12500.00,'cancelled',NULL,1,'2025-11-03 19:17:38','2026-02-11 19:17:38'),(11,10,'ORD-D008',72000.00,'delivered',NULL,5,'2025-11-18 19:17:38','2026-02-11 19:17:38'),(12,11,'ORD-D009',33600.00,'delivered',NULL,3,'2025-11-28 19:17:38','2026-02-11 19:17:38'),(13,9,'ORD-D010',45000.00,'delivered',NULL,1,'2025-12-18 19:17:38','2026-02-11 19:17:38'),(14,10,'ORD-D011',21700.00,'processing',NULL,2,'2025-12-23 19:17:38','2026-02-11 19:17:38'),(15,11,'ORD-D012',87500.00,'delivered',NULL,4,'2025-12-28 19:17:38','2026-02-11 19:17:38'),(16,9,'ORD-D013',64000.00,'delivered',NULL,5,'2026-01-17 19:17:38','2026-02-11 19:17:38'),(17,10,'ORD-D014',19500.00,'confirmed',NULL,2,'2026-01-24 19:17:38','2026-02-11 19:17:38'),(18,11,'ORD-D015',41000.00,'processing',NULL,3,'2026-02-01 19:17:38','2026-02-11 19:17:38'),(19,9,'ORD-D016',52000.00,'pending',NULL,1,'2026-02-07 19:17:38','2026-02-11 19:17:38'),(20,10,'ORD-D017',28500.00,'confirmed',NULL,2,'2026-02-09 19:17:38','2026-02-11 19:17:38'),(21,11,'ORD-D018',95000.00,'pending',NULL,4,'2026-02-10 19:17:38','2026-02-11 19:17:38');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(45) DEFAULT NULL,
  `amount` varchar(45) DEFAULT NULL,
  `payment_date` varchar(45) DEFAULT NULL,
  `payment_method` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'ORD-1001','14500.00','2026-02-05','CASH'),(2,'ORD-1002','22000.00','2026-02-05','CARD');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Construction','Construction products','2026-02-18 04:01:10','2026-02-18 04:01:10'),(2,'Finishing','Finishing products','2026-02-18 04:01:10','2026-02-18 04:01:10'),(3,'Plumbing','Plumbing products','2026-02-18 04:01:10','2026-02-18 04:01:10'),(4,'Raw Material','Raw material products','2026-02-18 04:01:10','2026-02-18 04:01:10');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_stocks`
--

DROP TABLE IF EXISTS `product_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_stocks`
--

LOCK TABLES `product_stocks` WRITE;
/*!40000 ALTER TABLE `product_stocks` DISABLE KEYS */;
INSERT INTO `product_stocks` VALUES (1,1,1,30,'2026-02-06 15:00:17'),(2,2,1,50,'2026-02-06 15:00:17'),(3,3,1,200,'2026-02-06 15:00:17'),(4,1,2,400,'2026-02-06 15:00:17'),(5,4,2,10,'2026-02-06 15:00:17'),(6,5,2,40,'2026-02-06 15:00:17'),(7,6,3,100,'2026-02-06 15:00:17'),(8,7,4,300,'2026-02-06 15:00:17'),(9,8,5,5,'2026-02-06 15:00:17'),(10,1,3,15,'2026-02-12 00:47:38'),(11,2,4,180,'2026-02-12 00:47:38'),(12,3,5,8,'2026-02-12 00:47:38'),(13,4,1,70,'2026-02-12 00:47:38'),(14,7,2,25,'2026-02-12 00:47:38');
/*!40000 ALTER TABLE `product_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `minimum_stock_level` int DEFAULT '100',
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `product_code` (`product_code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'P001','Cement 50kg','Construction',1450.00,100,'assets/images/products/bcbt.jpg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(2,'P002','Steel Rod 12mm','Construction',2200.00,200,'assets/images/products/clogard.jpg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(3,'P003','Sand 1 Cube','Raw Material',7500.00,50,'assets/images/products/cocacola.jpeg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(4,'P004','Paint 5L White','Finishing',3200.00,75,'assets/images/products/cocacola2.jpeg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(5,'P005','PVC Pipe 2inch','Plumbing',1200.00,150,'assets/images/products/dettol-bw.jpg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(6,'P006','Bricks Pack 100','Construction',1800.00,300,'assets/images/products/harpic.jpg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(7,'P007','Tiles Box','Finishing',4500.00,80,'assets/images/products/nestomalt.jpg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40'),(8,'P008','Water Tank 1000L','Plumbing',25000.00,20,'assets/images/products/ns-fw.jpg',1,'2026-02-06 15:00:17','2026-02-18 04:14:40');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
INSERT INTO `promotions` VALUES (1,'Cement Bulk Offer',NULL,1,10,5.00,'2026-01-01','2026-03-31',1,'2026-02-12 03:23:35','2026-02-12 03:23:35'),(2,'Paint Festival',NULL,4,5,10.00,'2026-02-01','2026-04-01',1,'2026-02-12 03:23:35','2026-02-12 03:23:35');
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdc_clerks`
--

DROP TABLE IF EXISTS `rdc_clerks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdc_clerks`
--

LOCK TABLES `rdc_clerks` WRITE;
/*!40000 ALTER TABLE `rdc_clerks` DISABLE KEYS */;
INSERT INTO `rdc_clerks` VALUES (1,'North Clerk',NULL,NULL,'north_clerk1@mail.com',5),(2,'South Clerk',NULL,NULL,'south_clerk1@mail.com',6);
/*!40000 ALTER TABLE `rdc_clerks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdc_districts`
--

DROP TABLE IF EXISTS `rdc_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdc_districts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `rdc_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdc_id` (`rdc_id`),
  CONSTRAINT `rdc_district_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdc_districts`
--

LOCK TABLES `rdc_districts` WRITE;
/*!40000 ALTER TABLE `rdc_districts` DISABLE KEYS */;
/*!40000 ALTER TABLE `rdc_districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdc_drivers`
--

DROP TABLE IF EXISTS `rdc_drivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdc_drivers`
--

LOCK TABLES `rdc_drivers` WRITE;
/*!40000 ALTER TABLE `rdc_drivers` DISABLE KEYS */;
INSERT INTO `rdc_drivers` VALUES (1,'North Driver',NULL,NULL,'north_driver1@mail.com',7),(2,'South Driver',NULL,NULL,'south_driver1@mail.com',8);
/*!40000 ALTER TABLE `rdc_drivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdc_logistics_officers`
--

DROP TABLE IF EXISTS `rdc_logistics_officers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdc_logistics_officers`
--

LOCK TABLES `rdc_logistics_officers` WRITE;
/*!40000 ALTER TABLE `rdc_logistics_officers` DISABLE KEYS */;
/*!40000 ALTER TABLE `rdc_logistics_officers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdc_managers`
--

DROP TABLE IF EXISTS `rdc_managers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdc_managers`
--

LOCK TABLES `rdc_managers` WRITE;
/*!40000 ALTER TABLE `rdc_managers` DISABLE KEYS */;
INSERT INTO `rdc_managers` VALUES (1,'North Manager',NULL,NULL,'north_manager@mail.com',3),(2,'South Manager',NULL,NULL,'south_manager@mail.com',4);
/*!40000 ALTER TABLE `rdc_managers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdc_sales_refs`
--

DROP TABLE IF EXISTS `rdc_sales_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdc_sales_refs`
--

LOCK TABLES `rdc_sales_refs` WRITE;
/*!40000 ALTER TABLE `rdc_sales_refs` DISABLE KEYS */;
/*!40000 ALTER TABLE `rdc_sales_refs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rdcs`
--

DROP TABLE IF EXISTS `rdcs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rdcs` (
  `rdc_id` int NOT NULL AUTO_INCREMENT,
  `rdc_code` enum('NORTH','SOUTH','EAST','WEST','CENTRAL') DEFAULT 'NORTH',
  `rdc_name` varchar(100) NOT NULL,
  `province` varchar(50) DEFAULT NULL,
  `address` text,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rdc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rdcs`
--

LOCK TABLES `rdcs` WRITE;
/*!40000 ALTER TABLE `rdcs` DISABLE KEYS */;
INSERT INTO `rdcs` VALUES (1,'NORTH','Northern RDC','Northern Province','Jaffna Industrial Zone','0711111111','2026-02-06 15:00:17'),(2,'SOUTH','Southern RDC','Southern Province','Galle Trade Center','0712222222','2026-02-06 15:00:17'),(3,'EAST','Eastern RDC','Eastern Province','Batticaloa Hub','0713333333','2026-02-06 15:00:17'),(4,'WEST','Western RDC','Western Province','Colombo Warehouse Complex','0714444444','2026-02-06 15:00:17'),(5,'CENTRAL','Central RDC','Central Province','Kandy Distribution Park','0715555555','2026-02-06 15:00:17');
/*!40000 ALTER TABLE `rdcs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `retail_customers`
--

DROP TABLE IF EXISTS `retail_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `retail_customers`
--

LOCK TABLES `retail_customers` WRITE;
/*!40000 ALTER TABLE `retail_customers` DISABLE KEYS */;
INSERT INTO `retail_customers` VALUES (1,'Customer One',NULL,NULL,'customer1@mail.com',9),(2,'Customer Two',NULL,NULL,'customer2@mail.com',10),(3,'Customer Three',NULL,NULL,'customer3@mail.com',11);
/*!40000 ALTER TABLE `retail_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_items`
--

DROP TABLE IF EXISTS `stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_items`
--

LOCK TABLES `stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_items` DISABLE KEYS */;
INSERT INTO `stock_transfer_items` VALUES (1,1,1,100),(2,2,2,50);
/*!40000 ALTER TABLE `stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_transfers` (
  `transfer_id` int NOT NULL AUTO_INCREMENT,
  `transfer_number` varchar(50) NOT NULL,
  `source_rdc_id` int NOT NULL,
  `destination_rdc_id` int NOT NULL,
  `requested_by` int NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
INSERT INTO `stock_transfers` VALUES (1,'TR-1001',1,2,3,'2026-02-06 15:00:17','Low cement stock',1,'APPROVED',NULL,NULL,NULL,'2026-02-06 15:00:17',NULL,NULL,NULL),(2,'TR-1002',2,3,4,'2026-02-06 15:00:17','Steel requirement',0,'PENDING',NULL,NULL,NULL,'2026-02-06 15:00:17',NULL,NULL,NULL),(3,'TR-1003',3,1,5,'2026-02-10 19:17:38','Urgent cement restock for North RDC',1,'PENDING',NULL,NULL,NULL,'2026-02-11 19:17:38',NULL,NULL,NULL);
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_admins`
--

DROP TABLE IF EXISTS `system_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_admins`
--

LOCK TABLES `system_admins` WRITE;
/*!40000 ALTER TABLE `system_admins` DISABLE KEYS */;
INSERT INTO `system_admins` VALUES (1,'System Admin',NULL,NULL,'sysadmin1@mail.com',1);
/*!40000 ALTER TABLE `system_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transfer_status_logs`
--

DROP TABLE IF EXISTS `transfer_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transfer_status_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `transfer_id` int NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `changed_by` int NOT NULL,
  `changed_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `transfer_id` (`transfer_id`),
  CONSTRAINT `transfer_status_logs_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers` (`transfer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfer_status_logs`
--

LOCK TABLES `transfer_status_logs` WRITE;
/*!40000 ALTER TABLE `transfer_status_logs` DISABLE KEYS */;
INSERT INTO `transfer_status_logs` VALUES (1,1,'PENDING_APPROVAL','DISPATCHED',3,'2026-02-06 15:00:17');
/*!40000 ALTER TABLE `transfer_status_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'sysadmin1','sysadmin1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','system_admin',NULL,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(2,'headoffice1','headoffice1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','head_office_manager',NULL,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(3,'north_manager','north_manager@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_manager',1,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(4,'south_manager','south_manager@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_manager',2,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(5,'north_clerk1','north_clerk1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_clerk',1,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(6,'south_clerk1','south_clerk1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_clerk',2,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(7,'north_driver1','north_driver1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_driver',1,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(8,'south_driver1','south_driver1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','rdc_driver',2,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(9,'customer1','customer1@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','customer',1,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(10,'customer2','customer2@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','customer',2,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(11,'customer3','customer3@mail.com','$2y$10$EDm88GnNLzt4PAzal72PbeHYfTfZ6gCvESWfNy3evlVu6u0B1G8aO','customer',3,NULL,NULL,NULL,1,'2026-02-06 15:00:17','2026-02-06 15:00:17'),(12,'yasas','yasasnew@gmail.com','$2y$10$FuToLZqlZIOqCDgYmD5Sy.UUo..gJ6U.r0iV/UgqQFsRNxJLEZuUi','rdc_manager',NULL,NULL,NULL,NULL,1,'2026-02-09 17:42:25','2026-02-09 17:42:25'),(13,'HO Manager','hom@isdn.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','head_office_manager',NULL,NULL,NULL,NULL,1,'2026-02-11 18:34:39','2026-02-11 18:34:39'),(14,'nayana kumari','nayanakumarimama@gmail.com','$2y$10$CExhNh61VPbGYOW/Svz4ceFgSfWZQRWxZDQLcTjtMSjbndSAv23sS','customer',NULL,'105814652667979563504','0d65b40f1e043190b42984ea4f0441b1a2d1f06ee0a80cc2f0f0a71b26f7d7e5','2026-02-15 22:45:28',1,'2026-02-12 04:02:02','2026-02-15 16:15:28'),(15,'yasas pasindu','yasaspasindufernando@gmail.com','$2y$10$6Jk2e5aYa1.S8oI.wAITBejUyT8wL.RbK7HEFr8kpLOQkhjOJt0k2','customer',NULL,NULL,NULL,NULL,1,'2026-02-12 12:52:46','2026-02-12 12:52:46');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'isdn'
--

--
-- Dumping routines for database 'isdn'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-19  8:26:21
