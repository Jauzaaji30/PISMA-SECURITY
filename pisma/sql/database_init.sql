-- Create and use database
CREATE DATABASE IF NOT EXISTS dig_security;
USE dig_security;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(150) NOT NULL,
    role ENUM('admin', 'security') NOT NULL DEFAULT 'security',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: guests
CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    identity_number VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NULL,
    institution VARCHAR(150) NULL,
    vehicle_number VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: visits
CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT NOT NULL,
    purpose TEXT NOT NULL,
    destination VARCHAR(150) NOT NULL,
    check_in_time DATETIME NULL,
    status ENUM('waiting', 'checked_in', 'completed') DEFAULT 'waiting',
    qr_code VARCHAR(100) NOT NULL UNIQUE,
    security_in_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE CASCADE,
    FOREIGN KEY (security_in_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table: blacklist
CREATE TABLE IF NOT EXISTS blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identity_number VARCHAR(100) NOT NULL,
    reason TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: security_logs
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50),
    guest_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (guest_id) REFERENCES guests(id) ON DELETE SET NULL
);

-- Insert Default Admin & Security (Password matches 'rahasia' exactly through password_hash)
INSERT INTO users (username, password, fullname, role, is_active) VALUES 
('admin', '$2y$10$w09ZtoT.FvVStyT0mN/UgeC1D5S5m3R./y.o90w0L3G/z6Kx6H5e.', 'Administrator Utama', 'admin', 1)
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO users (username, password, fullname, role, is_active) VALUES 
('security', '$2y$10$w09ZtoT.FvVStyT0mN/UgeC1D5S5m3R./y.o90w0L3G/z6Kx6H5e.', 'Petugas Security 1', 'security', 1)
ON DUPLICATE KEY UPDATE id=id;
