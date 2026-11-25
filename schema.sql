-- JSN Renewable Energy Private Limited - Normalized MySQL Database Schema
-- Approved Tagline: A new way to power

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
    `category` ENUM('Commercial Solar', 'Industrial Solar Sheds', 'PM-KUSUM Ground Solar', 'Housing Societies', 'Government & PSU', 'Hospitals', 'Schools & Institutions', 'Residential Solar') NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `client_name` VARCHAR(150) NULL,
    `system_type` ENUM('On-Grid Solar', 'Off-Grid Solar', 'Hybrid Solar', 'Ground-Mounted Solar') DEFAULT 'On-Grid Solar',
    `capacity_kw` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `completion_date` DATE NULL,
    `installation_year` SMALLINT NULL,
    `description` TEXT NULL,
    `hero_image` VARCHAR(255) NULL,
    `is_published` TINYINT(1) DEFAULT 1,
    `is_featured` TINYINT(1) DEFAULT 0,
    `seo_title` VARCHAR(255) NULL,
    `seo_description` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_projects_published` (`is_published`, `is_featured`),
    INDEX `idx_projects_category` (`category`)
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

-- 8. Server-Side Visitor Analytics Table
CREATE TABLE IF NOT EXISTS `visitor_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(64) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `page_visited` VARCHAR(255) NOT NULL,
    `referrer` VARCHAR(255) NULL,
    `browser` VARCHAR(50) DEFAULT 'Other',
    `os` VARCHAR(50) DEFAULT 'Other',
    `device_type` VARCHAR(30) DEFAULT 'Desktop',
    `user_agent` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_visitor_session` (`session_id`),
    INDEX `idx_visitor_created` (`created_at`),
    INDEX `idx_visitor_page` (`page_visited`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Initial Verified Default Projects
INSERT IGNORE INTO `projects` (`title`, `slug`, `category`, `location`, `client_name`, `system_type`, `capacity_kw`, `completion_date`, `installation_year`, `description`, `hero_image`, `is_published`, `is_featured`) VALUES
('2.55 MW PM-KUSUM Ground Solar Plant #1', '2-55-mw-pm-kusum-ground-solar-plant-1', 'PM-KUSUM Ground Solar', 'Bhiwani, Haryana', 'Utility DISCOM Grid', 'Ground-Mounted Solar', 2550.00, '2024-03-15', 2024, 'Grid-connected MW-scale utility power plant engineered and commissioned under Component-A of PM-KUSUM with 33kV substation tie-in.', 'assets/projects/bhiwani-5mw-1.jpg', 1, 1),
('2.55 MW Utility Ground Solar Plant #2', '2-55-mw-utility-ground-solar-plant-2', 'PM-KUSUM Ground Solar', 'Kapurisar, Bikaner, Rajasthan', 'JVVNL Grid PPA', 'Ground-Mounted Solar', 2550.00, '2024-08-20', 2024, 'Grid-connected utility solar farm engineered in high-irradiance Bikaner desert region under 25-year JVVNL PPA contract.', 'assets/projects/bhiwani-5mw-2.jpg', 1, 1),
('325 kW RWA Housing Society Rooftop', '325-kw-rwa-housing-society-rooftop', 'Housing Societies', 'CHS Complex, Maharashtra', 'Cooperative Housing Society', 'On-Grid Solar', 325.00, '2024-06-10', 2024, 'Multi-tower residential rooftop installation offsetting high-speed elevators, water pumps, and campus common area lighting under MSEDCL.', 'assets/projects/industrial-shed-2.jpg', 1, 1),
('170 kW Industrial Shed Solar Plant', '170-kw-industrial-shed-solar-plant', 'Industrial Solar Sheds', 'Jhajjar, Haryana', 'Manufacturing Unit', 'On-Grid Solar', 170.00, '2024-05-10', 2024, 'Custom non-penetrative roof structure engineering for corrugated metal factory shed minimizing daytime power tariffs under DHBVN.', 'assets/projects/industrial-shed-1.jpg', 1, 1),
('150 kW On-Grid Commercial Solar Plant', '150-kw-on-grid-commercial-solar-plant', 'Commercial Solar', 'Bulandshahr, UP', 'Commercial Manufacturing Unit', 'On-Grid Solar', 150.00, '2024-04-05', 2024, 'Rooftop solar installation for commercial manufacturing unit with bi-directional net metering clearance under UPPCL.', 'assets/projects/industrial-shed-3.jpg', 1, 0),
('60 kW Solar Power Installation', '60-kw-solar-power-installation', 'Schools & Institutions', 'Mainpuri, UP', 'Institutional Facility', 'On-Grid Solar', 60.00, '2024-01-18', 2024, 'High-efficiency mono-PERC rooftop solar power installation delivering clean energy independence for institutional facility.', 'assets/projects/mainpuri-60kw.jpg', 1, 1),
('40 kW Assotech Blith RWA Solar Rooftop', '40-kw-assotech-blith-rwa-solar-rooftop', 'Housing Societies', 'Gurugram, Haryana', 'Assotech Blith RWA', 'On-Grid Solar', 40.00, '2024-02-12', 2024, 'High-rise residential rooftop solar installation offsetting common facility power bills under Haryana solar policy.', 'assets/projects/bhiwani-ground-7.jpg', 1, 0);

-- Insert Default Settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_title', 'JSN Renewable Energy Private Limited'),
('tagline', 'A new way to power'),
('phone', '+91 92890 44454'),
('email', 'info@jsnrenewable.com'),
('address', 'Plot No. 86, Kajipura, Near LK International School, Wave City, Ghaziabad, UP-201002'),
('stat_capacity', '5.85 MW'),
('stat_kusum', '5.10 MW'),
('stat_rooftop', '745 kW'),
('stat_clearance', '100%');
