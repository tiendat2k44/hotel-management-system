<?php
/**
 * Simple health check script
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

function ok($label) { echo "[OK] $label\n"; }
function fail($label, $msg) { echo "[FAIL] $label - $msg\n"; }

try {
    // DB connection
    $pdo->query('SELECT 1');
    ok('Database connection');
} catch (Exception $e) {
    fail('Database connection', $e->getMessage());
}

// BASE_URL
echo 'BASE_URL: ' . BASE_URL . "\n";

// Assets URLs
$assets = [
    BASE_URL . 'assets/css/style.css',
    BASE_URL . 'assets/js/main.js',
];

foreach ($assets as $url) {
    $headers = @get_headers($url);
    if ($headers && strpos($headers[0], '200') !== false) {
        ok("Asset reachable: $url");
    } else {
        fail("Asset unreachable: $url", $headers[0] ?? 'no headers');
    }
}

// Routes
$routes = [
    BASE_URL . 'modules/auth/login.php',
    BASE_URL . 'modules/auth/logout.php',
    BASE_URL . 'modules/customer/dashboard.php',
];
foreach ($routes as $url) {
    $headers = @get_headers($url);
    if ($headers && strpos($headers[0], '200') !== false) {
        ok("Route reachable: $url");
    } else {
        fail("Route unreachable: $url", $headers[0] ?? 'no headers');
    }
}
