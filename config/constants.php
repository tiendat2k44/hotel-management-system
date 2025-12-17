<?php
/**
 * Các hằng số chung của hệ thống
 */

// Application info
define('APP_NAME', 'Hotel Management System');
define('APP_VERSION', '1.0.0');
define('COMPANY_NAME', 'Khách Sạn ABC');

// URL paths (tự động xác định BASE_URL theo môi trường)
// Tạo BASE_URL động để tránh lỗi 404 khi thư mục dự án đổi tên
$__protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https://' : 'http://';
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$__basePath = '';

// Phương pháp 1: Dùng SCRIPT_NAME để xác định base path
// SCRIPT_NAME có dạng: /hotel-management-system-main/config/constants.php
// Tách lấy phần /hotel-management-system-main/
if (strpos($__scriptName, '/config/constants.php') !== false) {
	$__basePath = str_replace('/config/constants.php', '', $__scriptName);
	$__basePath = trim($__basePath, '/');
} else {
	// Fallback: dùng tên thư mục chứa dự án
	$__docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
	$__rootPath = realpath(__DIR__ . '/..');
	if ($__docRoot && $__rootPath && strpos($__rootPath, $__docRoot) === 0) {
		$__basePath = trim(str_replace($__docRoot, '', $__rootPath), '/');
	}
	if ($__basePath === '') {
		$__basePath = basename($__rootPath);
	}
}

// Chuẩn hoá backslashes (Windows) thành forward slashes để hợp lệ trong URL
$__basePath = str_replace('\\', '/', $__basePath);
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
