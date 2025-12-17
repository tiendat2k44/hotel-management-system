-- ============================================
-- Hotel Management System - Database Setup
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS hotel_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_management_db;

-- ============================================
-- 1. Bảng người dùng (Users)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name NVARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- ============================================
-- 2. Bảng khách hàng (Customers)
-- ============================================
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    id_card VARCHAR(20),
    passport VARCHAR(20),
    address NVARCHAR(255),
    date_of_birth DATE,
    nationality NVARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_id_card (id_card),
    INDEX idx_passport (passport)
);

-- ============================================
-- 3. Bảng loại phòng (Room Types)
-- ============================================
CREATE TABLE IF NOT EXISTS room_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_name NVARCHAR(50) NOT NULL,
    description TEXT,
    base_price DECIMAL(12,2) NOT NULL,
    capacity INT DEFAULT 2,
    amenities TEXT,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_name (type_name),
    INDEX idx_status (status)
);

-- ============================================
-- 4. Bảng phòng (Rooms)
-- ============================================
CREATE TABLE IF NOT EXISTS rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(10) UNIQUE NOT NULL,
    room_type_id INT NOT NULL,
    floor INT,
    status ENUM('available', 'occupied', 'cleaning', 'maintenance') DEFAULT 'available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id),
    INDEX idx_room_number (room_number),
    INDEX idx_status (status),
    INDEX idx_floor (floor)
);

-- ============================================
-- 5. Bảng đặt phòng (Bookings)
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_code VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    actual_check_in DATETIME NULL,
    actual_check_out DATETIME NULL,
    adults INT DEFAULT 1,
    children INT DEFAULT 0,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(12,2),
    deposit_amount DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_booking_code (booking_code),
    INDEX idx_status (status),
    INDEX idx_check_in (check_in),
    INDEX idx_check_out (check_out),
    INDEX idx_customer_id (customer_id)
);

-- ============================================
-- 6. Bảng dịch vụ (Services)
-- ============================================
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_name NVARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit NVARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_name (service_name),
    INDEX idx_status (status)
);

-- ============================================
-- 7. Bảng sử dụng dịch vụ (Service Usage)
-- ============================================
CREATE TABLE IF NOT EXISTS service_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    usage_date DATE NOT NULL,
    total_price DECIMAL(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id),
    INDEX idx_booking_id (booking_id),
    INDEX idx_service_id (service_id),
    INDEX idx_usage_date (usage_date)
);

-- ============================================
-- 8. Bảng thanh toán (Payments)
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_code VARCHAR(20) UNIQUE NOT NULL,
    booking_id INT NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card') DEFAULT 'cash',
    payment_type ENUM('deposit', 'final', 'refund') NOT NULL,
    notes TEXT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    processed_by INT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (processed_by) REFERENCES users(id),
    INDEX idx_payment_code (payment_code),
    INDEX idx_booking_id (booking_id),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
);

-- ============================================
-- 9. Bảng hóa đơn (Invoices)
-- ============================================
CREATE TABLE IF NOT EXISTS invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_code VARCHAR(20) UNIQUE NOT NULL,
    booking_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(12,2),
    tax_rate DECIMAL(5,2) DEFAULT 10,
    tax_amount DECIMAL(12,2),
    total_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    balance DECIMAL(12,2),
    status ENUM('draft', 'issued', 'paid', 'overdue') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    INDEX idx_invoice_code (invoice_code),
    INDEX idx_status (status),
    INDEX idx_booking_id (booking_id),
    INDEX idx_issue_date (issue_date)
);

-- ============================================
-- Dữ liệu mẫu
-- ============================================

-- Admin user
INSERT INTO users (username, password, email, full_name, phone, role, status) 
VALUES ('admin', '$2y$12$8/6rX9vFT8W5VgkQHYZkeO8z3wVwBXcVxMqb8DY1G8H7oH0kQcjyu', 
        'admin@hotel.com', 'Admin', '0123456789', 'admin', 'active');

-- Staff user
INSERT INTO users (username, password, email, full_name, phone, role, status) 
VALUES ('staff1', '$2y$12$8/6rX9vFT8W5VgkQHYZkeO8z3wVwBXcVxMqb8DY1G8H7oH0kQcjyu', 
        'staff@hotel.com', 'Nhân viên 1', '0987654321', 'staff', 'active');

-- Customer user
INSERT INTO users (username, password, email, full_name, phone, role, status) 
VALUES ('customer1', '$2y$12$8/6rX9vFT8W5VgkQHYZkeO8z3wVwBXcVxMqb8DY1G8H7oH0kQcjyu', 
        'customer@email.com', 'Nguyễn Văn A', '0912345678', 'customer', 'active');

-- Room types
INSERT INTO room_types (type_name, description, base_price, capacity, amenities) VALUES
('Standard', 'Phòng tiêu chuẩn với tiện ích cơ bản', 500000, 2, 'Giường đơn/đôi, Phòng tắm, TV'),
('Deluxe', 'Phòng sang trọng với tầm nhìn đẹp', 800000, 2, 'Giường King, Ban công, TV 4K, Minibar'),
('Suite', 'Phòng cao cấp với phòng khách riêng', 1500000, 4, 'Phòng ngủ riêng, Phòng khách, Bếp, Jacuzzi');

-- Rooms
INSERT INTO rooms (room_number, room_type_id, floor) VALUES
('101', 1, 1), ('102', 1, 1), ('103', 2, 1),
('201', 1, 2), ('202', 2, 2), ('203', 3, 2),
('301', 2, 3), ('302', 2, 3), ('303', 3, 3);

-- Services
INSERT INTO services (service_name, description, price, unit) VALUES
('Đặc vụ ăn sáng', 'Bữa ăn sáng buffet', 100000, 'buổi'),
('Dịch vụ giặt ủi', 'Giặt ủi quần áo', 50000, 'bộ'),
('Dịch vụ spa', 'Massage toàn thân', 300000, 'buổi'),
('Dịch vụ đưa đón sân bay', 'Đưa/đón sân bay', 200000, 'lần'),
('Internet nhanh', 'Wifi tốc độ cao', 50000, 'ngày');

-- Customer detail
INSERT INTO customers (user_id, id_card, address, date_of_birth, nationality, notes)
VALUES (3, '001201234567', 'Hà Nội, Việt Nam', '1990-01-15', 'Việt Nam', 'Khách hàng thường xuyên');

?>
