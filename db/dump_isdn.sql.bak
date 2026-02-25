-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql302.infinityfree.com
-- Generation Time: Feb 22, 2026 at 12:48 PM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_40908791_isdn`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 3, 'LOGOUT', 'session', NULL, 'Logged out', '127.0.0.1', '2026-02-15 06:44:15'),
(2, 12, 'LOGIN', 'session', NULL, 'Logged in as rdc_sales_ref', '127.0.0.1', '2026-02-15 06:44:23'),
(3, 12, 'LOGOUT', 'session', NULL, 'Logged out', '127.0.0.1', '2026-02-15 06:44:35'),
(4, 14, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.91.184', '2026-02-15 07:51:55'),
(5, 5, 'LOGIN', 'session', NULL, 'Logged in as rdc_clerk', '175.157.18.243', '2026-02-15 07:53:48'),
(6, 14, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.91.184', '2026-02-15 07:55:38'),
(7, 12, 'LOGIN', 'session', NULL, 'Logged in as rdc_sales_ref', '45.121.91.184', '2026-02-15 07:56:34'),
(8, 5, 'LOGIN', 'session', NULL, 'Logged in as rdc_clerk', '175.157.18.243', '2026-02-15 08:00:45'),
(9, 12, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.91.184', '2026-02-15 08:02:10'),
(10, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '175.157.44.50', '2026-02-15 08:03:14'),
(11, 2, 'LOGOUT', 'session', NULL, 'Logged out', '175.157.44.50', '2026-02-15 08:04:27'),
(12, 12, 'LOGIN', 'session', NULL, 'Logged in as rdc_sales_ref', '112.134.9.181', '2026-02-15 08:07:27'),
(13, 5, 'LOGIN', 'session', NULL, 'Logged in as rdc_clerk', '175.157.44.50', '2026-02-15 08:08:42'),
(14, 12, 'LOGOUT', 'session', NULL, 'Logged out', '112.134.9.181', '2026-02-15 08:10:00'),
(15, 15, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.91.184', '2026-02-15 08:28:00'),
(16, 14, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.91.184', '2026-02-15 08:41:26'),
(17, 14, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.91.184', '2026-02-15 08:42:15'),
(18, 5, 'LOGOUT', 'session', NULL, 'Logged out', '175.157.18.243', '2026-02-15 09:23:05'),
(19, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '175.157.18.243', '2026-02-15 09:24:31'),
(20, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '212.104.231.34', '2026-02-15 11:13:16'),
(21, 3, 'LOGOUT', 'session', NULL, 'Logged out', '212.104.231.225', '2026-02-15 12:00:38'),
(22, 13, 'LOGIN', 'session', NULL, 'Logged in as logistics_officer', '212.104.231.225', '2026-02-15 12:45:10'),
(23, 13, 'LOGOUT', 'session', NULL, 'Logged out', '212.104.231.225', '2026-02-15 13:06:40'),
(24, 7, 'LOGIN', 'session', NULL, 'Logged in as rdc_driver', '212.104.231.225', '2026-02-15 13:06:58'),
(25, 7, 'LOGOUT', 'session', NULL, 'Logged out', '212.104.231.225', '2026-02-15 13:27:53'),
(26, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '212.104.231.225', '2026-02-15 13:28:21'),
(27, 3, 'LOGOUT', 'session', NULL, 'Logged out', '212.104.231.225', '2026-02-15 15:07:09'),
(28, 12, 'LOGIN', 'session', NULL, 'Logged in as rdc_sales_ref', '212.104.231.199', '2026-02-16 20:24:37'),
(29, 12, 'LOGOUT', 'session', NULL, 'Logged out', '212.104.231.199', '2026-02-17 05:06:08'),
(30, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '212.104.231.199', '2026-02-17 05:06:32'),
(31, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '212.104.231.199', '2026-02-17 05:22:25'),
(32, 5, 'LOGIN', 'session', NULL, 'Logged in as rdc_clerk', '14.1.78.80', '2026-02-17 09:56:26'),
(33, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '212.104.231.199', '2026-02-17 10:05:45'),
(34, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '212.104.231.199', '2026-02-17 10:05:47'),
(35, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '14.1.78.80', '2026-02-17 12:02:39'),
(36, 3, 'LOGOUT', 'session', NULL, 'Logged out', '14.1.78.80', '2026-02-17 12:08:12'),
(37, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '14.1.78.80', '2026-02-17 12:08:39'),
(38, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '112.134.10.164', '2026-02-17 12:31:28'),
(39, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '112.134.10.164', '2026-02-17 13:15:33'),
(40, 9, 'LOGIN', 'session', NULL, 'Logged in as customer', '212.104.231.199', '2026-02-18 04:17:48'),
(41, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 02:00:52'),
(42, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 02:10:05'),
(43, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 02:11:07'),
(44, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 02:18:48'),
(45, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 02:25:23'),
(46, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 02:26:50'),
(47, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 02:37:06'),
(48, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 02:37:34'),
(49, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 02:39:00'),
(50, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 02:43:49'),
(51, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 02:44:25'),
(52, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 03:06:09'),
(53, 2, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.91.37', '2026-02-19 03:10:29'),
(54, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 03:11:54'),
(55, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.91.37', '2026-02-19 03:12:52'),
(56, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.91.37', '2026-02-19 03:23:44'),
(57, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.91.37', '2026-02-19 03:23:55'),
(58, 12, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.88.227', '2026-02-19 03:44:06'),
(59, 12, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 03:44:46'),
(60, 12, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.88.227', '2026-02-19 03:49:10'),
(61, 12, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 03:49:16'),
(62, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 03:54:11'),
(63, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 03:54:53'),
(64, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas Ne', '45.121.88.227', '2026-02-19 03:55:11'),
(65, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas Ne', '45.121.88.227', '2026-02-19 04:19:37'),
(66, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:19:44'),
(67, 1, 'TOGGLE', 'user', 1, 'Toggled active status for user #1', '45.121.88.227', '2026-02-19 04:19:55'),
(68, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:20:02'),
(69, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 04:28:46'),
(70, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:29:08'),
(71, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:29:16'),
(72, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas New', '45.121.88.227', '2026-02-19 04:35:25'),
(73, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 04:35:36'),
(74, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 04:36:09'),
(75, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas Ne', '45.121.88.227', '2026-02-19 04:36:55'),
(76, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 04:39:16'),
(77, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 04:39:23'),
(78, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 04:42:32'),
(79, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 04:43:08'),
(80, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 04:43:11'),
(81, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 04:43:12'),
(82, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas New', '45.121.88.227', '2026-02-19 04:50:25'),
(83, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:50:35'),
(84, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:50:44'),
(85, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas New', '45.121.88.227', '2026-02-19 04:51:08'),
(86, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:53:21'),
(87, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:53:29'),
(88, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 04:54:35'),
(89, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:54:50'),
(90, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:55:03'),
(91, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 04:59:24'),
(92, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 05:03:57'),
(93, 2, 'LOGIN', 'session', NULL, 'Logged in as head_office_manager', '45.121.88.227', '2026-02-19 05:04:11'),
(94, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 06:07:36'),
(95, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 06:07:54'),
(96, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 06:08:01'),
(97, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 06:25:16'),
(98, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 06:31:54'),
(99, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 06:32:11'),
(100, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 06:32:22'),
(101, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 06:32:42'),
(102, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 06:33:39'),
(103, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas New', '45.121.88.227', '2026-02-19 06:34:05'),
(104, 1, 'UPDATE', 'user', 12, 'Updated user #12: Yasas New', '45.121.88.227', '2026-02-19 06:34:19'),
(105, 13, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.88.227', '2026-02-19 06:35:20'),
(106, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 06:37:55'),
(107, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 06:48:58'),
(108, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 06:56:18'),
(109, 1, 'UPDATE', 'product', 1, 'Updated product: Coca-Cola 1L', '45.121.88.227', '2026-02-19 07:03:00'),
(110, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 07:04:54'),
(111, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:05:09'),
(112, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:05:16'),
(113, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 07:05:40'),
(114, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 07:05:45'),
(115, 1, 'UPDATE', 'product', 1, 'Updated product: Coca-Cola 1L', '45.121.88.227', '2026-02-19 07:06:39'),
(116, 1, 'UPDATE', 'product', 2, 'Updated product: Nestomalt 400G', '45.121.88.227', '2026-02-19 07:07:08'),
(117, 1, 'UPDATE', 'product', 3, 'Updated product: Harpic Fresh 500ml', '45.121.88.227', '2026-02-19 07:07:41'),
(118, 1, 'UPDATE', 'product', 4, 'Updated product: Sunlight Detergent Powder 1kg', '45.121.88.227', '2026-02-19 07:08:01'),
(119, 1, 'UPDATE', 'product', 5, 'Updated product: Strepsils 24S', '45.121.88.227', '2026-02-19 07:08:24'),
(120, 1, 'UPDATE', 'product', 6, 'Updated product: Clogard Toothpaste 200g', '45.121.88.227', '2026-02-19 07:08:42'),
(121, 1, 'UPDATE', 'product', 7, 'Updated product: Nature\'s Secrets Face Wash 100ml', '45.121.88.227', '2026-02-19 07:09:01'),
(122, 1, 'UPDATE', 'product', 8, 'Updated product: Baby Cheramy Baby Talc 100g', '45.121.88.227', '2026-02-19 07:09:16'),
(123, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 07:20:14'),
(124, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 07:21:35'),
(125, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:21:51'),
(126, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:21:58'),
(127, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 07:22:11'),
(128, 1, 'TOGGLE', 'product', 1, 'Toggled product active status', '45.121.88.227', '2026-02-19 07:22:18'),
(129, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 07:23:12'),
(130, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 07:24:04'),
(131, 1, 'TOGGLE', 'user', 12, 'Toggled active status for user #12', '45.121.88.227', '2026-02-19 07:24:21'),
(132, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 07:24:27'),
(133, 13, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.88.227', '2026-02-19 07:28:53'),
(134, 13, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 07:29:05'),
(135, 13, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.88.227', '2026-02-19 07:31:32'),
(136, 13, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 07:33:14'),
(137, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 07:39:22'),
(138, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 07:39:31'),
(139, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 07:50:29'),
(140, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:50:48'),
(141, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:51:07'),
(142, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 07:51:15'),
(143, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 07:54:00'),
(144, 1, 'UPDATE', 'user', 13, 'Updated user #13: Yasas Pasindu Fernando', '45.121.88.227', '2026-02-19 07:57:40'),
(145, 1, 'TOGGLE', 'user', 13, 'Toggled active status for user #13', '45.121.88.227', '2026-02-19 07:57:47'),
(146, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 08:00:22'),
(147, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 08:00:29'),
(148, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 08:24:10'),
(149, 1, 'UPDATE', 'profile', 1, 'Changed password', '45.121.88.227', '2026-02-19 08:24:46'),
(150, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 08:24:53'),
(151, 1, 'LOGIN', 'session', NULL, 'Logged in as system_admin', '45.121.88.227', '2026-02-19 08:25:02'),
(152, 1, 'UPDATE', 'profile', 1, 'Updated own profile', '45.121.88.227', '2026-02-19 08:25:21'),
(153, 1, 'UPDATE', 'profile', 1, 'Updated own profile', '45.121.88.227', '2026-02-19 08:35:28'),
(154, 1, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 08:40:17'),
(155, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '14.1.78.212', '2026-02-19 08:54:36'),
(156, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '14.1.78.212', '2026-02-19 09:28:38'),
(157, 3, 'LOGIN', 'session', NULL, 'Logged in as rdc_manager', '14.1.78.212', '2026-02-19 09:29:54'),
(158, 13, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '45.121.88.227', '2026-02-19 10:01:00'),
(159, 13, 'LOGOUT', 'session', NULL, 'Logged out', '45.121.88.227', '2026-02-19 10:08:00'),
(160, 12, 'LOGIN', 'session', NULL, 'Logged in via Google as customer', '124.43.21.6', '2026-02-20 06:35:49'),
(161, 12, 'LOGOUT', 'session', NULL, 'Logged out', '124.43.21.59', '2026-02-20 06:36:04');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(5, 'Construction', 'Materials used for building and construction purposes.', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(6, 'Finishing', 'Products used for finishing touches in construction.', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(7, 'Plumbing', 'Pipes and fittings for plumbing needs.', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(8, 'Raw Material', 'Basic raw materials for construction.', '2026-02-19 03:34:35', '2026-02-19 03:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `email_type` varchar(50) NOT NULL,
  `status` enum('sent','failed','logged') DEFAULT 'logged',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient_email`, `subject`, `email_type`, `status`, `created_at`) VALUES
