<?php
/**
 * Auth check - Kiểm tra session
 * File này khởi tạo session và kiểm tra timeout để đảm bảo bảo mật
 */

// Bắt đầu session nếu chưa có (dùng để lưu thông tin đăng nhập)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra timeout: Nếu user không hoạt động quá lâu thì tự động đăng xuất
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['timeout'] = true;
}

$_SESSION['LAST_ACTIVITY'] = time();

?>
