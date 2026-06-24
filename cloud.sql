-- =============================================
-- CLOUD PLATFORM MINI - DATABASE SCHEMA
-- CodeIgniter 4 Compatible
-- =============================================

USE cloud_platform;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =============================================
-- TABLE: users
-- =============================================
CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `balance` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: server_plans
-- =============================================
CREATE TABLE `server_plans` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `cpu_cores` int(11) NOT NULL,
  `ram_gb` int(11) NOT NULL,
  `storage_gb` int(11) NOT NULL,
  `bandwidth_gb` int(11) DEFAULT 1000,
  `price_per_hour` decimal(10,4) NOT NULL,
  `price_per_month` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: servers
-- =============================================
CREATE TABLE `servers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL,
  `server_name` varchar(255) NOT NULL,
  `server_type` varchar(50) DEFAULT 'VPS',
  `ip_address` varchar(45) DEFAULT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `os` varchar(100) DEFAULT 'Ubuntu 22.04',
  `status` enum('provisioning','running','stopped','suspended','terminated') DEFAULT 'provisioning',
  `cpu_cores` int(11) NOT NULL,
  `ram_gb` int(11) NOT NULL,
  `storage_gb` int(11) NOT NULL,
  `bandwidth_used_gb` decimal(10,2) DEFAULT 0.00,
  `uptime_hours` decimal(10,2) DEFAULT 0.00,
  `last_start_time` datetime DEFAULT NULL,
  `last_stop_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `servers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `servers_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `server_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: server_monitoring
-- =============================================
CREATE TABLE `server_monitoring` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_id` int(11) UNSIGNED NOT NULL,
  `cpu_usage` decimal(5,2) DEFAULT 0.00,
  `ram_usage` decimal(5,2) DEFAULT 0.00,
  `disk_usage` decimal(5,2) DEFAULT 0.00,
  `network_in` decimal(10,2) DEFAULT 0.00,
  `network_out` decimal(10,2) DEFAULT 0.00,
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  KEY `idx_recorded` (`recorded_at`),
  CONSTRAINT `server_monitoring_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: billing_transactions
-- =============================================
CREATE TABLE `billing_transactions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `server_id` int(11) UNSIGNED DEFAULT NULL,
  `transaction_type` enum('topup','charge','refund','penalty') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `balance_before` decimal(15,2) DEFAULT 0.00,
  `balance_after` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `server_id` (`server_id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `billing_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `billing_transactions_ibfk_2` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: server_usage_logs
-- =============================================
CREATE TABLE `server_usage_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `server_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `plan_id` int(11) UNSIGNED NOT NULL,
  `hours_used` decimal(10,4) NOT NULL,
  `cost` decimal(10,4) NOT NULL,
  `billing_period_start` datetime NOT NULL,
  `billing_period_end` datetime NOT NULL,
  `charged` tinyint(1) DEFAULT 0,
  `charged_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_charged` (`charged`),
  CONSTRAINT `server_usage_logs_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `server_usage_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: activity_logs
-- =============================================
CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_action` (`action`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: system_settings
-- =============================================
CREATE TABLE `system_settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'string',
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERT DEFAULT DATA
-- =============================================

-- Default Admin User (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `balance`, `status`, `email_verified`) VALUES
('admin', 'admin@cloudplatform.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 10000.00, 'active', 1);

-- Default Server Plans
INSERT INTO `server_plans` (`plan_name`, `cpu_cores`, `ram_gb`, `storage_gb`, `bandwidth_gb`, `price_per_hour`, `price_per_month`, `description`, `status`) VALUES
('Starter', 1, 1, 25, 1000, 0.0075, 5.00, 'Perfect for small projects and development', 'active'),
('Basic', 1, 2, 50, 2000, 0.0150, 10.00, 'Ideal for small applications', 'active'),
('Standard', 2, 4, 80, 3000, 0.0300, 20.00, 'Great for medium workloads', 'active'),
('Advanced', 4, 8, 160, 4000, 0.0600, 40.00, 'For demanding applications', 'active'),
('Pro', 8, 16, 320, 5000, 0.1200, 80.00, 'High performance computing', 'active'),
('Enterprise', 16, 32, 640, 10000, 0.2400, 160.00, 'Maximum power and reliability', 'active');

-- Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'Cloud Platform Mini', 'string', 'Website name'),
('site_email', 'noreply@cloudplatform.local', 'string', 'System email address'),
('billing_enabled', '1', 'boolean', 'Enable billing system'),
('auto_charge_interval', '3600', 'integer', 'Auto charge interval in seconds (1 hour)'),
('min_balance', '5.00', 'decimal', 'Minimum balance to keep servers running'),
('signup_enabled', '1', 'boolean', 'Allow new user registrations'),
('email_verification', '0', 'boolean', 'Require email verification'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode');


-- =============================================
-- TABLE: domain_pricing (Harga Ekstensi Domain)
-- =============================================
CREATE TABLE `domain_pricing` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tld` varchar(20) NOT NULL, -- Contoh: .com, .net, .my.id
  `register_price` decimal(15,2) NOT NULL,
  `renew_price` decimal(15,2) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tld` (`tld`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: domains (Manajemen Domain User)
-- =============================================
CREATE TABLE `domains` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `tld_id` int(11) UNSIGNED NOT NULL,
  `domain_name` varchar(255) NOT NULL, -- Contoh: smartta.web.id
  `status` enum('pending','active','expired','suspended') DEFAULT 'pending',
  `registration_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 0,
  `domain_lock` tinyint(1) DEFAULT 1,
  `epp_code` varchar(64) DEFAULT NULL,
  `epp_revealed_at` datetime DEFAULT NULL,
  `ns1` varchar(255) DEFAULT 'ns1.cloudplatform.local',
  `ns2` varchar(255) DEFAULT 'ns2.cloudplatform.local',
  `nameserver_mode` enum('default','custom') DEFAULT 'default',
  `nameserver_status` enum('pending','pointed','warning') DEFAULT 'pointed',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_name` (`domain_name`),
  KEY `user_id` (`user_id`),
  KEY `tld_id` (`tld_id`),
  CONSTRAINT `domains_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `domains_ibfk_2` FOREIGN KEY (`tld_id`) REFERENCES `domain_pricing` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: domain_dns_records (DNS Zone User)
-- =============================================
CREATE TABLE `domain_dns_records` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) UNSIGNED NOT NULL,
  `record_type` enum('A','AAAA','CNAME','MX','TXT','NS') NOT NULL,
  `host` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `priority` int(11) DEFAULT NULL,
  `ttl` int(11) DEFAULT 3600,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `domain_id` (`domain_id`),
  CONSTRAINT `domain_dns_records_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: domain_child_nameservers (Child NS / Glue)
-- =============================================
CREATE TABLE `domain_child_nameservers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) UNSIGNED NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `ipv4` varchar(45) DEFAULT NULL,
  `ipv6` varchar(45) DEFAULT NULL,
  `status` enum('active','disabled') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_child_hostname` (`domain_id`, `hostname`),
  KEY `domain_id` (`domain_id`),
  CONSTRAINT `domain_child_nameservers_ibfk_1` FOREIGN KEY (`domain_id`) REFERENCES `domains` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default TLDs
INSERT INTO `domain_pricing` (`tld`, `register_price`, `renew_price`) VALUES 
('.com', 9.99, 10.99),
('.net', 11.99, 12.99),
('.my.id', 1.49, 1.49),
('.web.id', 3.49, 3.49);

COMMIT;
