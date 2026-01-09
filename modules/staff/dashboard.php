<?php
/**
<<<<<<< HEAD
 * Staff Dashboard
=======
 * Staff Dashboard - Trang chủ nhân viên
 * Hiển thị thống kê và danh sách công việc cần làm (check-in, check-out)
 * Nhân viên không thấy doanh thu (khác với admin)
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

<<<<<<< HEAD
requireRole([ROLE_STAFF, ROLE_ADMIN]);

// Lấy thống kê
try {
    // Tổng số phòng
=======
requireRole([ROLE_STAFF, ROLE_ADMIN]);  // Staff hoặc Admin đều xem được

// Lấy các số liệu thống kê cho nhân viên
try {
    // 1. Tổng số phòng
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms");
    $stmt->execute();
    $total_rooms = $stmt->fetch()['count'];
    
<<<<<<< HEAD
    // Phòng trống
=======
    // 2. Phòng trống (hiện tại)
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT r.id) as count FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND b.status IN ('pending','confirmed','checked_in')
            AND b.check_out > NOW()
        WHERE r.status = 'available' AND b.id IS NULL
    ");
    $stmt->execute();
    $available_rooms = $stmt->fetch()['count'];
    
    // Phòng đang sử dụng (checked_in)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT r.id) as count FROM rooms r
        JOIN bookings b ON r.id = b.room_id AND b.status = 'checked_in'
    ");
    $stmt->execute();
    $occupied_count = $stmt->fetch()['count'];
    
    // Booking hôm nay
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE DATE(check_in) = CURDATE()
    ");
    $stmt->execute();
    $today_bookings = $stmt->fetch()['count'];
    
<<<<<<< HEAD
    // Booking sắp check-in (48 giờ tới)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE DATE(check_in) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        AND status IN ('pending', 'confirmed')
=======
    // 5. Booking sắp check-in (trong vòng 48 giờ tới)
    // Nhân viên cần chuẩn bị phòng cho các booking này
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE DATE(check_in) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
        AND status IN ('pending', 'confirmed')  -- Chưa check-in
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
    ");
    $stmt->execute();
    $upcoming_bookings = $stmt->fetch()['count'];
    
<<<<<<< HEAD
    // Booking cần check-out hôm nay
=======
    // 6. Booking cần check-out hôm nay
    // Nhân viên cần xử lý check-out và dọn phòng
>>>>>>> 6981403bf39073ea6cabada40bb02769739be291
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM bookings 
        WHERE DATE(check_out) = CURDATE() AND status IN ('checked_in')
    ");
    $stmt->execute();
    $checkout_today = $stmt->fetch()['count'];
    
    // Booking gần đây (chưa check-in)
    $stmt = $pdo->prepare("
        SELECT b.*, u.full_name, r.room_number, rt.type_name, c.id as customer_id
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.status IN ('pending', 'confirmed')
        ORDER BY b.check_in ASC
        LIMIT 15
    ");
    $stmt->execute();
    $pending_bookings = $stmt->fetchAll();
    
    // Phòng đang sử dụng
    $stmt = $pdo->prepare("
        SELECT r.id, r.room_number, rt.type_name, b.check_out, u.full_name, b.id as booking_id
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        JOIN bookings b ON r.id = b.room_id
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        WHERE b.status IN ('checked_in')
        ORDER BY b.check_out ASC
        LIMIT 15
    ");
    $stmt->execute();
    $occupied_rooms = $stmt->fetchAll();
    
    // Phòng cần dọn dẫu
    $stmt = $pdo->prepare("
        SELECT r.id, r.room_number, rt.type_name, b.check_out, u.full_name
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        JOIN bookings b ON r.id = b.room_id
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        WHERE b.status = 'checked_out' 
        AND DATE(b.check_out) = CURDATE()
        ORDER BY b.check_out
        LIMIT 10
    ");
    $stmt->execute();
    $cleaning_rooms = $stmt->fetchAll();
    
    // Booking sắp đến
    $stmt = $pdo->prepare("
        SELECT b.*, u.full_name, r.room_number, rt.type_name, 
               DATEDIFF(b.check_in, CURDATE()) as days_until_checkin
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.status IN ('pending', 'confirmed')
        AND b.check_in BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        ORDER BY b.check_in ASC
        LIMIT 10
    ");
    $stmt->execute();
    $upcoming = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$page_title = 'Staff Dashboard';
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
                                Phòng trống
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $available_rooms; ?>/<?php echo $total_rooms; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-primary"></i>
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
                                Phòng đang sử dụng
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $occupied_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-success"></i>
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
                                Cần check-out hôm nay
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $checkout_today; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-out-alt fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Sắp check-in (48h)
                            </div>
                            <div class="h3 mb-0 text-gray-800"><?php echo $upcoming_bookings; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Booking sắp đến -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Booking sắp đến (3 ngày tới)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Phòng</th>
                                <th>Khách hàng</th>
                                <th>Check-in</th>
                                <th>Ngày đến</th>
                                <th>Loại phòng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($upcoming) > 0): ?>
                                <?php foreach ($upcoming as $booking): ?>
                                    <tr>
                                        <td><strong><?php echo esc($booking['room_number']); ?></strong></td>
                                        <td><?php echo esc($booking['full_name']); ?></td>
                                        <td><?php echo formatDate($booking['check_in']); ?></td>
                                        <td>
                                            <?php if ($booking['days_until_checkin'] == 0): ?>
                                                <span class="badge bg-danger">Hôm nay</span>
                                            <?php elseif ($booking['days_until_checkin'] == 1): ?>
                                                <span class="badge bg-warning">Ngày mai</span>
                                            <?php else: ?>
                                                <span class="badge bg-info"><?php echo $booking['days_until_checkin']; ?> ngày</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc($booking['type_name']); ?></td>
                                        <td>
                                            <a href="../bookings/view.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Không có booking sắp đến</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Phòng đang sử dụng -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-door-open"></i> Phòng đang sử dụng (<?php echo count($occupied_rooms); ?>)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Phòng</th>
                                <th>Khách hàng</th>
                                <th>Loại</th>
                                <th>Check-out</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($occupied_rooms) > 0): ?>
                                <?php foreach ($occupied_rooms as $room): ?>
                                    <tr>
                                        <td><strong><?php echo esc($room['room_number']); ?></strong></td>
                                        <td><?php echo esc($room['full_name']); ?></td>
                                        <td><?php echo esc($room['type_name']); ?></td>
                                        <td>
                                            <?php 
                                            $checkout_date = strtotime($room['check_out']);
                                            $today = strtotime(date('Y-m-d'));
                                            $diff = ($checkout_date - $today) / (60 * 60 * 24);
                                            if ($diff < 0) {
                                                echo '<span class="badge bg-danger">Quá hạn ' . abs(intval($diff)) . ' ngày</span>';
                                            } elseif ($diff == 0) {
                                                echo '<span class="badge bg-warning">Hôm nay</span>';
                                            } else {
                                                echo formatDate($room['check_out']);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="../bookings/view.php?id=<?php echo $room['booking_id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Không có phòng nào đang sử dụng</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar - Cần dọn dẫu & Công việc -->
        <div class="col-md-4">
            <!-- Phòng cần dọn dẫu -->
            <div class="card shadow mb-4 border-left-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-broom"></i> Cần dọn dẫu hôm nay</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (count($cleaning_rooms) > 0): ?>
                        <?php foreach ($cleaning_rooms as $room): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0">Phòng <?php echo esc($room['room_number']); ?></h6>
                                    <small class="text-muted"><?php echo esc($room['type_name']); ?></small>
                                    <p class="mb-0 text-primary"><?php echo esc($room['full_name']); ?></p>
                                </div>
                                <span class="badge bg-warning">Trả: <?php echo formatDate($room['check_out'], 'H:i'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="list-group-item text-center text-muted py-3">
                            <i class="fas fa-check-circle"></i> Không có phòng cần dọn
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thực đơn công việc -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks"></i> Công việc</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="tasks.php?type=pending-bookings" class="list-group-item list-group-item-action">
                        <i class="fas fa-hourglass-half text-warning"></i> Booking chờ xác nhận
                        <span class="badge bg-warning float-end"><?php echo $upcoming_bookings; ?></span>
                    </a>
                    <a href="tasks.php?type=occupied-rooms" class="list-group-item list-group-item-action">
                        <i class="fas fa-door-open text-success"></i> Phòng đang sử dụng
                        <span class="badge bg-success float-end"><?php echo $occupied_count; ?></span>
                    </a>
                    <a href="tasks.php?type=cleaning-rooms" class="list-group-item list-group-item-action">
                        <i class="fas fa-broom text-info"></i> Phòng cần dọn
                        <span class="badge bg-info float-end"><?php echo count($cleaning_rooms); ?></span>
                    </a>
                    <a href="tasks.php?type=upcoming-checkins" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-check text-danger"></i> Sắp check-in (48h)
                        <span class="badge bg-danger float-end"><?php echo $upcoming_bookings; ?></span>
                    </a>
                    <hr style="margin: 0;">
                    <a href="bookings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-list text-secondary"></i> Danh sách bookings
                    </a>
                    <a href="rooms.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-bed text-primary"></i> Quản lý phòng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
