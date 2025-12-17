<?php
require_once '../../config/constants.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

// Chỉ cho phép staff/admin, sau đó chuyển về dashboard
requireRole([ROLE_STAFF, ROLE_ADMIN]);
header('Location: ' . STAFF_URL . 'dashboard.php');
exit();
