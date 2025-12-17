<?php
/**
 * Customer Dashboard
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$customer_id = $_SESSION['customer_id'];

try {
    // Lấy thông tin khách hàng
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name, u.email, u.phone
        FROM customers c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = :id
    ");
    $stmt->execute(['id' => $customer_id]);
    $customer_info = $stmt->fetch();
    
    // Lấy danh sách booking của khách hàng
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, rt.type_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.customer_id = :customer_id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $stmt->execute(['customer_id' => $customer_id]);
    $bookings = $stmt->fetchAll();
    
    // Thống kê
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            COUNT(CASE WHEN status = 'checked_out' THEN 1 END) as completed,
            COUNT(CASE WHEN status IN ('confirmed', 'checked_in') THEN 1 END) as active,
            COALESCE(SUM(total_amount), 0) as total_spent
        FROM bookings
        WHERE customer_id = :customer_id
    ");
    $stmt->execute(['customer_id' => $customer_id]);
    $stats = $stmt->fetch();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$page_title = 'Dashboard khách hàng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Chào mừng -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <div class="card-body">
                    <h4 class="mb-0"><i class="fas fa-user"></i> Chào mừng <?php echo esc($_SESSION['full_name']); ?></h4>
                    <p class="mb-0 mt-2">Quản lý các booking và hồ sơ cá nhân của bạn tại đây</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5><?php echo $stats['total_bookings']; ?></h5>
                    <p class="text-muted mb-0">Tổng booking</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5><?php echo $stats['completed']; ?></h5>
                    <p class="text-muted mb-0">Hoàn thành</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5><?php echo $stats['active']; ?></h5>
                    <p class="text-muted mb-0">Đang hoạt động</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h5><?php echo formatCurrency($stats['total_spent']); ?></h5>
                    <p class="text-muted mb-0">Tổng chi tiêu</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking gần đây -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Booking gần đây</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã booking</th>
                                <th>Phòng</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo esc($booking['booking_code']); ?></strong></td>
                                    <td><?php echo esc($booking['room_number']); ?> (<?php echo esc($booking['type_name']); ?>)</td>
                                    <td>
                                        <?php echo formatDate($booking['check_in']); ?> - 
                                        <?php echo formatDate($booking['check_out']); ?>
                                    </td>
                                    <td>
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
                                        <span class="badge bg-<?php echo $status_badges[$booking['status']] ?? 'secondary'; ?>">
                                            <?php echo $status_texts[$booking['status']] ?? 'Không xác định'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="booking_history.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="booking_history.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-list"></i> Xem tất cả
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick actions -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-plus"></i> Đặt phòng</h6>
                </div>
                <div class="card-body">
                    <p>Bạn muốn đặt thêm phòng?</p>
                    <a href="../../index.php" class="btn btn-success w-100">
                        <i class="fas fa-search"></i> Tìm phòng
                    </a>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-user-edit"></i> Hồ sơ</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Tên:</strong> <?php echo esc($customer_info['full_name'] ?? ''); ?></p>
                    <p class="mb-2"><strong>Email:</strong> <?php echo esc($customer_info['email'] ?? ''); ?></p>
                    <p class="mb-3"><strong>Điện thoại:</strong> <?php echo esc($customer_info['phone'] ?? 'Chưa cập nhật'); ?></p>
                    <a href="../auth/profile.php" class="btn btn-info btn-sm w-100">
                        <i class="fas fa-edit"></i> Chỉnh sửa hồ sơ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
