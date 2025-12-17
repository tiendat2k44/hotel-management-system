<?php
/**
 * Các hằng số chung của hệ thống
 */

// Application info
define('APP_NAME', 'Hotel Management System');
define('APP_VERSION', '1.0.0');
define('COMPANY_NAME', 'Khách Sạn ABC');

// URL paths
define('BASE_URL', 'http://localhost/hotel-management-system/');
define('ADMIN_URL', BASE_URL . 'modules/admin/');
define('STAFF_URL', BASE_URL . 'modules/staff/');
define('CUSTOMER_URL', BASE_URL . 'modules/customer/');

// File paths
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
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
