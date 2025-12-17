<?php
/**
 * Trang logout
 */

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';
// Load constants first
require_once '../../config/constants.php';

// Log activity
if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'LOGOUT', 'Đăng xuất');
}

// Destroy session
session_unset();
session_destroy();

// Clear cookies
setcookie('remember_token', '', time() - 3600, '/');

// Redirect
redirect(BASE_URL, 'Đăng xuất thành công');
?>
