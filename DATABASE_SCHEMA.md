# Database Schema - Hotel Management System

## Tổng Quan

Cơ sở dữ liệu gồm **9 bảng chính** được thiết kế với mối quan hệ bình thường hóa (normalized) để tối ưu hóa dữ liệu và hiệu suất.

- **Character Set:** utf8mb4 (hỗ trợ tiếng Việt & emoji)
- **Collation:** utf8mb4_unicode_ci
- **Storage Engine:** InnoDB (hỗ trợ foreign keys)

---

## 1. Bảng USERS (Người Dùng)

Lưu trữ thông tin tài khoản người dùng gồm Admin, Staff, Customer

### Cấu Trúc

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    last_login DATETIME,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### Các Trường (Fields)

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key, tự động tăng |
| username | VARCHAR(50) | No | Tên đăng nhập, unique |
| email | VARCHAR(100) | No | Email, unique |
| password | VARCHAR(255) | No | Mật khẩu được hash (BCRYPT) |
| role | ENUM | No | Vai trò: admin, staff, customer |
| full_name | VARCHAR(100) | No | Tên đầy đủ |
| phone | VARCHAR(20) | Yes | Số điện thoại |
| avatar | VARCHAR(255) | Yes | Đường dẫn ảnh đại diện |
| last_login | DATETIME | Yes | Lần đăng nhập gần nhất |
| is_active | TINYINT | No | Trạng thái (1=active, 0=inactive) |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Indexes
```sql
INDEX idx_username (username)
INDEX idx_email (email)
INDEX idx_role (role)
```

### Sample Data
```sql
INSERT INTO users VALUES
(1, 'admin', 'admin@hotel.local', '$2y$12$...', 'admin', 'Quản Trị Viên', '0901234567', NULL, NOW(), 1, NOW(), NOW()),
(2, 'staff1', 'staff@hotel.local', '$2y$12$...', 'staff', 'Nhân Viên 1', '0902345678', NULL, NOW(), 1, NOW(), NOW()),
(3, 'customer1', 'customer@email.com', '$2y$12$...', 'customer', 'Nguyễn Văn A', '0903456789', NULL, NOW(), 1, NOW(), NOW());
```

---

## 2. Bảng CUSTOMERS (Khách Hàng)

Lưu trữ thông tin chi tiết khách hàng (mở rộng từ USERS)

### Cấu Trúc

```sql
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    identity_number VARCHAR(20),
    identity_type ENUM('national_id', 'passport', 'driving_license') DEFAULT 'national_id',
    date_of_birth DATE,
    address VARCHAR(255),
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Vietnam',
    total_spent DECIMAL(15,2) DEFAULT 0,
    number_of_bookings INT DEFAULT 0,
    is_vip TINYINT DEFAULT 0,
    loyalty_points INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| user_id | INT | No | Foreign Key → users.id |
| identity_number | VARCHAR(20) | Yes | Số CMND/Hộ chiếu/GPLX |
| identity_type | ENUM | No | Loại giấy tờ |
| date_of_birth | DATE | Yes | Ngày sinh |
| address | VARCHAR(255) | Yes | Địa chỉ |
| city | VARCHAR(100) | Yes | Thành phố |
| country | VARCHAR(100) | No | Quốc gia (mặc định: Vietnam) |
| total_spent | DECIMAL(15,2) | No | Tổng tiêu xài |
| number_of_bookings | INT | No | Số lần đặt phòng |
| is_vip | TINYINT | No | Là VIP hay không |
| loyalty_points | INT | No | Điểm tích lũy |
| notes | TEXT | Yes | Ghi chú |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Indexes
```sql
UNIQUE INDEX idx_user_id (user_id)
INDEX idx_identity_number (identity_number)
INDEX idx_city (city)
```

---

## 3. Bảng ROOM_TYPES (Loại Phòng)

Lưu trữ các loại phòng và thông tin tương ứng

### Cấu Trúc

```sql
CREATE TABLE room_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_guests INT DEFAULT 2,
    amenities TEXT,
    image_url VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| name | VARCHAR(100) | No | Tên loại phòng, unique |
