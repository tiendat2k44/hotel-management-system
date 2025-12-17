<?php
/**
 * Admin Dashboard
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

// Lấy thống kê
try {
    // Tổng số phòng
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms");
    $stmt->execute();
    $total_rooms = $stmt->fetch()['count'];
    
    // Phòng trống
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE status = 'available'");
    $stmt->execute();
    $available_rooms = $stmt->fetch()['count'];
    
    // Booking hôm nay
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE DATE(check_in) = DATE(NOW()) 
        AND status IN ('confirmed', 'checked_in')
    ");
    $stmt->execute();
    $today_bookings = $stmt->fetch()['count'];
    
    // Doanh thu tháng này
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM payments 
        WHERE MONTH(payment_date) = MONTH(NOW())
        AND YEAR(payment_date) = YEAR(NOW())
        AND status = 'completed'
    ");
    $stmt->execute();
    $monthly_revenue = $stmt->fetch()['total'];
    
    // Booking gần đây
    $stmt = $pdo->prepare("
        SELECT b.*, u.full_name, r.room_number, rt.type_name, c.id as customer_id
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_bookings = $stmt->fetchAll();
    
    // Phòng đang sử dụng
    $stmt = $pdo->prepare("
        SELECT r.room_number, rt.type_name, b.check_out, u.full_name
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        JOIN bookings b ON r.id = b.room_id
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        WHERE r.status = 'occupied'
        AND b.status IN ('checked_in')
        ORDER BY b.check_out
        LIMIT 10
    ");
    $stmt->execute();
    $occupied_rooms = $stmt->fetchAll();
    
    // Doanh thu 12 tháng gần nhất
    $stmt = $pdo->prepare("
        SELECT 
            MONTH(payment_date) as month,
            YEAR(payment_date) as year,
            SUM(amount) as total
        FROM payments
        WHERE YEAR(payment_date) = YEAR(NOW()) OR YEAR(payment_date) = YEAR(NOW()) - 1
        AND status = 'completed'
        GROUP BY YEAR(payment_date), MONTH(payment_date)
        ORDER BY YEAR(payment_date), MONTH(payment_date)
    ");
    $stmt->execute();
    $revenue_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$page_title = 'Admin Dashboard';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng phòng
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $total_rooms; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bed fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Phòng trống
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $available_rooms; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Booking hôm nay
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $today_bookings; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Doanh thu tháng
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo formatCurrency($monthly_revenue); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-alt fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Booking gần đây -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Booking gần đây</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mã booking</th>
                                <th>Khách hàng</th>
                                <th>Phòng</th>
                                <th>Ngày check-in</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td><strong><?php echo esc($booking['booking_code']); ?></strong></td>
                                    <td><?php echo esc($booking['full_name']); ?></td>
                                    <td><?php echo esc($booking['room_number']); ?></td>
                                    <td><?php echo formatDate($booking['check_in']); ?></td>
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
                                        <a href="../bookings/view.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Phòng đang sử dụng -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-door-open"></i> Phòng đang sử dụng</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($occupied_rooms as $room): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0">Phòng <?php echo esc($room['room_number']); ?></h6>
                                    <small class="text-muted"><?php echo esc($room['type_name']); ?></small>
                                    <p class="mb-0"><strong><?php echo esc($room['full_name']); ?></strong></p>
                                </div>
                                <small class="text-danger">
                                    Trả: <?php echo formatDate($room['check_out']); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menu nhanh -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-tools"></i> Quản lý</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="rooms/index.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-bed"></i> Quản lý phòng
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="bookings/index.php" class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-check"></i> Quản lý booking
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="services/index.php" class="btn btn-outline-info w-100">
                                <i class="fas fa-concierge-bell"></i> Quản lý dịch vụ
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="customers/index.php" class="btn btn-outline-warning w-100">
                                <i class="fas fa-users"></i> Quản lý khách hàng
                            </a>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <a href="reports/index.php" class="btn btn-outline-danger w-100">
                                <i class="fas fa-chart-bar"></i> Báo cáo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
