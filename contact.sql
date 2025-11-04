-- Create database
CREATE DATABASE IF NOT EXISTS cleantech_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE cleantech_db;

-- Create contact_requests table
CREATE TABLE IF NOT EXISTS contact_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    contact_no VARCHAR(20) NOT NULL,
    service VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'completed', 'cancelled') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create admin_users table (optional - for managing requests)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123 - change this immediately!)
-- Password is hashed using PHP's password_hash function
INSERT INTO admin_users (username, password, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@cleantech.com', 'admin');

-- Create notes table (for adding notes to contact requests)
CREATE TABLE IF NOT EXISTS request_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    admin_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES contact_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_request_id (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create services table (for managing available services)
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price_range VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default services
INSERT INTO services (service_name, description, price_range) VALUES
('Water Tank Cleaning', 'Professional water tank cleaning and sanitization services', '₹500-₹2000'),
('Sofa-Carpet Cleaning', 'Deep cleaning for sofas and carpets using advanced equipment', '₹800-₹3000'),
('Bathroom Cleaning', 'Thorough bathroom cleaning and sanitization', '₹300-₹1000'),
('Home Cleaning', 'Complete home cleaning services covering all areas', '₹1000-₹5000'),
('Swimming-Pool Cleaning', 'Professional pool maintenance and cleaning', '₹1500-₹5000'),
('Floor Cleaning', 'Specialized floor cleaning for all types of surfaces', '₹500-₹2500'),
('Solar Panel Cleaning', 'Expert solar panel cleaning to maximize efficiency', '₹1000-₹3000'),
('Drainage Cleaning', 'Professional drainage cleaning and maintenance', '₹800-₹2000');

-- View to get contact requests with service details
CREATE OR REPLACE VIEW contact_requests_view AS
SELECT 
    cr.id,
    cr.name,
    cr.email,
    cr.contact_no,
    cr.service,
    cr.message,
    cr.status,
    cr.created_at,
    cr.updated_at
FROM contact_requests cr
ORDER BY cr.created_at DESC;

-- View to get statistics
CREATE OR REPLACE VIEW contact_statistics AS
SELECT 
    COUNT(*) as total_requests,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_requests,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_requests,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_requests,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_requests,
    SUM(CASE WHEN WEEK(created_at) = WEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week_requests,
    SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) THEN 1 ELSE 0 END) as this_month_requests
FROM contact_requests;