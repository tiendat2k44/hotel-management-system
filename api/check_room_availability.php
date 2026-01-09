<?php
/**
 * API: Kiểm tra phòng có sẵn
<<<<<<< HEAD
=======
 * Endpoint: POST /api/check_room_availability.php
 * Input: check_in, check_out (format Y-m-d)
 * Output: JSON array các phòng trống trong khoảng thời gian đó
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
 */

require_once '../config/constants.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

<<<<<<< HEAD
header('Content-Type: application/json');
=======
header('Content-Type: application/json');  // Trả về JSON
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291

$check_in = $_POST['check_in'] ?? '';
$check_out = $_POST['check_out'] ?? '';

if (!validateDate($check_in) || !validateDate($check_out)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ngày không hợp lệ']);
    exit;
}

try {
<<<<<<< HEAD
=======
    // Tìm các phòng KHÔNG có booking trùng khoảng thời gian
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
    $stmt = $pdo->prepare("
        SELECT r.id, r.room_number, rt.type_name, rt.base_price
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id NOT IN (
<<<<<<< HEAD
            SELECT room_id FROM bookings 
            WHERE status IN ('confirmed', 'checked_in')
            AND (check_in < :check_out AND check_out > :check_in)
        )
        AND r.status IN ('available', 'occupied')
        ORDER BY r.floor, r.room_number
=======
            -- Subquery: Lấy danh sách room_id đã được đặt trong khoảng thời gian này
            SELECT room_id FROM bookings 
            WHERE status IN ('confirmed', 'checked_in')
            AND (check_in < :check_out AND check_out > :check_in)  -- Điều kiện overlap
        )
        AND r.status IN ('available', 'occupied')  -- Phòng có thể book
        ORDER BY r.floor, r.room_number  -- Sắp xếp theo tầng và số phòng
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
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
