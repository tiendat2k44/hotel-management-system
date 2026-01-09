<?php
/**
 * Auth check - Kiểm tra session
 */

// Bắt đầu session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['timeout'] = true;
}

$_SESSION['LAST_ACTIVITY'] = time();

?>
