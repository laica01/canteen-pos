-- =====================================================
--  Mura-Mura Canteen POS — Database Setup
--  Run this in phpMyAdmin or MySQL CLI
-- =====================================================

CREATE DATABASE IF NOT EXISTS canteen_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE canteen_pos;

-- STUDENTS (includes staff, admin)
CREATE TABLE IF NOT EXISTS students (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    fullname   VARCHAR(120) NOT NULL,
    username   VARCHAR(60)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    balance    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    role       ENUM('student','staff','admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PRODUCTS / MENU ITEMS
CREATE TABLE IF NOT EXISTS products (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(120) NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    image        VARCHAR(255) DEFAULT '',
    stock        INT NOT NULL DEFAULT 0,
    status       ENUM('Available','Sold Out') NOT NULL DEFAULT 'Available',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    product_name VARCHAR(120) NOT NULL,
    quantity     INT NOT NULL DEFAULT 1,
    total        DECIMAL(10,2) NOT NULL,
    status       ENUM('Preparing','Completed','Cancelled') NOT NULL DEFAULT 'Preparing',
    cancel_until DATETIME,
    is_cancelled TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);

-- =====================================================
--  SAMPLE DATA (optional — remove before production)
-- =====================================================

-- Default admin account (password: admin123)
INSERT INTO students (fullname, username, password, balance, role) VALUES
('Admin User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Sample menu items
INSERT INTO products (product_name, price, stock, status) VALUES
('Sinangag at Itlog', 35.00, 30, 'Available'),
('Adobo Rice', 45.00, 25, 'Available'),
('Pancit Canton', 40.00, 20, 'Available'),
('Sinigang na Baboy', 55.00, 15, 'Available'),
('Lugaw', 20.00, 50, 'Available'),
('Hotsilog', 50.00, 20, 'Available'),
('Fruit Shake', 30.00, 40, 'Available'),
('Bottled Water', 15.00, 100, 'Available')
ON DUPLICATE KEY UPDATE id=id;