| description | TEXT | Yes | Mô tả chi tiết |
| price_per_night | DECIMAL(10,2) | No | Giá mỗi đêm |
| max_guests | INT | No | Số khách tối đa |
| amenities | TEXT | Yes | Tiện nghi (JSON hoặc dạng text) |
| image_url | VARCHAR(255) | Yes | Đường dẫn hình ảnh |
| is_active | TINYINT | No | Trạng thái (1=active, 0=inactive) |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Indexes
```sql
UNIQUE INDEX idx_name (name)
```

### Sample Data
```sql
INSERT INTO room_types VALUES
(1, 'Standard', 'Phòng tiêu chuẩn', 300000, 2, 'Wi-Fi, TV, AC', NULL, 1, NOW(), NOW()),
(2, 'Deluxe', 'Phòng sang trọng', 500000, 2, 'Wi-Fi, TV, AC, Minibar', NULL, 1, NOW(), NOW()),
(3, 'Suite', 'Phòng cao cấp', 1000000, 4, 'Wi-Fi, TV, AC, Minibar, Spa bath', NULL, 1, NOW(), NOW());
```

---

## 4. Bảng ROOMS (Phòng)

Lưu trữ thông tin từng phòng cụ thể

### Cấu Trúc

```sql
CREATE TABLE rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(20) UNIQUE NOT NULL,
    room_type_id INT NOT NULL,
    floor INT NOT NULL,
    status ENUM('available', 'occupied', 'cleaning', 'maintenance') DEFAULT 'available',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| room_number | VARCHAR(20) | No | Số phòng, unique (VD: 101, 102, ...) |
| room_type_id | INT | No | Foreign Key → room_types.id |
| floor | INT | No | Tầng |
| status | ENUM | No | Trạng thái phòng |
| notes | TEXT | Yes | Ghi chú |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Trạng Thái Phòng

| Status | Mô Tả |
|--------|-------|
| available | Trống, sẵn sàng đặt |
| occupied | Có khách đang ở |
| cleaning | Đang được dọn dẹp |
| maintenance | Bảo trì, không thể đặt |

### Indexes
```sql
UNIQUE INDEX idx_room_number (room_number)
INDEX idx_room_type_id (room_type_id)
INDEX idx_status (status)
```

### Sample Data
```sql
INSERT INTO rooms VALUES
(1, '101', 1, 1, 'available', NULL, NOW(), NOW()),
(2, '102', 1, 1, 'available', NULL, NOW(), NOW()),
(3, '201', 2, 2, 'available', NULL, NOW(), NOW()),
(4, '202', 2, 2, 'occupied', NULL, NOW(), NOW()),
(5, '301', 3, 3, 'available', NULL, NOW(), NOW());
```

---

## 5. Bảng BOOKINGS (Đặt Phòng)

Lưu trữ thông tin đặt phòng

### Cấu Trúc

```sql
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    check_in_time TIME DEFAULT '14:00:00',
    check_out_time TIME DEFAULT '12:00:00',
    adults INT DEFAULT 1,
    children INT DEFAULT 0,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    special_requests TEXT,
    deposit_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| code | VARCHAR(50) | No | Mã booking, unique (VD: BK202501150001) |
| customer_id | INT | No | Foreign Key → customers.id |
| room_id | INT | No | Foreign Key → rooms.id |
| check_in_date | DATE | No | Ngày nhận phòng |
| check_out_date | DATE | No | Ngày trả phòng |
| check_in_time | TIME | No | Giờ nhận phòng (mặc định: 14:00) |
| check_out_time | TIME | No | Giờ trả phòng (mặc định: 12:00) |
| adults | INT | No | Số người lớn |
| children | INT | No | Số trẻ em |
| status | ENUM | No | Trạng thái booking |
| special_requests | TEXT | Yes | Yêu cầu đặc biệt |
| deposit_amount | DECIMAL(15,2) | No | Tiền cọc |
| notes | TEXT | Yes | Ghi chú |
| created_by | INT | Yes | Người tạo (staff/admin ID) |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Trạng Thái Booking

