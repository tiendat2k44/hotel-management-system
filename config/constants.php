<?php
/**
 * Các hằng số chung của hệ thống
 * File này định nghĩa tất cả các hằng số và đường dẫn được dùng trong toàn bộ ứng dụng
 */

// Thông tin ứng dụng
define('APP_NAME', 'Hotel Management System');  // Tên ứng dụng
define('APP_VERSION', '1.0.0');                // Phiên bản
define('COMPANY_NAME', 'Khách Sạn ABC');       // Tên khách sạn

// ===================================================================
// QUICK FIX: If automatic BASE_URL calculation is failing,
// uncomment and modify the line below with your actual URL:
// ===================================================================
// For XAMPP at http://localhost/TienDat123/hotel-management-system-main/
// define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
//
// For production at https://yourdomain.com/
// define('BASE_URL', 'https://yourdomain.com/hotel-management-system-main/');
// ===================================================================

// URL paths (tính BASE_URL dựa vào SCRIPT_FILENAME và DOCUMENT_ROOT)
$__protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https://' : 'http://';
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__basePath = '';

// Cách 1: Tính từ REQUEST_URI (most reliable in web context)
// REQUEST_URI: /TienDat123/hotel-management-system-main/config/constants.php
$__requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (!empty($__requestUri)) {
    $__requestUri = parse_url($__requestUri, PHP_URL_PATH);
    
    // Bỏ phần /config/constants.php
    if (strpos($__requestUri, '/config/constants.php') !== false) {
        $__basePath = str_replace('/config/constants.php', '', $__requestUri);
        $__basePath = trim($__basePath, '/');
    } elseif (strpos($__requestUri, '/index.php') !== false) {
        // Bỏ phần /index.php
        $__basePath = str_replace('/index.php', '', $__requestUri);
        $__basePath = trim($__basePath, '/');
    } elseif (strpos($__requestUri, '/modules/') !== false) {
        // Bỏ phần /modules/...
        $__parts = explode('/modules/', $__requestUri);
        $__basePath = trim($__parts[0], '/');
    }
}

// Fallback 2: Dùng SCRIPT_FILENAME nếu REQUEST_URI không có
if (empty($__basePath)) {
    $__scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $__documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    
    if ($__documentRoot && $__scriptFilename && strpos($__scriptFilename, $__documentRoot) === 0) {
        // Tách phần path từ document root
        // SCRIPT_FILENAME: /xampp/htdocs/TienDat123/hotel-management-system-main/config/constants.php
        // DOCUMENT_ROOT: /xampp/htdocs
        // Kết quả cần: TienDat123/hotel-management-system-main
        
        $__relativePath = trim(str_replace($__documentRoot, '', $__scriptFilename), '/');
        // $__relativePath = TienDat123/hotel-management-system-main/config/constants.php
        
        // Bỏ /config/constants.php từ cuối
        $__basePath = preg_replace('#/config/constants\.php$#', '', $__relativePath);
    }
}

// Fallback 3: Dùng tên thư mục (nếu không tìm thấy)
if (empty($__basePath)) {
    $__basePath = basename(dirname(__DIR__));
}

define('BASE_URL', $__protocol . $__host . '/' . ($__basePath ? $__basePath . '/' : ''));
define('ADMIN_URL', BASE_URL . 'modules/admin/');
define('STAFF_URL', BASE_URL . 'modules/staff/');
define('CUSTOMER_URL', BASE_URL . 'modules/customer/');

// File paths
define('ROOT_PATH', __DIR__ . '/../');
define('BASE_DIR', __DIR__ . '/..');
define('UPLOAD_PATH', ROOT_PATH . 'assets/uploads/');
define('IMAGE_PATH', ROOT_PATH . 'assets/images/');

// Session settings
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('REMEMBER_ME_DURATION', 86400 * 30); // 30 days

// Pagination
define('ITEMS_PER_PAGE', 10);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_CUSTOMER', 'customer');

// Status constants
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');

// Room status
define('ROOM_AVAILABLE', 'available');
define('ROOM_OCCUPIED', 'occupied');
define('ROOM_CLEANING', 'cleaning');
define('ROOM_MAINTENANCE', 'maintenance');

// Booking status
define('BOOKING_PENDING', 'pending');
define('BOOKING_CONFIRMED', 'confirmed');
define('BOOKING_CHECKED_IN', 'checked_in');
define('BOOKING_CHECKED_OUT', 'checked_out');
define('BOOKING_CANCELLED', 'cancelled');

// Payment methods
define('PAYMENT_CASH', 'cash');
define('PAYMENT_TRANSFER', 'bank_transfer');
define('PAYMENT_CARD', 'credit_card');

// VAT rate
define('VAT_RATE', 10);

// Currency
define('CURRENCY', 'VND');
define('CURRENCY_SYMBOL', '₫');

?>
