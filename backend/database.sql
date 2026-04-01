-- backend/database.sql

-- Database creation
CREATE DATABASE IF NOT EXISTS washmate;
USE washmate;

-- Users table (Customers and Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    address TEXT,
    fcm_token VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Laundry Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    icon_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending', 'picked_up', 'washing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    pickup_date DATETIME,
    delivery_date DATETIME,
    special_instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items table (Services included in an order)
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    service_id INT,
    quantity INT DEFAULT 1,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
);

-- Insert a default admin user (Password: admin123)
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES 
('Admin', 'admin@washmate.com', '$2y$10$FHfBGSU1bXUTrl43T8XR9eaezpxC7h/SC0M14iPp.ONtVWygZ4wKu', 'admin');

-- Insert some default services
INSERT IGNORE INTO services (name, description, price, icon_url) VALUES 
('Wash & Fold', 'Standard wash and fold service per kg', 5.00, 'icons/wash_fold.png'),
('Ironing', 'Professional ironing service per item', 2.00, 'icons/ironing.png'),
('Dry Cleaning', 'Premium dry cleaning for delicate items', 10.00, 'icons/dry_clean.png');