| Status | Mô Tả |
|--------|-------|
| pending | Chờ xác nhận |
| confirmed | Đã xác nhận |
| checked_in | Khách đã nhận phòng |
| checked_out | Khách đã trả phòng |
| cancelled | Đã hủy |

### Indexes
```sql
UNIQUE INDEX idx_code (code)
INDEX idx_customer_id (customer_id)
INDEX idx_room_id (room_id)
INDEX idx_check_in_date (check_in_date)
INDEX idx_check_out_date (check_out_date)
INDEX idx_status (status)
```

---

## 6. Bảng SERVICES (Dịch Vụ)

Lưu trữ các dịch vụ có thể sử dụng

### Cấu Trúc

```sql
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    category VARCHAR(50),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| name | VARCHAR(100) | No | Tên dịch vụ, unique |
| description | TEXT | Yes | Mô tả |
| price | DECIMAL(10,2) | No | Giá dịch vụ |
| unit | VARCHAR(50) | No | Đơn vị (lần, phần, ngày, ...) |
| category | VARCHAR(50) | Yes | Phân loại (ăn uống, spa, ...) |
| is_active | TINYINT | No | Trạng thái |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Sample Data
```sql
INSERT INTO services VALUES
(1, 'Breakfast', 'Bữa sáng buffet', 150000, 'phần', 'dining', 1, NOW(), NOW()),
(2, 'Lunch', 'Bữa trưa', 200000, 'phần', 'dining', 1, NOW(), NOW()),
(3, 'Massage', 'Dịch vụ massage', 300000, 'lần', 'spa', 1, NOW(), NOW()),
(4, 'Laundry', 'Giặt ủi quần áo', 50000, 'kg', 'laundry', 1, NOW(), NOW()),
(5, 'Airport Transfer', 'Đưa đón sân bay', 200000, 'lần', 'transport', 1, NOW(), NOW());
```

---

## 7. Bảng SERVICE_USAGE (Sử Dụng Dịch Vụ)

Lưu trữ dịch vụ được sử dụng trong từng booking

### Cấu Trúc

```sql
CREATE TABLE service_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(15,2) NOT NULL,
    usage_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| booking_id | INT | No | Foreign Key → bookings.id |
| service_id | INT | No | Foreign Key → services.id |
| quantity | INT | No | Số lượng sử dụng |
| unit_price | DECIMAL(10,2) | No | Giá mỗi đơn vị |
| total_price | DECIMAL(15,2) | No | Tổng giá (quantity × unit_price) |
| usage_date | DATE | Yes | Ngày sử dụng |
| notes | TEXT | Yes | Ghi chú |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Indexes
```sql
INDEX idx_booking_id (booking_id)
INDEX idx_service_id (service_id)
```

---

## 8. Bảng PAYMENTS (Thanh Toán)

Lưu trữ thông tin thanh toán

### Cấu Trúc

```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    booking_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'other') DEFAULT 'cash',
    payment_type ENUM('deposit', 'final', 'refund') DEFAULT 'deposit',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    reference_code VARCHAR(100),
    notes TEXT,
    processed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| code | VARCHAR(50) | No | Mã thanh toán, unique (VD: PM202501150001) |
| booking_id | INT | No | Foreign Key → bookings.id |
| amount | DECIMAL(15,2) | No | Số tiền |
| payment_method | ENUM | No | Phương thức thanh toán |
| payment_type | ENUM | No | Loại thanh toán |
| status | ENUM | No | Trạng thái thanh toán |
| reference_code | VARCHAR(100) | Yes | Mã tham chiếu (VD: mã GD ngân hàng) |
| notes | TEXT | Yes | Ghi chú |
| processed_by | INT | Yes | Người xử lý |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Phương Thức Thanh Toán

| Method | Mô Tả |
|--------|-------|
| cash | Tiền mặt |
| bank_transfer | Chuyển khoản ngân hàng |
| credit_card | Thẻ tín dụng |
| other | Khác |

### Loại Thanh Toán

| Type | Mô Tả |
|------|-------|
| deposit | Tiền cọc |
| final | Thanh toán cuối cùng |
| refund | Hoàn tiền |

### Indexes
```sql
UNIQUE INDEX idx_code (code)
INDEX idx_booking_id (booking_id)
INDEX idx_status (status)
```

---

## 9. Bảng INVOICES (Hóa Đơn)

Lưu trữ hóa đơn thanh toán

### Cấu Trúc

```sql
CREATE TABLE invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    booking_id INT NOT NULL,
    customer_id INT NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(15,2) NOT NULL,
    vat_rate DECIMAL(5,2) DEFAULT 10.00,
    vat_amount DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    paid_amount DECIMAL(15,2) DEFAULT 0,
    remaining_amount DECIMAL(15,2),
    status ENUM('draft', 'issued', 'paid', 'partially_paid', 'overdue', 'cancelled') DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