(1, 'north_sales_ref@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'failed', '2026-02-15 06:44:25'),
(2, 'yasasnew@gmail.com', 'Welcome to ISDN - Google Account Linked!', 'welcome_google', 'sent', '2026-02-15 07:51:55'),
(3, 'yasasnew@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 07:51:56'),
(4, 'north_clerk1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 07:53:49'),
(5, 'north_sales_ref@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 07:56:36'),
(6, 'north_clerk1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 08:00:47'),
(7, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 08:03:17'),
(8, 'north_sales_ref@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 08:07:28'),
(9, 'north_clerk1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 08:08:43'),
(10, 'yasaspasindufernando@gmail.com', 'Welcome to ISDN - Google Account Linked!', 'welcome_google', 'sent', '2026-02-15 08:28:00'),
(11, 'yasaspasindufernando@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 08:28:01'),
(12, 'yasasnew@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 08:41:27'),
(13, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 09:24:33'),
(14, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 11:13:20'),
(15, 'north_logistics_officer@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 12:45:11'),
(16, 'north_driver1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 13:06:59'),
(17, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-15 13:28:24'),
(18, 'north_sales_ref@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-16 20:24:38'),
(19, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 05:06:33'),
(20, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 05:22:26'),
(21, 'north_clerk1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 09:56:27'),
(22, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 10:05:46'),
(23, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 10:05:48'),
(24, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 12:02:40'),
(25, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 12:08:40'),
(26, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 12:31:30'),
(27, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-17 13:15:34'),
(28, 'customer1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-18 04:18:00'),
(29, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:00:54'),
(30, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:10:09'),
(31, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:11:09'),
(32, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:18:50'),
(33, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:25:26'),
(34, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:26:51'),
(35, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:37:07'),
(36, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:37:35'),
(37, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:39:01'),
(38, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:43:50'),
(39, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 02:44:26'),
(40, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:06:10'),
(41, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:11:56'),
(42, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:12:53'),
(43, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:23:57'),
(44, 'yasasnew@gmail.com', 'Welcome to ISDN - Google Account Linked!', 'welcome_google', 'sent', '2026-02-19 03:44:06'),
(45, 'yasasnew@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:44:07'),
(46, 'yasasnew@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:49:11'),
(47, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 03:54:14'),
(48, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 04:28:49'),
(49, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 04:36:10'),
(50, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 04:43:10'),
(51, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 04:43:12'),
(52, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 04:43:13'),
(53, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 04:54:37'),
(54, 'headoffice1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 05:04:13'),
(55, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 06:07:38'),
(56, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 06:31:56'),
(57, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 06:33:41'),
(58, 'yasaspasindufernando@gmail.com', 'Welcome to ISDN - Google Account Linked!', 'welcome_google', 'sent', '2026-02-19 06:35:20'),
(59, 'yasaspasindufernando@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 06:35:21'),
(60, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:04:55'),
(61, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:21:36'),
(62, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:24:05'),
(63, 'yasaspasindufernando@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:28:54'),
(64, 'yasaspasindufernando@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:31:33'),
(65, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:39:23'),
(66, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:50:30'),
(67, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 07:54:01'),
(68, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 08:00:30'),
(69, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 08:24:11'),
(70, 'sysadmin1@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 08:25:03'),
(71, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 08:54:38'),
(72, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 09:28:40'),
(73, 'north_manager@mail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 09:29:55'),
(74, 'yasaspasindufernando@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-19 10:01:03'),
(75, 'yasaspasindufernando@gmail.com', 'ISDN - Password Reset Request', 'password_reset', 'sent', '2026-02-19 10:21:46'),
(76, 'yasasnew@gmail.com', 'ISDN - New Login to Your Account', 'login_notification', 'sent', '2026-02-20 06:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `head_office_managers`
--

CREATE TABLE `head_office_managers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `head_office_managers`
--

INSERT INTO `head_office_managers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Head Office Manager', NULL, NULL, 'headoffice1@mail.com', 2);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `applied_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `filename`, `applied_at`) VALUES
(1, '001_create_users_table.sql', '2026-02-15 06:43:59'),
(2, '002-1_create_categories_table.sql', '2026-02-15 06:43:59'),
(3, '002_create_rdcs_table.sql', '2026-02-15 06:43:59'),
(4, '003_create_product_categories_table.sql', '2026-02-15 06:43:59'),
(5, '004_create_products_table.sql', '2026-02-15 06:43:59'),
(6, '005_create_retail_customer_table.sql', '2026-02-15 06:43:59'),
(7, '006_create_orders_table.sql', '2026-02-15 06:43:59'),
(8, '007_create_stock_transfer_table.sql', '2026-02-15 06:43:59'),
(9, '008_create_stock_transfer_items.sql', '2026-02-15 06:43:59'),
(10, '009_transfer_status_logs.sql', '2026-02-15 06:43:59'),
(11, '010_create_order_delivery_table.sql', '2026-02-15 06:44:00'),
(12, '011_create_order_item_table.sql', '2026-02-15 06:44:00'),
(13, '012_create_payment_table.sql', '2026-02-15 06:44:00'),
(14, '013_create_product_stock_table.sql', '2026-02-15 06:44:00'),
(15, '014_create_promotion_table.sql', '2026-02-15 06:44:00'),
(16, '015_create_rdc_district_table.sql', '2026-02-15 06:44:00'),
(17, '016_create_rdc_sales_ref_table.sql', '2026-02-15 06:44:00'),
(18, '017_create_rdc_manager_table copy.sql', '2026-02-15 06:44:00'),
(19, '018_create_logistics_officers_table.sql', '2026-02-15 06:44:00'),
(20, '019_create_rdc_clerks_table.sql', '2026-02-15 06:44:00'),
(21, '020_create_rdc_drivers_table.sql', '2026-02-15 06:44:00'),
(22, '021_create_head_office_managers_table.sql', '2026-02-15 06:44:00'),
(23, '022_create_system_admins_table.sql', '2026-02-15 06:44:00'),
(24, '023_create_stock_movement_logs_table.sql', '2026-02-15 06:44:00'),
(25, '024_create_shopping_carts_table.sql', '2026-02-15 06:44:00'),
(26, '025_audit_logs_and_user_active.sql', '2026-02-15 06:44:00'),
(27, '026_auth_enhancements.sql', '2026-02-15 06:44:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `customer_id` int(11) NOT NULL,
  `placed_by` int(11) DEFAULT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','delivered','cancelled') DEFAULT 'pending',
  `estimated_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rdc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_date`, `customer_id`, `placed_by`, `order_number`, `total_amount`, `status`, `estimated_date`, `created_at`, `updated_at`, `rdc_id`) VALUES
(1, '2026-02-18 19:34:35', 1, NULL, 'ORD-1001', '14500.00', 'confirmed', '2026-02-10', '2026-02-14 03:37:07', '2026-02-19 03:37:07', 1),
(2, '2026-02-18 19:34:35', 2, NULL, 'ORD-1002', '22000.00', 'processing', '2026-02-12', '2026-02-07 03:37:07', '2026-02-19 03:37:07', 2),
(3, '2026-02-18 19:34:35', 3, NULL, 'ORD-1003', '7500.00', 'pending', '2026-02-15', '2026-01-30 03:37:07', '2026-02-19 03:37:07', 3),
(4, '2026-02-18 19:37:07', 9, NULL, 'ORD-D001', '36250.00', 'delivered', NULL, '2025-09-02 02:37:07', '2026-02-19 03:37:07', 1),
(5, '2026-02-18 19:37:07', 10, NULL, 'ORD-D002', '44000.00', 'delivered', NULL, '2025-09-12 02:37:07', '2026-02-19 03:37:07', 2),
(6, '2026-02-18 19:37:07', 11, NULL, 'ORD-D003', '15000.00', 'delivered', NULL, '2025-09-27 02:37:07', '2026-02-19 03:37:07', 3),
(7, '2026-02-18 19:37:07', 9, NULL, 'ORD-D004', '29000.00', 'delivered', NULL, '2025-10-07 02:37:07', '2026-02-19 03:37:07', 1),
(8, '2026-02-18 19:37:07', 10, NULL, 'ORD-D005', '58500.00', 'delivered', NULL, '2025-10-27 02:37:07', '2026-02-19 03:37:07', 2),
(9, '2026-02-18 19:37:07', 11, NULL, 'ORD-D006', '18200.00', 'delivered', NULL, '2025-11-06 03:37:07', '2026-02-19 03:37:07', 4),
(10, '2026-02-18 19:37:07', 9, NULL, 'ORD-D007', '12500.00', 'cancelled', NULL, '2025-11-11 03:37:07', '2026-02-19 03:37:07', 1),
(11, '2026-02-18 19:37:07', 10, NULL, 'ORD-D008', '72000.00', 'delivered', NULL, '2025-11-26 03:37:07', '2026-02-19 03:37:07', 5),
(12, '2026-02-18 19:37:07', 11, NULL, 'ORD-D009', '33600.00', 'delivered', NULL, '2025-12-06 03:37:07', '2026-02-19 03:37:07', 3),
(13, '2026-02-18 19:37:07', 9, NULL, 'ORD-D010', '45000.00', 'delivered', NULL, '2025-12-26 03:37:07', '2026-02-19 03:37:07', 1),
(14, '2026-02-18 19:37:07', 10, NULL, 'ORD-D011', '21700.00', 'processing', NULL, '2025-12-31 03:37:07', '2026-02-19 03:37:07', 2),
(15, '2026-02-18 19:37:07', 11, NULL, 'ORD-D012', '87500.00', 'delivered', NULL, '2026-01-05 03:37:07', '2026-02-19 03:37:07', 4),
(16, '2026-02-18 19:37:07', 9, NULL, 'ORD-D013', '64000.00', 'delivered', NULL, '2026-01-25 03:37:07', '2026-02-19 03:37:07', 5),
(17, '2026-02-18 19:37:07', 10, NULL, 'ORD-D014', '19500.00', 'confirmed', NULL, '2026-02-01 03:37:07', '2026-02-19 03:37:07', 2),
(18, '2026-02-18 19:37:07', 11, NULL, 'ORD-D015', '41000.00', 'processing', NULL, '2026-02-09 03:37:07', '2026-02-19 03:37:07', 3),
(19, '2026-02-18 19:37:07', 9, NULL, 'ORD-D016', '52000.00', 'pending', NULL, '2026-02-15 03:37:07', '2026-02-19 03:37:07', 1),
(20, '2026-02-18 19:37:07', 10, NULL, 'ORD-D017', '28500.00', 'confirmed', NULL, '2026-02-17 03:37:07', '2026-02-19 03:37:07', 2),
(21, '2026-02-18 19:37:07', 11, NULL, 'ORD-D018', '95000.00', 'pending', NULL, '2026-02-18 03:37:07', '2026-02-19 03:37:07', 4);

-- --------------------------------------------------------

--
-- Table structure for table `order_deliveries`
--

CREATE TABLE `order_deliveries` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_deliveries`
--

INSERT INTO `order_deliveries` (`id`, `order_id`, `delivery_date`, `driver_id`, `completed_date`) VALUES
(1, 1, '2026-02-10 00:00:00', 7, '2026-02-10 18:00:00'),
(2, 2, '2026-02-12 00:00:00', 8, '2026-02-13 00:00:00'),
(3, 4, '2025-09-03 19:37:07', 7, '2025-09-03 23:37:07'),
(4, 5, '2025-09-14 19:37:07', 8, '2025-09-14 15:37:07'),
(5, 6, '2025-09-28 19:37:07', 7, '2025-09-28 11:37:07'),
(6, 7, '2025-10-07 19:37:07', 7, '2025-10-08 01:37:07'),
(7, 8, '2025-10-28 19:37:07', 8, '2025-10-28 19:37:07'),
(8, 9, '2025-11-08 19:37:07', 7, '2025-11-08 07:37:07'),
(9, 11, '2025-11-27 19:37:07', 8, '2025-11-27 07:37:07'),
(10, 12, '2025-12-07 19:37:07', 7, '2025-12-07 15:37:07'),
(11, 13, '2025-12-26 19:37:07', 7, '2025-12-26 23:37:07'),
(12, 15, '2026-01-07 19:37:07', 8, '2026-01-07 19:37:07'),
(13, 16, '2026-01-26 19:37:07', 8, '2026-01-26 13:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `selling_price`, `discount`) VALUES
(1, 1, 1, 10, '1450.00', '0.00'),
(2, 2, 2, 10, '2200.00', '0.00'),
(3, 3, 3, 1, '7500.00', '0.00'),
(4, 4, 1, 25, '1450.00', '0.00'),
(5, 5, 2, 20, '2200.00', '0.00'),
(6, 6, 3, 2, '7500.00', '0.00'),
(7, 7, 1, 10, '1450.00', '0.00'),
(8, 7, 6, 8, '1800.00', '0.00'),
(9, 8, 4, 10, '3200.00', '0.00'),
(10, 8, 7, 5, '4500.00', '0.00'),
(11, 8, 5, 5, '1200.00', '0.00'),
(12, 9, 6, 5, '1800.00', '0.00'),
(13, 9, 1, 6, '1200.00', '200.00'),
(14, 10, 1, 10, '1250.00', '0.00'),
(15, 11, 8, 2, '25000.00', '0.00'),
(16, 11, 7, 5, '4400.00', '0.00'),
(17, 12, 3, 3, '7500.00', '0.00'),
(18, 12, 5, 10, '1200.00', '100.00'),
(19, 13, 4, 8, '3200.00', '0.00'),
(20, 13, 6, 12, '1500.00', '0.00'),
(21, 14, 2, 5, '2200.00', '0.00'),
(22, 14, 1, 8, '1450.00', '200.00'),
(23, 15, 8, 3, '25000.00', '0.00'),
(24, 15, 5, 8, '1200.00', '0.00'),
(25, 15, 7, 2, '4500.00', '500.00'),
(26, 16, 2, 15, '2200.00', '0.00'),
(27, 16, 3, 2, '7500.00', '0.00'),
(28, 16, 6, 10, '1800.00', '500.00'),
(29, 17, 4, 3, '3200.00', '0.00'),
(30, 17, 7, 2, '4500.00', '0.00'),
(31, 18, 1, 15, '1450.00', '0.00'),
(32, 18, 2, 8, '2200.00', '200.00'),
(33, 18, 5, 5, '1200.00', '0.00'),
(34, 19, 8, 2, '25000.00', '0.00'),
(35, 19, 4, 1, '2000.00', '0.00'),
(36, 20, 1, 10, '1450.00', '0.00'),
(37, 20, 6, 5, '1800.00', '0.00'),
(38, 20, 3, 1, '7500.00', '500.00'),
(39, 21, 2, 20, '2200.00', '0.00'),
(40, 21, 8, 1, '25000.00', '0.00'),
(41, 21, 7, 4, '4500.00', '0.00'),
(42, 21, 4, 2, '3200.00', '200.00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount`, `payment_date`, `payment_method`) VALUES
(1, 1, '14500.00', '2026-02-18 19:34:35', 'CASH'),
(2, 1, '22000.00', '2026-02-18 19:34:35', 'CARD');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `minimum_stock_level` int(11) DEFAULT 100,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_code`, `product_name`, `category_id`, `unit_price`, `minimum_stock_level`, `image_url`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1, 'P001', 'Coca-Cola 1L', 2, '1450.00', 100, 'uploads/product_6996b67fd42b1.jpeg', 1, 'Coca-Cola Original Taste Carbonated Soft Drink', '2026-02-19 03:34:35', '2026-02-19 07:22:18'),
(2, 'P002', 'Nestomalt 400G', 1, '2200.00', 200, 'uploads/product_6996b69c5cd72.jpg', 1, 'Nestomalt Malted Food Drink Packet 400G', '2026-02-19 03:34:35', '2026-02-19 07:07:08'),
(3, 'P003', 'Harpic Fresh 500ml', 4, '7500.00', 50, 'uploads/product_6996b6bde6299.jpg', 1, 'Harpic Fresh Floral 500ml', '2026-02-19 03:34:35', '2026-02-19 07:07:41'),
(4, 'P004', 'Sunlight Detergent Powder 1kg', 3, '3200.00', 75, 'uploads/product_6996b6d1cf0c8.jpg', 1, 'Sunlight Clean and Jasmine Fresh Detergent Powder - 1kg', '2026-02-19 03:34:35', '2026-02-19 07:08:01'),
(5, 'P005', 'Strepsils 24S', 5, '1200.00', 150, 'uploads/product_6996b6e87b9b8.jpg', 1, 'Strepsils Honey & Lemon 24S', '2026-02-19 03:34:35', '2026-02-19 07:08:24'),
(6, 'P006', 'Clogard Toothpaste 200g', 6, '1800.00', 300, 'uploads/product_6996b6fabb7c8.jpg', 1, 'Clogard Toothpaste Natural Salt 200g', '2026-02-19 03:34:35', '2026-02-19 07:08:42'),
(7, 'P007', 'Nature\'s Secrets Face Wash 100ml', 7, '4500.00', 80, 'uploads/product_6996b70d86bb5.jpg', 1, 'Nature\'s Secrets Face Wash 100ml', '2026-02-19 03:34:35', '2026-02-19 07:09:01'),
(8, 'P008', 'Baby Cheramy Baby Talc 100g', 8, '25000.00', 20, 'uploads/product_6996b71c1c195.jpg', 1, 'Baby Cheramy Baby Talc Classic 100g', '2026-02-19 03:34:35', '2026-02-19 07:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Grocery & Food Items', 'Grocery & Food Items', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(2, 'Beverages', 'Beverages', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(3, 'Household Essentials', 'Household Essentials', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(4, 'Home Cleaning Products', 'Home Cleaning Products', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(5, 'Health Care Products', 'Health Care Products', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(6, 'Personal Care', 'Personal Care', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(7, 'Beauty & Skincare', 'Beauty & Skincare', '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(8, 'Baby Care Products', 'Baby Care Products', '2026-02-19 03:34:35', '2026-02-19 03:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `rdc_id` int(11) DEFAULT NULL,
  `available_quantity` bigint(20) DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`id`, `product_id`, `rdc_id`, `available_quantity`, `last_updated`) VALUES
(1, 1, 1, 30, '2026-02-18 19:34:35'),
(2, 2, 1, 50, '2026-02-18 19:34:35'),
(3, 3, 1, 200, '2026-02-18 19:34:35'),
(4, 1, 2, 400, '2026-02-18 19:34:35'),
(5, 4, 2, 10, '2026-02-18 19:34:35'),
(6, 5, 2, 40, '2026-02-18 19:34:35'),
(7, 6, 3, 100, '2026-02-18 19:34:35'),
(8, 7, 4, 300, '2026-02-18 19:34:35'),
(9, 8, 5, 5, '2026-02-18 19:34:35'),
(10, 1, 3, 15, '2026-02-18 19:37:07'),
(11, 2, 4, 180, '2026-02-18 19:37:07'),
(12, 3, 5, 8, '2026-02-18 19:37:07'),
(13, 4, 1, 70, '2026-02-18 19:37:07'),
(14, 7, 2, 25, '2026-02-18 19:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_count` int(11) DEFAULT NULL,
  `discount_percentage` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `name`, `description`, `product_id`, `product_count`, `discount_percentage`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Cement Bulk Offer', NULL, 1, 10, '5.00', '2026-01-01', '2026-03-31', 1, '2026-02-19 03:34:35', '2026-02-19 03:34:35'),
(2, 'Paint Festival', NULL, 4, 5, '10.00', '2026-02-01', '2026-04-01', 1, '2026-02-19 03:34:35', '2026-02-19 03:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `rdcs`
--

CREATE TABLE `rdcs` (
  `rdc_id` int(11) NOT NULL,
  `rdc_code` enum('NORTH','SOUTH','EAST','WEST','CENTRAL') DEFAULT 'NORTH',
  `rdc_name` varchar(100) NOT NULL,
  `province` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rdcs`
--

INSERT INTO `rdcs` (`rdc_id`, `rdc_code`, `rdc_name`, `province`, `address`, `contact_number`, `created_at`) VALUES
(1, 'NORTH', 'Northern RDC', 'Northern Province', 'Jaffna Industrial Zone', '0711111111', '2026-02-19 03:34:35'),
(2, 'SOUTH', 'Southern RDC', 'Southern Province', 'Galle Trade Center', '0712222222', '2026-02-19 03:34:35'),
(3, 'EAST', 'Eastern RDC', 'Eastern Province', 'Batticaloa Hub', '0713333333', '2026-02-19 03:34:35'),
(4, 'WEST', 'Western RDC', 'Western Province', 'Colombo Warehouse Complex', '0714444444', '2026-02-19 03:34:35'),
(5, 'CENTRAL', 'Central RDC', 'Central Province', 'Kandy Distribution Park', '0715555555', '2026-02-19 03:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `rdc_clerks`
--

CREATE TABLE `rdc_clerks` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rdc_clerks`
--

INSERT INTO `rdc_clerks` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'North Clerk', NULL, NULL, 'north_clerk1@mail.com', 5),
(2, 'South Clerk', NULL, NULL, 'south_clerk1@mail.com', 6);

-- --------------------------------------------------------

--
-- Table structure for table `rdc_districts`
--

CREATE TABLE `rdc_districts` (
  `id` int(11) NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `rdc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdc_drivers`
--

CREATE TABLE `rdc_drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rdc_drivers`
--

INSERT INTO `rdc_drivers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'North Driver', NULL, NULL, 'north_driver1@mail.com', 7),
(2, 'South Driver', NULL, NULL, 'south_driver1@mail.com', 8);

-- --------------------------------------------------------

--
-- Table structure for table `rdc_logistics_officers`
--

CREATE TABLE `rdc_logistics_officers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rdc_managers`
--

CREATE TABLE `rdc_managers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rdc_managers`
--

INSERT INTO `rdc_managers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'North Manager', NULL, NULL, 'north_manager@mail.com', 3),
(2, 'South Manager', NULL, NULL, 'south_manager@mail.com', 4);

-- --------------------------------------------------------

--
-- Table structure for table `rdc_sales_refs`
--

CREATE TABLE `rdc_sales_refs` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `retail_customers`
--

CREATE TABLE `retail_customers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `retail_customers`
--

INSERT INTO `retail_customers` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'Customer One', NULL, NULL, 'customer1@mail.com', 9),
(2, 'Customer Two', NULL, NULL, 'customer2@mail.com', 10),
(3, 'Customer Three', NULL, NULL, 'customer3@mail.com', 11);

-- --------------------------------------------------------

--
-- Table structure for table `shopping_carts`
--

CREATE TABLE `shopping_carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shopping_carts`
--

INSERT INTO `shopping_carts` (`id`, `user_id`, `product_id`, `quantity`) VALUES
(1, 12, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movement_logs`
--

CREATE TABLE `stock_movement_logs` (
  `movement_id` int(11) NOT NULL,
  `rdc_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `movement_type` enum('STOCK_IN','STOCK_OUT','TRANSFER_OUT','TRANSFER_IN','ADJUSTMENT','DAMAGED','RETURNED','EXPIRED') NOT NULL,
  `quantity` int(11) NOT NULL COMMENT 'Positive or negative',
  `previous_quantity` int(11) DEFAULT NULL,
  `new_quantity` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_by_role` varchar(50) NOT NULL,
  `created_by_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movement_logs`
--

INSERT INTO `stock_movement_logs` (`movement_id`, `rdc_id`, `product_id`, `movement_type`, `quantity`, `previous_quantity`, `new_quantity`, `created_by`, `created_by_role`, `created_by_name`, `created_at`, `note`) VALUES
(1, 1, 1, 'STOCK_IN', 100, 400, 500, 1, 'RDC_MANAGER', 'North Manager', '2026-02-19 03:34:35', 'New stock received at RDC'),
(2, 1, 2, 'STOCK_OUT', 100, 900, 800, 1, 'RDC_MANAGER', 'North Manager', '2026-02-19 03:34:35', 'Stock issued for customer orders');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `transfer_id` int(11) NOT NULL,
  `transfer_number` varchar(50) NOT NULL,
  `source_rdc_id` int(11) NOT NULL,
  `destination_rdc_id` int(11) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `requested_by_role` enum('RDC_CLERK','RDC_MANAGER') NOT NULL,
  `requested_date` timestamp NULL DEFAULT current_timestamp(),
  `request_reason` text DEFAULT NULL,
  `is_urgent` tinyint(1) DEFAULT 0,
  `approval_status` enum('CLERK_REQUESTED','PENDING','APPROVED','REJECTED','CANCELLED','RECEIVED') DEFAULT 'CLERK_REQUESTED',
  `approved_by` int(11) DEFAULT NULL,
  `approval_date` timestamp NULL DEFAULT NULL,
  `approval_remarks` text DEFAULT NULL,
  `current_status_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_date` timestamp NULL DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfers`
--

INSERT INTO `stock_transfers` (`transfer_id`, `transfer_number`, `source_rdc_id`, `destination_rdc_id`, `requested_by`, `requested_by_role`, `requested_date`, `request_reason`, `is_urgent`, `approval_status`, `approved_by`, `approval_date`, `approval_remarks`, `current_status_updated`, `completed_date`, `receiver_name`, `delivery_notes`) VALUES
(1, 'TR-1001', 1, 2, 3, 'RDC_MANAGER', '2026-02-19 03:34:35', 'Low cement stock', 1, 'APPROVED', NULL, NULL, NULL, '2026-02-19 03:34:35', NULL, NULL, NULL),
(2, 'TR-1002', 2, 3, 4, 'RDC_MANAGER', '2026-02-19 03:34:35', 'Steel requirement', 0, 'PENDING', NULL, NULL, NULL, '2026-02-19 03:34:35', NULL, NULL, NULL),
(3, 'TR-1003', 3, 1, 5, 'RDC_CLERK', '2026-02-18 03:37:07', 'Urgent cement restock for North RDC', 1, 'PENDING', NULL, NULL, NULL, '2026-02-19 03:37:07', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `item_id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `requested_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_admins`
--

CREATE TABLE `system_admins` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_admins`
--

INSERT INTO `system_admins` (`id`, `name`, `address`, `contact_number`, `email`, `user_id`) VALUES
(1, 'System Admin', '', '', 'sysadmin1@mail.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transfer_status_logs`
--

CREATE TABLE `transfer_status_logs` (
  `log_id` int(11) NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `changed_by` int(11) NOT NULL,
  `change_by_role` varchar(50) NOT NULL,
  `change_by_name` varchar(255) DEFAULT NULL,
  `changed_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','rdc_manager','rdc_clerk','rdc_sales_ref','logistics_officer','rdc_driver','head_office_manager','system_admin') DEFAULT 'customer',
  `rdc_id` int(11) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `rdc_id`, `google_id`, `password_reset_token`, `password_reset_expires`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'sysadmin1', 'sysadmin1@mail.com', '$2y$10$N8yIAvwNNnOqBIhUEKd2Fe9CV7cyYqLYwOx7SErHtSEkqYpTvokMC', 'system_admin', NULL, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:35:28'),
(2, 'headoffice1', 'headoffice1@mail.com', '$2y$10$i5qNMoYTSN7g3lsxBwSubuhBTHiRFqCsRoPOTYoKXd1FXP5tPekeK', 'head_office_manager', NULL, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(3, 'north_manager', 'north_manager@mail.com', '$2y$10$YyqZ6MwgdcaUaUkjCR1bfut0tOwWOqG69tnknRMT5Cf53h4lfwJXy', 'rdc_manager', 1, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(4, 'south_manager', 'south_manager@mail.com', '$2y$10$YyqZ6MwgdcaUaUkjCR1bfut0tOwWOqG69tnknRMT5Cf53h4lfwJXy', 'rdc_manager', 2, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(5, 'north_clerk1', 'north_clerk1@mail.com', '$2y$10$NWfU7JsMnf1w9RKPNooKXeom9GvgxJnf6t78q3hI8F.OB0hQqJSsG', 'rdc_clerk', 1, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(6, 'south_clerk1', 'south_clerk1@mail.com', '$2y$10$NWfU7JsMnf1w9RKPNooKXeom9GvgxJnf6t78q3hI8F.OB0hQqJSsG', 'rdc_clerk', 2, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(7, 'north_driver1', 'north_driver1@mail.com', '$2y$10$N6led9zxSyqe5FWFWeLkhud5dv/WdWZ2B7D2pYobXE.F1QCt6c3gS', 'rdc_driver', 1, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(8, 'south_driver1', 'south_driver1@mail.com', '$2y$10$N6led9zxSyqe5FWFWeLkhud5dv/WdWZ2B7D2pYobXE.F1QCt6c3gS', 'rdc_driver', 2, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(9, 'customer1', 'customer1@mail.com', '$2y$10$7Ko21lVRdSr4sYcxblL4kuYuBP98J7CAdmnZcueoTN0DXpSae5rNy', 'customer', 1, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(10, 'customer2', 'customer2@mail.com', '$2y$10$7Ko21lVRdSr4sYcxblL4kuYuBP98J7CAdmnZcueoTN0DXpSae5rNy', 'customer', 2, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(11, 'customer3', 'customer3@mail.com', '$2y$10$7Ko21lVRdSr4sYcxblL4kuYuBP98J7CAdmnZcueoTN0DXpSae5rNy', 'customer', 3, NULL, NULL, NULL, 1, '2026-02-19 03:34:35', '2026-02-19 08:00:13'),
(12, 'Yasas New', 'yasasnew@gmail.com', '$2y$10$ufSsxjSE2JEljhaH7VdMt.mpTr50UvIrZ9Xct6nFA4BN.f9bCDJpK', 'customer', NULL, '106973302403748897349', NULL, NULL, 1, '2026-02-19 03:44:05', '2026-02-19 07:24:21'),
(13, 'Yasas Pasindu Fernando', 'yasaspasindufernando@gmail.com', '$2y$10$P4xVlRTU11AYuDLhZSDCTOH/LiNO4LyZ2QsS2R0umemms385Ar5H6', 'customer', NULL, '106180848889430269046', '939543336d5bb3342a13af4d14fc8e4a027d63bda4bcd5c1e6c009887a2f067e', '2026-02-19 16:51:45', 1, '2026-02-19 06:35:17', '2026-02-19 10:21:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created_at` (`created_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_type` (`email_type`),
  ADD KEY `idx_email_created` (`created_at`);

--
-- Indexes for table `head_office_managers`
--
ALTER TABLE `head_office_managers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filename` (`filename`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_orders_created` (`created_at`),
  ADD KEY `idx_orders_rdc` (`rdc_id`),
  ADD KEY `idx_orders_rdc_id` (`rdc_id`);

--
-- Indexes for table `order_deliveries`
--
ALTER TABLE `order_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_od_order_id` (`order_id`),
  ADD KEY `idx_od_completed` (`completed_date`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rdc_product` (`rdc_id`,`product_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `rdc_id` (`rdc_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `rdcs`
--
ALTER TABLE `rdcs`
  ADD PRIMARY KEY (`rdc_id`);

--
-- Indexes for table `rdc_clerks`
--
ALTER TABLE `rdc_clerks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rdc_districts`
--
ALTER TABLE `rdc_districts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rdc_id` (`rdc_id`);

--
-- Indexes for table `rdc_drivers`
--
ALTER TABLE `rdc_drivers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rdc_logistics_officers`
--
ALTER TABLE `rdc_logistics_officers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rdc_managers`
--
ALTER TABLE `rdc_managers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rdc_sales_refs`
--
ALTER TABLE `rdc_sales_refs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `retail_customers`
--
ALTER TABLE `retail_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shopping_carts`
--
ALTER TABLE `shopping_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sc_user_id_idx` (`user_id`),
  ADD KEY `fk_sc_product_id_idx` (`product_id`);

--
-- Indexes for table `stock_movement_logs`
--
ALTER TABLE `stock_movement_logs`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_movement_type` (`movement_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_rdc_product` (`rdc_id`,`product_id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`transfer_id`),
  ADD UNIQUE KEY `transfer_number` (`transfer_number`),
  ADD KEY `source_rdc_id` (`source_rdc_id`),
  ADD KEY `destination_rdc_id` (`destination_rdc_id`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `transfer_id` (`transfer_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `system_admins`
--
ALTER TABLE `system_admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transfer_status_logs`
--
ALTER TABLE `transfer_status_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `transfer_id` (`transfer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_google_id` (`google_id`),
  ADD KEY `idx_users_reset_token` (`password_reset_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `head_office_managers`
--
ALTER TABLE `head_office_managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_deliveries`
--
ALTER TABLE `order_deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rdcs`
--
ALTER TABLE `rdcs`
  MODIFY `rdc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rdc_clerks`
--
ALTER TABLE `rdc_clerks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rdc_districts`
--
ALTER TABLE `rdc_districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdc_drivers`
--
ALTER TABLE `rdc_drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rdc_logistics_officers`
--
ALTER TABLE `rdc_logistics_officers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rdc_managers`
--
ALTER TABLE `rdc_managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rdc_sales_refs`
--
ALTER TABLE `rdc_sales_refs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `retail_customers`
--
ALTER TABLE `retail_customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shopping_carts`
--
ALTER TABLE `shopping_carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movement_logs`
--
ALTER TABLE `stock_movement_logs`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `transfer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_admins`
--
ALTER TABLE `system_admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transfer_status_logs`
--
ALTER TABLE `transfer_status_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_rdc` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`),
  ADD CONSTRAINT `orders_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
