<?php
/**
 * Trang đăng xuất
 */

// Load constants trước
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

// Ghi log hoạt động (nếu đang đăng nhập)
if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'LOGOUT', 'Đăng xuất');
}

// Huỷ session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = [];
session_unset();
session_destroy();
if (session_status() === PHP_SESSION_NONE || session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

// Xoá cookie nhớ đăng nhập
setcookie('remember_token', '', time() - 3600, '/');

// Điều hướng về trang chủ
redirect(BASE_URL, 'Đăng xuất thành công');
?>