### Các Trường

| Trường | Kiểu | Nullable | Mô Tả |
|--------|------|----------|-------|
| id | INT | No | Primary Key |
| code | VARCHAR(50) | No | Mã hóa đơn, unique (VD: INV202501150001) |
| booking_id | INT | No | Foreign Key → bookings.id |
| customer_id | INT | No | Foreign Key → customers.id |
| invoice_date | DATE | No | Ngày lập hóa đơn |
| due_date | DATE | Yes | Ngày hạn thanh toán |
| subtotal | DECIMAL(15,2) | No | Tổng tiền trước VAT |
| vat_rate | DECIMAL(5,2) | No | Tỷ lệ VAT (%) - mặc định 10% |
| vat_amount | DECIMAL(15,2) | No | Số tiền VAT |
| total_amount | DECIMAL(15,2) | No | Tổng tiền (subtotal + VAT) |
| paid_amount | DECIMAL(15,2) | No | Số tiền đã trả |
| remaining_amount | DECIMAL(15,2) | Yes | Còn phải trả |
| status | ENUM | No | Trạng thái hóa đơn |
| notes | TEXT | Yes | Ghi chú |
| created_by | INT | Yes | Người tạo |
| created_at | TIMESTAMP | No | Thời gian tạo |
| updated_at | TIMESTAMP | No | Thời gian cập nhật |

### Trạng Thái Hóa Đơn

| Status | Mô Tả |
|--------|-------|
| draft | Bản nháp |
| issued | Đã phát hành |
| paid | Đã thanh toán |
| partially_paid | Thanh toán một phần |
| overdue | Quá hạn |
| cancelled | Đã hủy |

### Indexes
```sql
UNIQUE INDEX idx_code (code)
INDEX idx_booking_id (booking_id)
INDEX idx_customer_id (customer_id)
INDEX idx_status (status)
```

---

## Mối Quan Hệ (Relationships)

### Entity-Relationship Diagram

```
users (1) ──── (1) customers
                    │
                    │ (1)
                    ├─────→ bookings (n)
                    │            │
                    │            ├─→ rooms (1) ──→ room_types (1)
                    │            │
                    │            ├─→ payments (n)
                    │            │
                    │            ├─→ service_usage (n) ──→ services (1)
                    │            │
                    │            └─→ invoices (n)
                    │
                    └─ (1 hoặc nhiều)
```

### Mối Quan Hệ Chi Tiết

#### 1. users ↔ customers
- **1-to-1**: Mỗi customer có một user account
- **Foreign Key**: customers.user_id → users.id
- **Action**: ON DELETE CASCADE (xóa user → xóa customer)

#### 2. customers ↔ bookings
- **1-to-many**: Một khách hàng có nhiều booking
- **Foreign Key**: bookings.customer_id → customers.id
- **Action**: ON DELETE RESTRICT (không xóa customer nếu có booking)

#### 3. rooms ↔ bookings
- **1-to-many**: Một phòng có nhiều booking (nhưng không overlap về thời gian)
- **Foreign Key**: bookings.room_id → rooms.id
- **Action**: ON DELETE RESTRICT

#### 4. room_types ↔ rooms
- **1-to-many**: Một loại phòng có nhiều phòng
- **Foreign Key**: rooms.room_type_id → room_types.id
- **Action**: ON DELETE RESTRICT

#### 5. bookings ↔ service_usage
- **1-to-many**: Một booking có nhiều dịch vụ
- **Foreign Key**: service_usage.booking_id → bookings.id
- **Action**: ON DELETE CASCADE (xóa booking → xóa service_usage)

