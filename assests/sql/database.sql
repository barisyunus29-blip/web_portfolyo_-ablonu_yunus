-- Araba Parçaları E-Ticaret Web Sitesi - Veritabanı Şeması

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(10),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
);

CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image_url VARCHAR(500),
    specifications JSON,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_slug (slug),
    INDEX idx_featured (featured),
    INDEX idx_category (category_id)
);

CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY unique_user_product (user_id, product_id)
);

CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(10) NOT NULL,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order (order_id)
);

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (email)
);

-- Örnek Kategoriler
INSERT INTO categories (name, slug, description, icon) VALUES
('Motor Parçaları', 'motor-parçaları', 'Motor ve motor aksesuarları', '🔧'),
('Fren Sistemi', 'fren-sistemi', 'Fren diskleri, balataları ve aksesuarları', '🛑'),
('Elektrik Sistemi', 'elektrik-sistemi', 'Alternatör, marş motoru ve elektrik bileşenleri', '⚡'),
('Süspansiyon', 'süspansiyon', 'Amortisör, yay ve süspansiyon bileşenleri', '🚗'),
('Kaporta', 'kaporta', 'Kaput, kapı, çamurluk ve kaporta parçaları', '🛡️'),
('Aydınlatma', 'aydınlatma', 'Far, arka lamba ve aydınlatma sistemleri', '💡');

-- Örnek Ürünler
INSERT INTO products (category_id, name, slug, description, price, stock, featured) VALUES
(1, 'Motor Yağı 5W-30', 'motor-yağı-5w-30', 'Yüksek kaliteli sentetik motor yağı', 150.00, 50, TRUE),
(1, 'Hava Filtresi', 'hava-filtresi', 'Orijinal hava filtresi', 85.00, 30, TRUE),
(2, 'Fren Diski Ön', 'fren-diski-ön', 'Yüksek performanslı fren diski', 320.00, 25, TRUE),
(2, 'Fren Balatası', 'fren-balatası', 'Organik fren balatası seti', 180.00, 40, FALSE),
(3, 'Alternatör', 'alternatör', 'Yüksek amperli alternatör', 450.00, 15, TRUE),
(3, 'Marş Motoru', 'marş-motoru', 'Orijinal marş motoru', 380.00, 20, FALSE),
(4, 'Ön Amortisör', 'ön-amortisör', 'Kaliteli ön amortisör', 280.00, 35, TRUE),
(4, 'Arka Yay', 'arka-yay', 'Paslanmaz arka yay', 220.00, 25, FALSE),
(5, 'Kaput', 'kaput', 'Orijinal kaput paneli', 500.00, 10, FALSE),
(5, 'Ön Çamurluk', 'ön-çamurluk', 'Ön çamurluk paneli', 350.00, 15, FALSE),
(6, 'Ön Far Seti', 'ön-far-seti', 'LED ön far seti', 600.00, 12, TRUE),
(6, 'Arka Lamba', 'arka-lamba', 'LED arka lamba', 200.00, 30, FALSE);

-- Admin Kullanıcısı
INSERT INTO users (email, password, name, role) VALUES
('admin@arabaparçalari.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/1m', 'Admin', 'admin');
