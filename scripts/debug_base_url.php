<?php
/**
 * DEBUG BASE_URL - Kiểm tra xem BASE_URL được tính đúng không
 * Truy cập: http://localhost/<project>/scripts/debug_base_url.php
 */

require_once __DIR__ . '/../config/constants.php';

echo "<h1>BASE_URL Debug Info</h1>";
echo "<pre>";
echo "BASE_URL: " . BASE_URL . "\n";
echo "ROOT_PATH: " . ROOT_PATH . "\n\n";

echo "SERVER Variables:\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";

echo "\n\nAsset URLs:\n";
echo "CSS: " . BASE_URL . "assets/css/style.css\n";
echo "JS: " . BASE_URL . "assets/js/main.js\n";

echo "\n\nRoute URLs:\n";
echo "Login: " . BASE_URL . "modules/auth/login.php\n";
echo "Logout: " . BASE_URL . "modules/auth/logout.php\n";
echo "Dashboard: " . BASE_URL . "modules/customer/dashboard.php\n";

echo "</pre>";
?>
