-- Create database for user system
CREATE DATABASE IF NOT EXISTS user_system;
USE user_system;

-- Create users table for MySQL
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Create a sample index for better performance
CREATE INDEX idx_users_login ON users(username, email);

-- Note: MongoDB collections will be created automatically when first document is inserted
-- The profile collection in MongoDB will have the following structure:
-- {
--   "user_id": NumberInt(1),
--   "first_name": "John",
--   "last_name": "Doe",
--   "age": NumberInt(25),
--   "date_of_birth": "1999-01-01",
--   "contact": "+1234567890",
--   "address": "123 Main St",
--   "city": "New York",
--   "country": "USA",
--   "bio": "Software Developer",
--   "created_at": ISODate(),
--   "updated_at": ISODate()
-- }