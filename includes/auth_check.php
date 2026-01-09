<?php
/**
 * Auth check - Kiểm tra session
<<<<<<< HEAD
 */

// Bắt đầu session
=======
 * File này khởi tạo session và kiểm tra timeout để đảm bảo bảo mật
 */

// Bắt đầu session nếu chưa có (dùng để lưu thông tin đăng nhập)
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
// Session timeout
=======
// Kiểm tra timeout: Nếu user không hoạt động quá lâu thì tự động đăng xuất
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['timeout'] = true;
}

$_SESSION['LAST_ACTIVITY'] = time();

?>
