# Hướng Dẫn Cài Đặt - Hotel Management System

## Yêu Cầu Hệ Thống

- **PHP:** 7.4 hoặc cao hơn
- **MySQL:** 5.7 hoặc cao hơn
- **Web Server:** Apache hoặc Nginx (khuyến nghị Apache)
- **Trình duyệt:** Chrome, Firefox, Safari, Edge (phiên bản mới)

## Bước 1: Chuẩn Bị Môi Trường

### Nếu dùng XAMPP (Windows)
1. Tải XAMPP từ https://www.apachefriends.org/
2. Cài đặt XAMPP
3. Mở XAMPP Control Panel
4. Khởi động Apache và MySQL

### Nếu dùng WAMP (Windows)
1. Tải WAMP từ http://www.wampserver.com/
2. Cài đặt và khởi động WAMP

### Nếu dùng LAMP (Linux)
```bash
sudo apt-get update
sudo apt-get install apache2 mysql-server php libapache2-mod-php php-mysql
sudo systemctl start apache2
sudo systemctl start mysql
```

## Bước 2: Tải Code

### Option 1: Sử dụng Git
```bash
cd /path/to/htdocs  # Hoặc www folder
git clone https://github.com/yourusername/hotel-management-system.git
cd hotel-management-system
```

### Option 2: Tải ZIP và giải nén
1. Tải file ZIP từ GitHub
2. Giải nén vào thư mục htdocs (XAMPP) hoặc www (WAMP)

## Bước 3: Tạo Database

### Phương pháp 1: Sử dụng phpMyAdmin
1. Mở http://localhost/phpmyadmin
2. Đăng nhập với username `root` (không có password hoặc password của bạn)
3. Nhấp "New" để tạo database mới
4. Nhập tên database: `hotel_management_db`
5. Nhấp "Create"
6. Chọn database vừa tạo
7. Nhấp tab "Import"
8. Chọn file `database.sql` từ thư mục project
9. Nhấp "Go" để import

### Phương pháp 2: Sử dụng Command Line
```bash
# Mở MySQL CLI
mysql -u root -p

# Hoặc nếu không có password
mysql -u root

# Chạy lệnh SQL
CREATE DATABASE hotel_management_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_management_db;
SOURCE /path/to/database.sql;
```

## Bước 4: Cấu Hình Database Connection

1. Mở file `config/database.php`
2. Kiểm tra và cập nhật các thông số:

```php
define('DB_HOST', 'localhost');      // Máy chủ MySQL
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL (nếu có)
define('DB_NAME', 'hotel_management_db'); // Tên database
```

3. Lưu file

## Bước 5: Cấu Hình File Permissions

### Trên Linux/Mac
```bash
# Cấp quyền ghi cho thư mục uploads
chmod 755 assets/uploads/
chmod 755 assets/images/
```

### Trên Windows
1. Nhấp chuột phải vào thư mục `assets/uploads`
2. Chọn "Properties"
3. Chọn tab "Security"
4. Nhấp "Edit" và cho phép ghi (Write)

## Bước 6: Truy Cập Ứng Dụng

1. Mở trình duyệt web
2. Truy cập: `http://localhost/hotel-management-system/`

Nếu hiện trang chủ có giao diện đẹp, bạn đã cài đặt thành công! ✅

## Bước 7: Đăng Nhập

Sử dụng tài khoản demo để test:

### Admin
- **URL:** http://localhost/hotel-management-system/modules/auth/login.php
- **Username:** admin
- **Password:** password

### Nhân viên
- **Username:** staff1
- **Password:** password

### Khách hàng
- **Username:** customer1
- **Password:** password

## Cấu Hình Bổ Sung

### 1. Cấu Hình BASE_URL
Nếu bạn đặt project ở vị trí khác, hãy cập nhật `BASE_URL` trong `config/constants.php`:

```php
define('BASE_URL', 'http://localhost/hotel-management-system/');
// Hoặc nếu là domain thực
define('BASE_URL', 'https://yourdomain.com/');
```

### 2. Cấu Hình Session
Nếu gặp lỗi session, kiểm tra `php.ini`:

```ini
session.save_path = "/tmp"  # Hoặc đường dẫn khác
session.gc_maxlifetime = 1440
```

