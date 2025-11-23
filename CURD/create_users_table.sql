



























=-- Create users table for authentication (Facebook-style)
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `remember_token_hash` varchar(255) DEFAULT NULL,
  `remember_token_expires` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user
-- Email: admin@example.com
-- Password: admin123
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `remember_token_hash`, `remember_token_expires`, `created_at`)
VALUES (1, 'Administrator', 'admin@example.com', '$2y$10$GYUA9O8UXx805JOWApUOJeFbPrD3tz28DDg4XSxgXR8p/ELgNmiZK', NULL, NULL, NOW())
ON DUPLICATE KEY UPDATE `name` = 'Administrator';

