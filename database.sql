-- Atmapotek: schema and safe demo data for local development.
CREATE DATABASE IF NOT EXISTS tubes_pwd_apotek
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tubes_pwd_apotek;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  address TEXT NULL,
  role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS medicines (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(12,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  category VARCHAR(100) NOT NULL,
  image VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_price DECIMAL(12,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_details (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  medicine_id INT UNSIGNED NOT NULL,
  quantity INT NOT NULL,
  price_at_purchase DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_order_details_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_order_details_medicine FOREIGN KEY (medicine_id) REFERENCES medicines(id)
) ENGINE=InnoDB;

-- Demo accounts. Passwords: admin = Admin123!, pelanggan = User12345!
INSERT IGNORE INTO users (id, username, email, password, full_name, address, role) VALUES
  (1, 'admin', 'admin@atmapotek.local', '$2y$12$YpuRrezOiukUDP/kVysLXebUlfFIyXovTQ28Fdagt/gOFwVZksdEm', 'Administrator Atmapotek', 'Jakarta, Indonesia', 'admin'),
  (2, 'pelanggan', 'pelanggan@atmapotek.local', '$2y$12$TVN8R3pt8YrpF7XYXgO65udhY.0n6exIOPDR8aJ4nFxukadSY4986', 'Pelanggan Demo', 'Yogyakarta, Indonesia', 'user');

INSERT IGNORE INTO medicines (id, name, description, price, stock, category, image) VALUES
  (1, 'Paracetamol 500 mg', 'Obat bebas untuk membantu meredakan demam dan nyeri ringan.', 8000.00, 48, 'Obat Bebas', '692b41d167481.jpeg'),
  (2, 'Vitamin C 1000 mg', 'Suplemen vitamin C untuk membantu memenuhi kebutuhan harian.', 35000.00, 30, 'Suplemen', '6936e777abf26.jpg'),
  (3, 'Minyak Kayu Putih 60 ml', 'Minyak oles untuk memberikan rasa hangat dan nyaman.', 18000.00, 24, 'Obat Herbal', NULL),
  (4, 'Oralit', 'Larutan oralit untuk membantu menggantikan cairan tubuh.', 5000.00, 60, 'Obat Bebas', NULL);

INSERT IGNORE INTO orders (id, user_id, order_date, total_price, payment_method, status) VALUES
  (1, 2, '2026-07-01 10:00:00', 43000.00, 'Transfer Bank', 'completed');

INSERT IGNORE INTO order_details (id, order_id, medicine_id, quantity, price_at_purchase) VALUES
  (1, 1, 1, 1, 8000.00),
  (2, 1, 2, 1, 35000.00);