### 3. Cấu Hình Upload File
Kiểm tra các thông số trong `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

## Kiểm Tra Cài Đặt

Tạo file `test.php` trong thư mục project:

```php
<?php
// Kiểm tra PHP version
echo "PHP Version: " . phpversion() . "<br>";

// Kiểm tra MySQL extension
if (extension_loaded('mysqli')) {
    echo "MySQL extension: OK<br>";
} else {
    echo "MySQL extension: FAILED<br>";
}

// Kiểm tra kết nối database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hotel_management_db', 'root', '');
    echo "Database Connection: OK<br>";
} catch (PDOException $e) {
    echo "Database Connection: FAILED - " . $e->getMessage() . "<br>";
}

// Kiểm tra quyền ghi
if (is_writable('assets/uploads')) {
    echo "Upload folder permission: OK<br>";
} else {
    echo "Upload folder permission: FAILED<br>";
}

echo "<br>Cài đặt hoàn tất!";
?>
```

Truy cập `http://localhost/hotel-management-system/test.php` để kiểm tra.

## Troubleshooting

### Lỗi: "Fatal error: Call to undefined function mysqli_connect()"
**Giải pháp:** Kích hoạt extension mysqli trong php.ini
```ini
extension=mysqli
extension=pdo_mysql
```

### Lỗi: "SQLSTATE[HY000]: General error: 2030"
**Giải pháp:** Kiểm tra quyền user MySQL, tạo user mới:
```sql
CREATE USER 'hoteluser'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON hotel_management_db.* TO 'hoteluser'@'localhost';
FLUSH PRIVILEGES;
```

### Lỗi: "Cannot write to upload folder"
**Giải pháp:** 
```bash
chmod 775 assets/uploads/
chmod 775 assets/images/
```

### Session không lưu
**Giải pháp:** Tạo thư mục session và cấp quyền:
```bash
mkdir -p /tmp/php-sessions
chmod 777 /tmp/php-sessions
```

Sau đó cập nhật php.ini:
```ini
session.save_path = "/tmp/php-sessions"
```

### Ứng dụng không hiển thị giao diện CSS/JS
**Giải pháp:** Cập nhật BASE_URL trong `config/constants.php` để đúng với cấu trúc URL của bạn

## Cài Đặt HTTPS (SSL/TLS)

### Tạo Certificate tự ký (cho development)
```bash
# Tạo private key
openssl genrsa -out server.key 2048

# Tạo certificate request
openssl req -new -key server.key -out server.csr

# Tạo certificate tự ký
openssl x509 -req -days 365 -in server.csr -signkey server.key -out server.crt
```

### Cấu hình Apache
Thêm vào httpd-ssl.conf:
```apache
SSLEngine on
SSLCertificateFile "path/to/server.crt"
SSLCertificateKeyFile "path/to/server.key"
```

## Bảo Mật Ban Đầu

Sau khi cài đặt, thực hiện các bước bảo mật:

1. **Đổi mật khẩu admin**
   - Đăng nhập với tài khoản admin
   - Truy cập hồ sơ cá nhân
   - Đổi mật khẩu

2. **Xóa file test.php**
   - Xóa file `test.php` đã tạo ở bước kiểm tra

3. **Cấu hình .htaccess**
   - File `.htaccess` đã có sẵn trong project
   - Đảm bảo mod_rewrite được kích hoạt trong Apache

4. **Cấp quyền file**
   - chmod 644 cho các file PHP
   - chmod 755 cho các thư mục

## Nâng Cấp Hệ Thống

### Cập nhật từ GitHub
```bash
cd hotel-management-system
git pull origin main
```

### Backup Database
```bash
mysqldump -u root -p hotel_management_db > backup_$(date +%Y%m%d).sql
```

### Phục hồi Database
```bash
mysql -u root -p hotel_management_db < backup_20231215.sql
```

## Support

Nếu gặp vấn đề:
1. Kiểm tra error_log của Apache: `logs/error.log`
2. Kiểm tra PHP error_log
3. Xem README.md để biết thêm chi tiết
4. Liên hệ qua email hỗ trợ

---

**Chúc bạn cài đặt thành công!** 🎉
