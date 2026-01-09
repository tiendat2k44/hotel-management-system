<?php
/**
 * API: Kiểm tra phòng có sẵn
 */

require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$check_in = $_POST['check_in'] ?? '';
$check_out = $_POST['check_out'] ?? '';

if (!validateDate($check_in) || !validateDate($check_out)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ngày không hợp lệ']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.id, r.room_number, rt.type_name, rt.base_price
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id NOT IN (
            SELECT room_id FROM bookings 
            WHERE status IN ('confirmed', 'checked_in')
            AND (check_in < :check_out AND check_out > :check_in)
        )
        AND r.status IN ('available', 'occupied')
        ORDER BY r.floor, r.room_number
    ");
    
    $stmt->execute([
        'check_in' => $check_in,
        'check_out' => $check_out
    ]);
    
    $rooms = $stmt->fetchAll();
    echo json_encode($rooms);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi hệ thống']);
}
?>
