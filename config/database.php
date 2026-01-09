<?php
/**
 * Cấu hình kết nối Database
<<<<<<< HEAD
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '1508');
define('DB_NAME', 'hotel_management_db');
define('DB_CHARSET', 'utf8mb4');
=======
 * File này chứa thông tin kết nối CSDL và tạo đối tượng PDO
 */

// Thông số kết nối database
define('DB_HOST', 'localhost');      // Địa chỉ máy chủ MySQL
define('DB_USER', 'root');           // Tên đăng nhập MySQL
define('DB_PASS', '');               // Mật khẩu MySQL
define('DB_NAME', 'hotel_management_db'); // Tên cơ sở dữ liệu
define('DB_CHARSET', 'utf8mb4');     // Bảng mã ký tự (hỗ trợ emoji, tiếng Việt)
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291

// Ensure constants are loaded
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/constants.php';
}

<<<<<<< HEAD
// PDO connection
=======
// Tạo kết nối PDO (PHP Data Objects) đến MySQL
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        array(
<<<<<<< HEAD
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
=======
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Bật chế độ báo lỗi exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC  // Trả về mảng kết hợp (key-value)
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
        )
    );
} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}

?>
