<?php
/**
 * Seed demo passwords to 123456 (bcrypt cost 12)
 * Usage: Place this under your web root and run via browser or CLI:
 * - Browser: http://localhost/<project>/scripts/seed_demo_passwords.php
 * - CLI: php scripts/seed_demo_passwords.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

$users = ['admin', 'staff1', 'customer1'];
$newPassword = '123456';
$cost = 12;

$results = [];

try {
    foreach ($users as $username) {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => $cost]);
        $stmt = $pdo->prepare("UPDATE users SET password = :password, is_active = 1 WHERE username = :username");
        $stmt->execute(['password' => $hash, 'username' => $username]);
        $results[] = [
            'username' => $username,
            'updated' => true,
            'hash' => $hash,
        ];
    }
    $status = 'success';
    $message = 'Demo passwords updated to 123456';
} catch (Exception $e) {
    $status = 'error';
    $message = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode([
    'status' => $status,
    'message' => $message,
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
