-- Add username field to users table for Facebook-style login
ALTER TABLE `users` 
ADD COLUMN `username` VARCHAR(50) NULL AFTER `name`,
ADD UNIQUE KEY `username` (`username`);

-- Update existing users to have username (use name as username)
UPDATE `users` SET `username` = LOWER(REPLACE(`name`, ' ', '')) WHERE `username` IS NULL;

