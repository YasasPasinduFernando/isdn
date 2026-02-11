-- ============================================
-- RDC Districts Table
-- ============================================

CREATE TABLE IF NOT EXISTS `rdc_districts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL,
  `description` varchar(150) DEFAULT NULL,
  `rdc_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rdc_id` (`rdc_id`),
  CONSTRAINT `rdc_district_rdc_fk` FOREIGN KEY (`rdc_id`) REFERENCES `rdcs` (`rdc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
