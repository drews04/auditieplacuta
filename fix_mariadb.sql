-- Fix MariaDB Connection Issue
-- Run this in phpMyAdmin SQL tab or MySQL command line

-- 1. Show current users
SELECT User, Host FROM mysql.user;

-- 2. Grant all privileges to root@localhost
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION;

-- 3. Grant all privileges to root@127.0.0.1
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION;

-- 4. Flush privileges to apply changes
FLUSH PRIVILEGES;

-- 5. Verify the grants
SHOW GRANTS FOR 'root'@'localhost';
SHOW GRANTS FOR 'root'@'127.0.0.1';

