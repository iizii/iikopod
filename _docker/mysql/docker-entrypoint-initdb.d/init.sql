CREATE DATABASE IF NOT EXISTS `default`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'default'@'%'
    IDENTIFIED WITH caching_sha2_password BY 'secret';

GRANT ALL ON `default`.* TO 'default'@'%';

FLUSH PRIVILEGES;