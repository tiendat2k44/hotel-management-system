# Development Guide - Hotel Management System

## Mục Lục
1. [Thiết Lập Môi Trường Phát Triển](#thiết-lập-môi-trường-phát-triển)
2. [Cấu Trúc Project](#cấu-trúc-project)
3. [Quy Chuẩn Lập Trình](#quy-chuẩn-lập-trình)
4. [Hướng Dẫn Tạo Module Mới](#hướng-dẫn-tạo-module-mới)
5. [Database Migrations](#database-migrations)
6. [Testing](#testing)
7. [Debugging](#debugging)
8. [Deployment](#deployment)

---

## Thiết Lập Môi Trường Phát Triển

### Yêu Cầu
- PHP 7.4+ (khuyến nghị 8.0+)
- MySQL 5.7+ hoặc MariaDB 10.3+
- Apache hoặc Nginx với mod_rewrite
- Composer (tùy chọn, cho autoloader)
- Git (version control)

### Cài Đặt Local
```bash
# Clone repository
git clone <your-repo-url> hotel-management-system
cd hotel-management-system

# Cấp quyền cho folders
chmod 755 ./
chmod 755 ./assets/uploads/
chmod 755 ./logs/ (nếu có)

# Tạo database
mysql -u root -p < database.sql

# Copy config và cấu hình
cp config/database.php.example config/database.php
# Chỉnh sửa config/database.php với thông tin database của bạn
```

### Cấu Hình Apache
```apache
<Directory /var/www/html/hotel-management-system>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
    
    # Enable mod_rewrite
    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteBase /hotel-management-system/
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php?/$1 [QSA,L]
    </IfModule>
</Directory>
```

### Virtual Host (tùy chọn)
```apache
<VirtualHost *:80>
    ServerName hotel.local
    ServerAlias www.hotel.local
    DocumentRoot /var/www/html/hotel-management-system
    
    <Directory /var/www/html/hotel-management-system>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/hotel-error.log
    CustomLog ${APACHE_LOG_DIR}/hotel-access.log combined
</VirtualHost>
```

---

## Cấu Trúc Project

```
hotel-management-system/
├── assets/                      # Static assets
│   ├── css/
│   │   ├── style.css           # Main stylesheet
│   │   ├── dashboard.css       # Dashboard specific
│   │   └── responsive.css      # Responsive design
│   ├── js/
│   │   ├── main.js             # Core functionality
│   │   └── booking.js          # Booking module
│   ├── images/                 # Logo, icons, etc
│   └── uploads/                # User uploaded files
├── config/                      # Configuration files
│   ├── database.php            # Database connection
│   ├── constants.php           # Application constants
│   └── config.example.php      # Config template
├── includes/                    # Shared includes
│   ├── header.php              # HTML header & navbar
│   ├── footer.php              # HTML footer
│   ├── navigation.php          # Role-based navigation
│   ├── functions.php           # Utility functions
│   └── auth_check.php          # Session check
├── modules/                     # Feature modules
│   ├── auth/                   # Authentication
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── logout.php
│   │   └── profile.php
│   ├── admin/                  # Admin panel
│   │   ├── dashboard.php
│   │   ├── rooms/
│   │   │   ├── index.php
│   │   │   ├── add.php
│   │   │   ├── edit.php
│   │   │   └── delete.php
│   │   ├── bookings/
│   │   ├── services/
│   │   └── reports/ (đang phát triển)
│   ├── staff/                  # Staff panel (đang phát triển)
│   └── customer/               # Customer panel
│       ├── dashboard.php
│       └── booking_history.php
├── api/                         # API endpoints
│   ├── check_room_availability.php
│   ├── get_room_price.php
│   └── ... (khác)
├── classes/                     # PHP Classes (trong tương lai)
│   ├── Database.php
│   ├── User.php
│   ├── Booking.php
│   └── ... (khác)
├── logs/                        # Application logs
├── .htaccess                    # Apache configuration
├── index.php                    # Homepage
├── database.sql                 # Database schema
├── README.md                    # Project readme
├── INSTALLATION.md              # Installation guide
├── FEATURES.md                  # Features list
├── API_DOCUMENTATION.md         # API docs
├── DATABASE_SCHEMA.md           # Schema docs
└── DEVELOPMENT_GUIDE.md         # This file

```

---

## Quy Chuẩn Lập Trình

### PHP Code Style

#### Naming Conventions
```php
// Classes (PascalCase)
class UserManager { }

// Functions (snake_case)
function get_user_by_id($id) { }

// Variables (camelCase)
$userName = "John";

// Constants (UPPER_SNAKE_CASE)
const DATABASE_HOST = "localhost";
define('APP_VERSION', '1.0.0');

// Booleans (is/has prefix)
$isActive = true;
$hasPermission = false;
```

#### File Organization
```php
<?php
/**
 * File: modules/admin/rooms/add.php
 * Description: Thêm phòng mới
 * Created: 2025-01-15
 * Author: Developer Name
 */

// 1. Security check
require_once '../../../includes/auth_check.php';
require_once '../../../config/constants.php';
require_once '../../../includes/functions.php';

// 2. Require permissions
requireRole(['admin']);

// 3. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process logic
}

// 4. Include template
require_once '../../../includes/header.php';
?>

<!-- HTML content -->

<?php
require_once '../../../includes/footer.php';
?>
```

#### Security Best Practices
```php
// Always escape output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// Use prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = ?");
$stmt->execute([$id, $role]);

// Validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception("Invalid email");
}

// Hash passwords
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Use CSRF tokens
if (!validateCsrf($_POST['csrf_token'])) {
    throw new Exception("CSRF token invalid");
}

// Check permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}
```

#### Comments
```php
/**
 * Calculate room total including VAT
 *
 * @param int $room_id Room ID
 * @param string $check_in Check-in date (YYYY-MM-DD)
 * @param string $check_out Check-out date (YYYY-MM-DD)
 * @return array ['subtotal' => X, 'vat' => Y, 'total' => Z]
 * @throws Exception If dates invalid
 */
function calculateRoomTotal($room_id, $check_in, $check_out) {
    // Implementation
}

// Bad practice - don't do this
$x = get_data(); // Get data
```

---

## Hướng Dẫn Tạo Module Mới

### Ví Dụ: Tạo Module Quản Lý Nhân Viên

#### Step 1: Tạo Folder Structure
```bash
mkdir -p modules/admin/staff
```

#### Step 2: Tạo File Index (List)
```php
<?php
/**
 * File: modules/admin/staff/index.php
 * Description: Danh sách nhân viên
 */

require_once '../../../includes/auth_check.php';
require_once '../../../includes/functions.php';
requireRole(['admin']);

// Get list
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$per_page = 10;

try {
    $offset = ($page - 1) * $per_page;
    
    // Build query
    $query = "SELECT u.id, u.username, u.email, u.full_name, u.phone, 
                     u.is_active, u.created_at 
              FROM users u 
              WHERE u.role = 'staff'";
    $params = [];
    
    if ($search) {
        $query .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param];
    }
    
    $query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count
    $count_query = "SELECT COUNT(*) FROM users WHERE role = 'staff'";
    if ($search) {
        $count_query .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    }
    $count_stmt = $conn->prepare($count_query);
    $search ? $count_stmt->execute([$search_param, $search_param, $search_param]) 
            : $count_stmt->execute();
    $total = $count_stmt->fetchColumn();
    $total_pages = ceil($total / $per_page);
    
} catch (Exception $e) {
    logActivity('staff_list_error', 'Error: ' . $e->getMessage(), $_SESSION['user_id']);
    $_SESSION['flash_message'] = "Lỗi: " . $e->getMessage();
    $_SESSION['flash_type'] = 'danger';
}

require_once '../../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Quản Lý Nhân Viên</h2>
        </div>
        <div class="col-md-4">
            <a href="add.php" class="btn btn-primary float-end">+ Thêm Nhân Viên</a>
        </div>
    </div>
    
    <!-- Search form -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" 
                       placeholder="Tìm theo tên hoặc email" value="<?php echo $search; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary w-100">Tìm Kiếm</button>
            </div>
        </div>
    </form>
    
    <!-- Staff table -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Tên Đăng Nhập</th>
                    <th>Email</th>
                    <th>Tên Đầy Đủ</th>
                    <th>Điện Thoại</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Tạo</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['username']); ?></td>
                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                    <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($member['phone'] ?? '-'); ?></td>
                    <td>
                        <?php if ($member['is_active']): ?>
                            <span class="badge bg-success">Hoạt Động</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Vô Hiệu</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo formatDate($member['created_at']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $member['id']; ?>" 
                           class="btn btn-sm btn-warning">Sửa</a>
                        <a href="delete.php?id=<?php echo $member['id']; ?>" 
                           class="btn btn-sm btn-danger" onclick="return confirm('Bạn chắc chắn?')">Xóa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php require_once '../../../includes/footer.php'; ?>
```

#### Step 3: Tạo File Add (Create)
```php
<?php
/**
 * File: modules/admin/staff/add.php
 * Description: Thêm nhân viên mới
 */

require_once '../../../includes/auth_check.php';
require_once '../../../includes/functions.php';
requireRole(['admin']);

$errors = [];
$data = ['username' => '', 'email' => '', 'full_name' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? ''
    ];
    
    if (empty($data['username'])) $errors[] = "Username không được để trống";
    if (empty($data['email'])) $errors[] = "Email không được để trống";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ";
    if (empty($data['full_name'])) $errors[] = "Tên không được để trống";
    if (empty($data['password'])) $errors[] = "Mật khẩu không được để trống";
    if (strlen($data['password']) < 6) $errors[] = "Mật khẩu ít nhất 6 ký tự";
    
    if (empty($errors)) {
        try {
            // Check duplicate
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$data['username'], $data['email']]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username hoặc Email đã tồn tại";
            } else {
                // Insert
                $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $conn->prepare(
                    "INSERT INTO users (username, email, password, role, full_name, phone, is_active) 
                     VALUES (?, ?, ?, 'staff', ?, ?, 1)"
                );
                $stmt->execute([$data['username'], $data['email'], $hashed_password, $data['full_name'], $data['phone']]);
                
                logActivity('staff_added', "Thêm nhân viên: {$data['username']}", $_SESSION['user_id']);
                $_SESSION['flash_message'] = "Thêm nhân viên thành công!";
                $_SESSION['flash_type'] = 'success';
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

require_once '../../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h2>Thêm Nhân Viên</h2>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                <div>• <?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Tên Đăng Nhập</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($data['username']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($data['email']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Tên Đầy Đủ</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($data['full_name']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Điện Thoại</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($data['phone']); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Mật Khẩu</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100">Thêm</button>
                    </div>
                    <div class="col-md-6">
                        <a href="index.php" class="btn btn-secondary w-100">Hủy</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>
```

---

## Database Migrations

### Tạo Migration Script

```php
<?php
/**
 * File: migrations/001_create_tables.php
 * Description: Tạo cấu trúc bảng
 */

class Migration001 {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function up() {
        // Thực thi SQL UP
        $sql = file_get_contents(__DIR__ . '/../database.sql');
        $this->conn->exec($sql);
    }
    
    public function down() {
        // Thực thi SQL DOWN để rollback
        $sqls = [
            "DROP TABLE IF EXISTS invoices",
            "DROP TABLE IF EXISTS payments",
            "DROP TABLE IF EXISTS service_usage",
            "DROP TABLE IF EXISTS services",
            "DROP TABLE IF EXISTS bookings",
            "DROP TABLE IF EXISTS rooms",
            "DROP TABLE IF EXISTS room_types",
            "DROP TABLE IF EXISTS customers",
            "DROP TABLE IF EXISTS users"
        ];
        
        foreach ($sqls as $sql) {
            $this->conn->exec($sql);
        }
    }
}
```

### Chạy Migrations
```php
<?php
// migrations.php - Script chạy migrations

require_once 'config/database.php';

$migration = new Migration001($conn);
$migration->up(); // Hoặc $migration->down();
```

---

## Testing

### Unit Testing với PHPUnit

```php
<?php
/**
 * File: tests/BookingTest.php
 */

use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase {
    private $db;
    
    protected function setUp(): void {
        // Setup test database
        $this->db = new PDO('mysql:host=localhost;dbname=hotel_test', 'root', '');
    }
    
    public function testCalculateNights() {
        $nights = calculateNights('2025-01-15', '2025-01-18');
        $this->assertEquals(3, $nights);
    }
    
    public function testIsRoomAvailable() {
        $available = isRoomAvailable(1, '2025-01-15', '2025-01-18', $this->db);
        $this->assertTrue(is_bool($available));
    }
}
```

### Chạy Tests
```bash
vendor/bin/phpunit tests/
```

---

## Debugging

### Enable Debug Mode
```php
<?php
// config/constants.php

define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
```

### Logging
```php
<?php
function debugLog($message, $data = []) {
    if (DEBUG_MODE) {
        $log = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($data)) {
            $log .= ' - ' . json_encode($data);
        }
        error_log($log, 3, __DIR__ . '/../logs/debug.log');
    }
}

// Usage
debugLog('User login attempt', ['username' => 'john']);
```

### Browser DevTools
```javascript
// assets/js/debug.js
console.log('Debug info:', {
    user: <?php echo json_encode($_SESSION['username'] ?? null); ?>,
    timestamp: new Date(),
    page: window.location.href
});
```

---

## Deployment

### Pre-Deployment Checklist
- [ ] Database backup created
- [ ] Code tested locally
- [ ] All dependencies installed
- [ ] Environment variables configured
- [ ] Debug mode disabled
- [ ] File permissions set correctly
- [ ] HTTPS configured
- [ ] Backups scheduled

### Production Deployment Steps

```bash
# 1. SSH to server
ssh user@server.com

# 2. Navigate to project directory
cd /var/www/hotel-management-system

# 3. Pull latest code
git pull origin main

# 4. Install/update dependencies (if using composer)
composer install --no-dev --optimize-autoloader

# 5. Set permissions
chmod 755 ./
chmod 755 ./assets/uploads/
chmod 755 ./logs/

# 6. Backup database
mysqldump -u user -p hotel_management_db > backup_$(date +%Y%m%d_%H%M%S).sql

# 7. Run migrations (if any)
php migrations.php

# 8. Clear cache (if implemented)
rm -rf cache/*

# 9. Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql

# 10. Verify deployment
curl https://yourdomain.com/
```

### .env File (Production)
```env
# config/.env
DB_HOST=localhost
DB_USER=hotel_prod_user
DB_PASS=secure_password_here
DB_NAME=hotel_production

APP_ENV=production
APP_DEBUG=false
SESSION_TIMEOUT=1800

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_FROM=noreply@yourdomain.com
MAIL_PASSWORD=your_app_password
```

### Monitoring & Maintenance

```bash
# Check disk usage
df -h

# Check database size
mysql -u user -p -e "SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'MB' FROM information_schema.tables WHERE table_schema = 'hotel_management_db';"

# Check log files
tail -f logs/error.log
tail -f logs/activity.log

# Database optimization
mysql -u user -p hotel_management_db -e "OPTIMIZE TABLE users, customers, bookings, rooms, services, payments, invoices;"
```

---

## Kontribusi

### Git Workflow

```bash
# 1. Create feature branch
git checkout -b feature/add-new-feature

# 2. Make changes and commit
git add .
git commit -m "Add: Describe your changes clearly"

# 3. Push to remote
git push origin feature/add-new-feature

# 4. Create Pull Request
# Go to GitHub/GitLab and create PR with description

# 5. After review and approval, merge
git checkout main
git merge feature/add-new-feature
git push origin main
```

### Commit Message Format
```
<type>: <subject>

<body>

Fixes #<issue-number>
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

---

**Version:** 1.0.0  
**Last Updated:** December 2025
