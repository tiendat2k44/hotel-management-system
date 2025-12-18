<?php
/**
 * Cấu hình kết nối Database
 * File này chứa thông tin kết nối CSDL và tạo đối tượng PDO
 */

// Thông số kết nối database
define('DB_HOST', 'localhost');      // Địa chỉ máy chủ MySQL
define('DB_USER', 'root');           // Tên đăng nhập MySQL
define('DB_PASS', '');               // Mật khẩu MySQL
define('DB_NAME', 'hotel_management_db'); // Tên cơ sở dữ liệu
define('DB_CHARSET', 'utf8mb4');     // Bảng mã ký tự (hỗ trợ emoji, tiếng Việt)

// Ensure constants are loaded
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/constants.php';
}

// Tạo kết nối PDO (PHP Data Objects) đến MySQL
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Bật chế độ báo lỗi exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC  // Trả về mảng kết hợp (key-value)
        )
    );
} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}

?>
