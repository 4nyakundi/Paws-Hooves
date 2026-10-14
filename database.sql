-- Paws & Hooves Database Schema
-- Database: cwcwdmnn_wangari26

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` VARCHAR(64) NOT NULL UNIQUE,
  `owner_name` VARCHAR(150) NOT NULL,
  `owner_phone` VARCHAR(50) NOT NULL,
  `owner_email` VARCHAR(150) DEFAULT NULL,
  `owner_location` VARCHAR(255) DEFAULT NULL,
  `pet_name` VARCHAR(100) NOT NULL,
  `pet_type` VARCHAR(50) NOT NULL,
  `service_type` VARCHAR(100) NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` VARCHAR(20) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`appointment_date`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial default admin user
-- dr.vet@pawhooves.co.ke / Dr.Vet 0759396531
-- Password hash generated using password_hash('Dr.Vet 0759396531', PASSWORD_BCRYPT)
INSERT INTO `admin_users` (`username`, `password_hash`, `role`)
VALUES ('dr.vet@pawhooves.co.ke', '$2y$10$w5e0z1BqM9jTfM9qjS7X8uG8I7kU9FfJk9E9m7P2zZkQ3d7zQ9l2O', 'admin')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);
