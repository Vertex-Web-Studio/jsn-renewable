-- JSN Renewable Energy Private Limited - Normalized MySQL Database Schema
-- Approved Tagline: A NEW WAY TO POWER

CREATE DATABASE IF NOT EXISTS `jsn_renewable_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jsn_renewable_db`;

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `role` ENUM('SuperAdmin', 'Admin', 'Manager') DEFAULT 'Admin',
    `last_login` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `category` ENUM('Commercial Solar', 'Industrial Solar Sheds', 'PM-KUSUM Ground Solar', 'Housing Societies', 'Government & PSU', 'Hospitals', 'Schools & Institutions') NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `client_name` VARCHAR(150) NULL,
    `system_type` ENUM('On-Grid Solar', 'Off-Grid Solar', 'Hybrid Solar', 'Ground-Mounted Solar') DEFAULT 'On-Grid Solar',
    `capacity_kw` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `completion_date` DATE NULL,
    `description` TEXT NULL,
    `hero_image` VARCHAR(255) NULL,
    `is_published` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `seo_title` VARCHAR(255) NULL,
    `seo_description` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Project Gallery Images Table
CREATE TABLE IF NOT EXISTS `project_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) NULL,
    `alt_text` VARCHAR(255) NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Customer Enquiries & Lead Management Table
CREATE TABLE IF NOT EXISTS `enquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(100) NULL,
    `project_category` VARCHAR(100) NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `message` TEXT NULL,
    `status` ENUM('New', 'Contacted', 'Follow-up', 'Converted', 'Rejected') DEFAULT 'New',
    `follow_up_notes` TEXT NULL,
    `assigned_admin_id` INT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Enquiry Follow-up Notes Table
CREATE TABLE IF NOT EXISTS `enquiry_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `enquiry_id` INT NOT NULL,
    `admin_id` INT NOT NULL,
    `note` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Centralized Media Library Table
CREATE TABLE IF NOT EXISTS `media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `file_url` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Website Global Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Initial Verified Default Projects
INSERT INTO `projects` (`title`, `slug`, `category`, `location`, `client_name`, `system_type`, `capacity_kw`, `completion_date`, `description`, `hero_image`, `is_published`, `is_featured`) VALUES
('2.55 MW PM-KUSUM Ground Solar Plant #1', '2-55-mw-pm-kusum-ground-solar-plant-1', 'PM-KUSUM Ground Solar', 'Bhiwani, Haryana', 'Utility DISCOM Grid', 'Ground-Mounted Solar', 2550.00, '2024-03-15', 'Grid-connected MW-scale utility power plant engineered and commissioned under federal PM-KUSUM program in Bhiwani, Haryana.', 'assets/projects/bhiwani-5mw-1.jpg', 1, 1),
('2.55 MW Utility Ground Solar Plant #2', '2-55-mw-utility-ground-solar-plant-2', 'PM-KUSUM Ground Solar', 'Rajasthan / NCR', 'State Electricity Transmission', 'Ground-Mounted Solar', 2550.00, '2024-08-20', 'MW-scale utility grid project commissioned with full substation synchronization and 25-year DISCOM Power Purchase Agreement (PPA).', 'assets/projects/bhiwani-5mw-2.jpg', 1, 1),
('170 kW Industrial Shed Solar Plant', '170-kw-industrial-shed-solar-plant', 'Industrial Solar Sheds', 'Jhajjar, Haryana', 'Manufacturing Unit', 'On-Grid Solar', 170.00, '2024-05-10', 'Custom roof structure engineering for corrugated metal factory shed minimizing electricity tariffs under DHBVN net-metering.', 'assets/projects/industrial-shed-1.jpg', 1, 1),
('60 kW Solar Power Installation', '60-kw-solar-power-installation', 'Commercial Solar', 'Mainpuri, UP', 'Institutional Facility', 'On-Grid Solar', 60.00, '2024-01-18', 'High-efficiency mono-PERC rooftop solar power setup for institutional facility in Mainpuri, UP.', 'assets/projects/mainpuri-60kw.jpg', 1, 1);

-- Insert Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'JSN Renewable Energy Private Limited'),
('tagline', 'A NEW WAY TO POWER'),
('phone', '+91 92890 44454'),
('email', 'info@jsnrenewable.com'),
('address', 'Plot No. 86, Kajipura, Near LK International School, Wave City, Ghaziabad, UP-201002'),
('stat_capacity', '5.85 MW'),
('stat_kusum', '5.10 MW'),
('stat_rooftop', '745 kW'),
('stat_clearance', '100%');
