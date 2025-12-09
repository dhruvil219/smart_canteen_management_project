-- Canteen Website Database Schema
CREATE DATABASE IF NOT EXISTS canteen_db;
USE canteen_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Food categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Food items table
CREATE TABLE food_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    is_veg BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_time TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    food_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin@123)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@canteen.com', '$2y$10$a2Mu2knddPmVmZPISmLtve.gTDlPNYQDR8II1yWjcBUZSJsycR0Gq', 'Admin User', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Main Course', 'Hearty vegetarian main dishes'),
('Snacks', 'Light vegetarian snacks and appetizers'),
('Beverages', 'Fresh juices, tea, coffee and other drinks'),
('Desserts', 'Sweet vegetarian treats');

-- Insert sample food items
INSERT INTO food_items (name, description, price, category_id, image_url) VALUES 
('Vegetable Biryani', 'Aromatic basmati rice with mixed vegetables and spices', 120.00, 1, 'images/veg-biryani.jpg'),
('Paneer Butter Masala', 'Creamy tomato curry with cottage cheese', 140.00, 1, 'images/paneer-butter-masala.jpg'),
('Dal Tadka', 'Yellow lentils tempered with spices', 80.00, 1, 'images/daltadka.jpg'),
('Samosa', 'Crispy pastry filled with spiced potatoes', 25.00, 2, 'images/samosa.jpg'),
('Masala Chai', 'Traditional Indian spiced tea', 15.00, 3, 'images/masala-chai.jpg'),
('Fresh Lime Water', 'Refreshing lime juice with mint', 20.00, 3, 'images/lime-water.jpg.webp'),
('Gulab Jamun', 'Sweet milk dumplings in sugar syrup', 30.00, 4, 'images/gulab-jamun.jpg');

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NULL,
    user_id INT NULL,
    rating TINYINT NOT NULL,
    comments TEXT,
    name VARCHAR(100) NULL,
    email VARCHAR(150) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
