-- ============================================
-- Payment Table
-- ============================================

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(45) DEFAULT NULL,
  `amount` varchar(45) DEFAULT NULL,
  `payment_date` varchar(45) DEFAULT NULL,
  `payment_method` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
