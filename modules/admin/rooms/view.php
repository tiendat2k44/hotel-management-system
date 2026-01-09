<?php
/**
 * Xem chi tiết phòng
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
    $stmt = $pdo->prepare("
        SELECT r.*, rt.type_name, rt.base_price, rt.capacity, rt.description as type_description
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id = :id
    ");
    $stmt->execute(['id' => $room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        redirect('index.php', 'Phòng không tồn tại', 'danger');
    }
    
    // Lấy danh sách booking của phòng này
    $stmt = $pdo->prepare("
        SELECT b.*, c.full_name, c.email, c.phone,
               u.username
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        WHERE b.room_id = :room_id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $stmt->execute(['room_id' => $room_id]);
    $bookings = $stmt->fetchAll();
    
    // Lấy booking hiện tại (nếu có)
    $stmt = $pdo->prepare("
        SELECT b.*, c.full_name, c.email, c.phone
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        WHERE b.room_id = :room_id 
          AND b.status IN ('confirmed', 'checked_in')
          AND b.check_out >= CURDATE()
        ORDER BY b.check_in
        LIMIT 1
    ");
    $stmt->execute(['room_id' => $room_id]);
    $current_booking = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirect('index.php', 'Lỗi hệ thống', 'danger');
}

$page_title = 'Chi tiết phòng ' . $room['room_number'];
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Quản lý phòng</a></li>
                    <li class="breadcrumb-item active">Phòng <?php echo esc($room['room_number']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <!-- Cột trái: Thông tin phòng và ảnh -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bed"></i> Phòng <?php echo esc($room['room_number']); ?></h5>
                    <div>
                        <a href="edit.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="index.php" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($room['image_url'])): ?>
                        <div class="text-center mb-4">
                            <?php if (strpos($room['image_url'], 'http') === 0): ?>
                                <!-- URL từ internet -->
                                <img src="<?php echo esc($room['image_url']); ?>" 
                                     alt="Room <?php echo esc($room['room_number']); ?>" 
                                     class="img-fluid rounded shadow-sm"
                                     style="max-height: 400px; object-fit: cover; width: 100%;">
                            <?php else: ?>
                                <!-- URL từ server -->
                                <img src="<?php echo BASE_URL . esc($room['image_url']); ?>" 
                                     alt="Room <?php echo esc($room['room_number']); ?>" 
                                     class="img-fluid rounded shadow-sm"
                                     style="max-height: 400px; object-fit: cover; width: 100%;"
                                     onerror="this.src='<?php echo BASE_URL; ?>assets/images/no-image.png'">
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-light text-center py-5 mb-4 rounded">
                            <i class="fas fa-image fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có hình ảnh phòng</p>
                            <a href="edit.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Thêm hình ảnh
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Thông tin cơ bản</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Số phòng:</td>
                                    <td><?php echo esc($room['room_number']); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Loại phòng:</td>
                                    <td><?php echo esc($room['type_name']); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tầng:</td>
                                    <td>Tầng <?php echo $room['floor']; ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Sức chứa:</td>
                                    <td><?php echo $room['capacity']; ?> người</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Giá cơ bản:</td>
                                    <td class="text-success fw-bold"><?php echo formatCurrency($room['base_price']); ?>/đêm</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted">Trạng thái</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Trạng thái hiện tại:</td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'available' => 'success',
                                            'occupied' => 'danger',
                                            'cleaning' => 'warning',
                                            'maintenance' => 'secondary'
                                        ];
                                        $status_texts = [
                                            'available' => 'Trống',
                                            'occupied' => 'Đã đặt',
                                            'cleaning' => 'Đang dọn',
                                            'maintenance' => 'Bảo trì'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $status_badges[$room['status']] ?? 'secondary'; ?>">
                                            <?php echo $status_texts[$room['status']] ?? 'Không xác định'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Ngày tạo:</td>
                                    <td><?php echo formatDateTime($room['created_at']); ?></td>
                                </tr>
                            </table>
                            
                            <?php if (!empty($room['notes'])): ?>
                                <h6 class="text-muted mt-3">Ghi chú</h6>
                                <div class="alert alert-info mb-0">
                                    <?php echo nl2br(esc($room['notes'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($room['type_description'])): ?>
                        <div class="mt-3">
                            <h6 class="text-muted">Mô tả loại phòng</h6>
                            <p class="text-muted"><?php echo nl2br(esc($room['type_description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Cột phải: Booking hiện tại và lịch sử -->
        <div class="col-lg-4">
            <!-- Booking hiện tại -->
            <?php if ($current_booking): ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Booking hiện tại</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Mã booking:</strong><br>
                            <a href="<?php echo ADMIN_URL; ?>bookings/view.php?id=<?php echo $current_booking['id']; ?>">
                                <?php echo esc($current_booking['booking_code']); ?>
                            </a>
                        </p>
                        <p class="mb-2">
                            <strong>Khách hàng:</strong><br>
                            <?php echo esc($current_booking['full_name']); ?>
                        </p>
                        <p class="mb-2">
                            <strong>Check-in:</strong><br>
                            <?php echo formatDate($current_booking['check_in']); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Check-out:</strong><br>
                            <?php echo formatDate($current_booking['check_out']); ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Lịch sử booking -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Lịch sử booking gần đây</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($bookings)): ?>
                        <div class="p-3 text-center text-muted">
                            <i class="fas fa-info-circle"></i> Chưa có booking nào
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($bookings as $booking): ?>
                                <a href="<?php echo ADMIN_URL; ?>bookings/view.php?id=<?php echo $booking['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?php echo esc($booking['booking_code']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo esc($booking['full_name']); ?>
                                            </small>
                                            <br>
                                            <small>
                                                <?php echo formatDate($booking['check_in']); ?> - 
                                                <?php echo formatDate($booking['check_out']); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo match($booking['status']) {
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'checked_in' => 'success',
                                                'checked_out' => 'secondary',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php 
                                            echo match($booking['status']) {
                                                'pending' => 'Chờ xử lý',
                                                'confirmed' => 'Đã xác nhận',
                                                'checked_in' => 'Đã nhận phòng',
                                                'checked_out' => 'Đã trả phòng',
                                                'cancelled' => 'Đã hủy',
                                                default => $booking['status']
                                            };
                                            ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
