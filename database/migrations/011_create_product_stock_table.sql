-- ============================================
-- Product Stock Table
-- ============================================

CREATE TABLE IF NOT EXISTS `product_stocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `rdc_id` int DEFAULT NULL,
  `available_quantity` bigint DEFAULT NULL,
  `last_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `rdc_id` (`rdc_id`),
  CONSTRAINT `product_stock_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `product_stock_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`),
  UNIQUE KEY unique_rdc_product (rdc_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
