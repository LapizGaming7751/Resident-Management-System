-- Add blacklist table to existing database
-- Run this script to add the blacklist functionality

-- Create blacklist table
CREATE TABLE IF NOT EXISTS `blacklist` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `blacklisted_car_plate` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blacklisted_car_plate` (`blacklisted_car_plate`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add foreign key constraint
ALTER TABLE `blacklist`
  ADD CONSTRAINT `blacklist_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;