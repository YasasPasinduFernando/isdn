-- ============================================
-- Order Delivery Table
-- ============================================

CREATE TABLE IF NOT EXISTS `order_deliveries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `driver_id` int DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
