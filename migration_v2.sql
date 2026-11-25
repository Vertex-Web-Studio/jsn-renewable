-- JSN Renewable Energy - Non-Destructive Database Migration v2
-- Safely upgrades existing databases without resetting or deleting project data.

USE `jsn_renewable_db`;

-- 1. Add installation_year to projects if missing
ALTER TABLE `projects`
    ADD COLUMN IF NOT EXISTS `installation_year` SMALLINT NULL AFTER `completion_date`,
    ADD INDEX IF NOT EXISTS `idx_projects_published` (`is_published`, `is_featured`),
    ADD INDEX IF NOT EXISTS `idx_projects_category` (`category`);

-- Backfill installation_year from completion_date where null
UPDATE `projects`
SET `installation_year` = YEAR(`completion_date`)
WHERE `installation_year` IS NULL AND `completion_date` IS NOT NULL;

-- 2. Create or upgrade visitor_logs table
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

ALTER TABLE `visitor_logs`
    ADD COLUMN IF NOT EXISTS `referrer` VARCHAR(255) NULL AFTER `page_visited`,
    ADD COLUMN IF NOT EXISTS `browser` VARCHAR(50) DEFAULT 'Other' AFTER `referrer`,
    ADD COLUMN IF NOT EXISTS `os` VARCHAR(50) DEFAULT 'Other' AFTER `browser`;
