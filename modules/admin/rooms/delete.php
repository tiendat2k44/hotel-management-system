<?php
/**
 * Xóa phòng
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$room_id = $_GET['id'] ?? '';

if (empty($room_id)) {
    redirect('index.php', 'Phòng không tồn tại', 'danger');
}

try {
    // Lấy thông tin phòng
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :id");
    $stmt->execute(['id' => $room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        redirect('index.php', 'Phòng không tồn tại', 'danger');
    }
    
    // Kiểm tra xem có booking nào không
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE room_id = :room_id 
        AND status IN ('confirmed', 'checked_in')
    ");
    $stmt->execute(['room_id' => $room_id]);
    
    if ($stmt->fetch()['count'] > 0) {
        redirect('index.php', 'Không thể xóa phòng này vì có booking chưa hoàn thành', 'danger');
    }
    
    // Xóa phòng
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
    $stmt->execute(['id' => $room_id]);
    
    logActivity($pdo, $_SESSION['user_id'], 'DELETE_ROOM', 'Xóa phòng ' . $room['room_number']);
    redirect('index.php', 'Xóa phòng thành công');
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirect('index.php', 'Lỗi hệ thống', 'danger');
}
?>
