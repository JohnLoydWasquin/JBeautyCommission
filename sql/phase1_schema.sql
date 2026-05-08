-- Phase 1: JBeauty foundation schema
CREATE DATABASE IF NOT EXISTS `jbeauty_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jbeauty_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone_number` VARCHAR(40) NOT NULL,
  `is_phone_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `id_document_path` VARCHAR(255) DEFAULT NULL,
  `kyc_status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `unique_phone` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
