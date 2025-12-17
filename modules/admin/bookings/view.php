<?php
/**
 * Xem chi tiết booking
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$booking_id = $_GET['id'] ?? '';
$errors = [];
$booking = null;
$services_used = [];

if (empty($booking_id)) {
    redirect('index.php', 'Booking không tồn tại', 'danger');
}

try {
    // Lấy thông tin booking
    $stmt = $pdo->prepare("
        SELECT b.*, u.full_name, u.phone, u.email,
               r.room_number, rt.type_name, rt.base_price,
               c.id as customer_id
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.id = :id
    ");
    $stmt->execute(['id' => $booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        redirect('index.php', 'Booking không tồn tại', 'danger');
    }
    
    // Lấy danh sách dịch vụ đã sử dụng
    $stmt = $pdo->prepare("
        SELECT su.*, s.service_name, s.unit
        FROM service_usage su
        JOIN services s ON su.service_id = s.id
        WHERE su.booking_id = :booking_id
        ORDER BY su.usage_date
    ");
    $stmt->execute(['booking_id' => $booking_id]);
    $services_used = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirect('index.php', 'Lỗi hệ thống', 'danger');
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'confirm' && $booking['status'] == 'pending') {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
            $stmt->execute(['id' => $booking_id]);
            logActivity($pdo, $_SESSION['user_id'], 'CONFIRM_BOOKING', 'Xác nhận booking ' . $booking['booking_code']);
            setFlash('success', 'Xác nhận booking thành công');
            redirect('view.php?id=' . $booking_id);
        } catch (PDOException $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
    
    elseif ($action == 'check_in' && $booking['status'] == 'confirmed') {
        try {
            $stmt = $pdo->prepare("
                UPDATE bookings SET status = 'checked_in', actual_check_in = NOW() WHERE id = :id
            ");
            $stmt->execute(['id' => $booking_id]);
            
            // Update room status
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = :id");
            $stmt->execute(['id' => $booking['room_id']]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CHECK_IN', 'Check-in booking ' . $booking['booking_code']);
            setFlash('success', 'Check-in thành công');
            redirect('view.php?id=' . $booking_id);
        } catch (PDOException $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
    
    elseif ($action == 'check_out' && $booking['status'] == 'checked_in') {
        try {
            $stmt = $pdo->prepare("
                UPDATE bookings SET status = 'checked_out', actual_check_out = NOW() WHERE id = :id
            ");
            $stmt->execute(['id' => $booking_id]);
            
            // Update room status
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'cleaning' WHERE id = :id");
            $stmt->execute(['id' => $booking['room_id']]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CHECK_OUT', 'Check-out booking ' . $booking['booking_code']);
            setFlash('success', 'Check-out thành công');
            redirect('view.php?id=' . $booking_id);
        } catch (PDOException $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
    
    elseif ($action == 'cancel') {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = :id");
            $stmt->execute(['id' => $booking_id]);
            
            // Update room status
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = :id");
            $stmt->execute(['id' => $booking['room_id']]);
            
            logActivity($pdo, $_SESSION['user_id'], 'CANCEL_BOOKING', 'Hủy booking ' . $booking['booking_code']);
            setFlash('success', 'Hủy booking thành công');
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
}

$nights = calculateNights($booking['check_in'], $booking['check_out']);
$room_total = $booking['base_price'] * $nights;
$services_total = array_sum(array_column($services_used, 'total_price'));

$page_title = 'Chi tiết booking';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check"></i> Booking #<?php echo esc($booking['booking_code']); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo esc($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Thông tin khách hàng -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tên:</strong> <?php echo esc($booking['full_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo esc($booking['email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Điện thoại:</strong> <?php echo esc($booking['phone']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin phòng -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2"><i class="fas fa-bed"></i> Thông tin phòng</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Số phòng:</strong> <?php echo esc($booking['room_number']); ?></p>
                                <p><strong>Loại:</strong> <?php echo esc($booking['type_name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Giá cơ bản:</strong> <?php echo formatCurrency($booking['base_price']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin check-in/check-out -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2"><i class="fas fa-calendar"></i> Thời gian ở</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?></p>
                                <?php if ($booking['actual_check_in']): ?>
                                    <p><small class="text-muted">Thực tế: <?php echo formatDateTime($booking['actual_check_in']); ?></small></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?></p>
                                <?php if ($booking['actual_check_out']): ?>
                                    <p><small class="text-muted">Thực tế: <?php echo formatDateTime($booking['actual_check_out']); ?></small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p><strong>Số đêm:</strong> <?php echo $nights; ?> đêm</p>
                        <p><strong>Số người:</strong> <?php echo $booking['adults']; ?> người lớn, <?php echo $booking['children']; ?> trẻ em</p>
                    </div>
                    
                    <!-- Danh sách dịch vụ -->
                    <?php if (!empty($services_used)): ?>
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2"><i class="fas fa-concierge-bell"></i> Dịch vụ đã sử dụng</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Dịch vụ</th>
                                            <th>Ngày sử dụng</th>
                                            <th>Số lượng</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services_used as $service): ?>
                                            <tr>
                                                <td><?php echo esc($service['service_name']); ?></td>
                                                <td><?php echo formatDate($service['usage_date']); ?></td>
                                                <td><?php echo $service['quantity']; ?> <?php echo esc($service['unit']); ?></td>
                                                <td><?php echo formatCurrency($service['total_price']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Tổng tiền -->
                    <div class="mb-4 border-top pt-3">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <p class="d-flex justify-content-between"><strong>Phòng:</strong> <span><?php echo formatCurrency($room_total); ?></span></p>
                                <p class="d-flex justify-content-between"><strong>Dịch vụ:</strong> <span><?php echo formatCurrency($services_total); ?></span></p>
                                <p class="d-flex justify-content-between"><strong>Tổng cộng:</strong> <span class="text-danger" style="font-size: 1.2em;"><?php echo formatCurrency($booking['total_amount']); ?></span></p>
                                <p class="d-flex justify-content-between"><strong>Tiền cọc:</strong> <span><?php echo formatCurrency($booking['deposit_amount']); ?></span></p>
                                <p class="d-flex justify-content-between border-top pt-2"><strong>Còn lại:</strong> <span><?php echo formatCurrency($booking['total_amount'] - $booking['deposit_amount']); ?></span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ghi chú -->
                    <?php if (!empty($booking['special_requests'])): ?>
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2"><i class="fas fa-sticky-note"></i> Yêu cầu đặc biệt</h6>
                            <p><?php echo esc($booking['special_requests']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Trạng thái -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">Trạng thái</h6>
                </div>
                <div class="card-body">
                    <?php
                    $status_badges = [
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'checked_in' => 'success',
                        'checked_out' => 'secondary',
                        'cancelled' => 'danger'
                    ];
                    $status_texts = [
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'checked_in' => 'Đã nhận phòng',
                        'checked_out' => 'Đã trả phòng',
                        'cancelled' => 'Đã hủy'
                    ];
                    ?>
                    <p class="mb-3">
                        <span class="badge bg-<?php echo $status_badges[$booking['status']]; ?> p-2" style="font-size: 1em;">
                            <?php echo $status_texts[$booking['status']]; ?>
                        </span>
                    </p>
                    
                    <!-- Action buttons -->
                    <form method="POST" class="d-grid gap-2">
                        <?php if ($booking['status'] == 'pending'): ?>
                            <button type="submit" name="action" value="confirm" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Xác nhận
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] == 'confirmed'): ?>
                            <button type="submit" name="action" value="check_in" class="btn btn-info btn-sm">
                                <i class="fas fa-sign-in-alt"></i> Check-in
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] == 'checked_in'): ?>
                            <button type="submit" name="action" value="check_out" class="btn btn-warning btn-sm">
                                <i class="fas fa-sign-out-alt"></i> Check-out
                            </button>
                        <?php endif; ?>
                        
                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                            <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm" onclick="return confirm('Bạn chắc chứ?')">
                                <i class="fas fa-times"></i> Hủy booking
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Quick actions -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Thao tác</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="edit.php?id=<?php echo $booking_id; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <a href="../../../api/generate_invoice.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-outline-success btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> Xuất hóa đơn
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