#### 6. services ↔ service_usage
- **1-to-many**: Một dịch vụ có nhiều lần sử dụng
- **Foreign Key**: service_usage.service_id → services.id
- **Action**: ON DELETE RESTRICT

#### 7. bookings ↔ payments
- **1-to-many**: Một booking có nhiều thanh toán
- **Foreign Key**: payments.booking_id → bookings.id
- **Action**: ON DELETE RESTRICT

#### 8. bookings ↔ invoices
- **1-to-many**: Một booking có một hoặc nhiều hóa đơn
- **Foreign Key**: invoices.booking_id → bookings.id
- **Action**: ON DELETE RESTRICT

---

## Constraints & Validations

### Primary Keys
- Tất cả bảng có PRIMARY KEY dạng INT AUTO_INCREMENT

### Foreign Keys
- Tất cả FK đều có constraints ON DELETE để đảm bảo data integrity

### Unique Constraints
- users.username, users.email
- customers.user_id
- room_types.name
- rooms.room_number
- bookings.code
- services.name
- payments.code
- invoices.code

### NOT NULL Fields
- Các trường quan trọng bắt buộc phải có giá trị

### ENUM Fields
- users.role: admin, staff, customer
- rooms.status: available, occupied, cleaning, maintenance
- bookings.status: pending, confirmed, checked_in, checked_out, cancelled
- services.category: dining, spa, transport, laundry, etc.
- payments.payment_method: cash, bank_transfer, credit_card, other
- payments.payment_type: deposit, final, refund
- payments.status: pending, completed, failed
- invoices.status: draft, issued, paid, partially_paid, overdue, cancelled

---

## Calculated Fields & Queries

### Tính Số Đêm Ở
```sql
SELECT booking_id, DATEDIFF(check_out_date, check_in_date) as nights
FROM bookings;
```

### Tính Tổng Tiền Phòng
```sql
SELECT b.id, 
       DATEDIFF(b.check_out_date, b.check_in_date) * rt.price_per_night as room_total
FROM bookings b
JOIN rooms r ON b.room_id = r.id
JOIN room_types rt ON r.room_type_id = rt.id;
```

### Tính Tổng Tiền Dịch Vụ
```sql
SELECT booking_id, SUM(total_price) as services_total
FROM service_usage
GROUP BY booking_id;
```

### Tính Tổng Tiền Booking (bao gồm VAT)
```sql
SELECT 
    b.id,
    DATEDIFF(b.check_out_date, b.check_in_date) * rt.price_per_night as room_total,
    COALESCE(su.services_total, 0) as services_total,
    DATEDIFF(b.check_out_date, b.check_in_date) * rt.price_per_night + 
    COALESCE(su.services_total, 0) as subtotal,
    (DATEDIFF(b.check_out_date, b.check_in_date) * rt.price_per_night + 
     COALESCE(su.services_total, 0)) * 0.1 as vat,
    (DATEDIFF(b.check_out_date, b.check_in_date) * rt.price_per_night + 
     COALESCE(su.services_total, 0)) * 1.1 as total
FROM bookings b
JOIN rooms r ON b.room_id = r.id
JOIN room_types rt ON r.room_type_id = rt.id
LEFT JOIN (
    SELECT booking_id, SUM(total_price) as services_total
    FROM service_usage
    GROUP BY booking_id
) su ON b.id = su.booking_id;
```

---

## Performance Optimization

### Indexes Strategy
- **Primary Keys**: Tất cả bảng
- **Foreign Keys**: Để accelerate joins
- **Status Fields**: Vì thường được filter
- **Date Fields**: Để accelerate date range queries
- **Unique Fields**: Tự động tạo unique index

### Query Optimization Tips
1. Luôn JOIN với room_types để lấy giá phòng
2. Sử dụng LEFT JOIN cho optional relationships (service_usage)
3. Sử dụng DATEDIFF() cho tính toán ngày
4. Index các trường search thường xuyên

---

**Phiên bản Schema:** 1.0.0  
**Cập nhật:** Tháng 12/2025
