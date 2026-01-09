<?php
/**
 * Staff - Chi tiết công việc
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_STAFF, ROLE_ADMIN]);

$task_type = $_GET['type'] ?? 'pending-bookings';
$page_title = 'Chi tiết công việc';

try {
    switch ($task_type) {
        case 'pending-bookings':
            // Booking chờ xác nhận
            $page_title = 'Booking chờ xác nhận';
            $stmt = $pdo->prepare("
                SELECT b.*, u.full_name, r.room_number, rt.type_name, 
                       DATEDIFF(b.check_in, CURDATE()) as days_until_checkin,
                       COUNT(p.id) as total_payments,
                       COALESCE(SUM(p.amount), 0) as total_paid
                FROM bookings b
                JOIN customers c ON b.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                JOIN room_types rt ON r.room_type_id = rt.id
                LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
                WHERE b.status = 'pending'
                GROUP BY b.id
                ORDER BY b.check_in ASC
            ");
            $stmt->execute();
            $tasks = $stmt->fetchAll();
            $task_template = 'pending-bookings';
            break;
            
        case 'occupied-rooms':
            // Phòng đang sử dụng
            $page_title = 'Phòng đang sử dụng';
            $stmt = $pdo->prepare("
                SELECT r.id, r.room_number, r.floor, rt.type_name, 
                       b.id as booking_id, b.check_out, u.full_name, u.email, u.phone,
                       b.adults, b.children, b.special_requests,
                       DATEDIFF(b.check_out, CURDATE()) as days_until_checkout
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                JOIN bookings b ON r.id = b.room_id
                JOIN customers c ON b.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE b.status = 'checked_in'
                ORDER BY b.check_out ASC, r.floor ASC, r.room_number ASC
            ");
            $stmt->execute();
            $tasks = $stmt->fetchAll();
            $task_template = 'occupied-rooms';
            break;
            
        case 'cleaning-rooms':
            // Phòng cần dọn
            $page_title = 'Phòng cần dọn dẫu';
            $stmt = $pdo->prepare("
                SELECT r.id, r.room_number, r.floor, rt.type_name, rt.base_price,
                       b.id as booking_id, b.check_out, u.full_name, u.phone,
                       b.adults, b.children
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                JOIN bookings b ON r.id = b.room_id
                JOIN customers c ON b.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                WHERE b.status = 'checked_out'
                AND DATE(b.check_out) = CURDATE()
                ORDER BY b.check_out DESC, r.floor ASC, r.room_number ASC
            ");
            $stmt->execute();
            $tasks = $stmt->fetchAll();
            $task_template = 'cleaning-rooms';
            break;
            
        case 'upcoming-checkins':
            // Booking sắp check-in (48 giờ)
            $page_title = 'Booking sắp check-in (48 giờ)';
            $stmt = $pdo->prepare("
                SELECT b.*, u.full_name, u.email, u.phone, r.room_number, rt.type_name,
                       DATEDIFF(b.check_in, CURDATE()) as days_until_checkin,
                       (b.total_amount * 0.3) as deposit_required
                FROM bookings b
                JOIN customers c ON b.customer_id = c.id
                JOIN users u ON c.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE b.status IN ('pending', 'confirmed')
                AND b.check_in BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 2 DAY)
                ORDER BY b.check_in ASC
            ");
            $stmt->execute();
            $tasks = $stmt->fetchAll();
            $task_template = 'upcoming-checkins';
            break;
            
        default:
            $tasks = [];
            $task_template = '';
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $tasks = [];
    $task_template = '';
}

?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-tasks"></i> <?php echo esc($page_title); ?>
            </h5>
            <a href="dashboard.php" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <?php if ($task_template === 'pending-bookings'): ?>
            <!-- Booking chờ xác nhận -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Booking</th>
                            <th>Khách hàng</th>
                            <th>Email/Phone</th>
                            <th>Phòng</th>
                            <th>Check-in</th>
                            <th>Đến trong</th>
                            <th>Thanh toán</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tasks) > 0): ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><strong><?php echo esc($task['booking_code']); ?></strong></td>
                                    <td><?php echo esc($task['full_name']); ?></td>
                                    <td>
                                        <small>
                                            <?php if (!empty($task['email'])): ?>
                                                <i class="fas fa-envelope"></i> <?php echo esc($task['email']); ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($task['phone'])): ?>
                                                <i class="fas fa-phone"></i> <?php echo esc($task['phone']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td><?php echo esc($task['room_number']); ?> (<?php echo esc($task['type_name']); ?>)</td>
                                    <td><?php echo formatDate($task['check_in']); ?></td>
                                    <td>
                                        <?php if ($task['days_until_checkin'] == 0): ?>
                                            <span class="badge bg-danger">Hôm nay</span>
                                        <?php elseif ($task['days_until_checkin'] == 1): ?>
                                            <span class="badge bg-warning">Ngày mai</span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?php echo $task['days_until_checkin']; ?> ngày</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo formatCurrency($task['total_paid']); ?> 
                                        <small class="text-muted">/ <?php echo formatCurrency($task['total_amount']); ?></small>
                                    </td>
                                    <td>
                                        <a href="../admin/bookings/view.php?id=<?php echo $task['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                        <a href="../admin/bookings/edit.php?id=<?php echo $task['id']; ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">Không có booking chờ xác nhận</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($task_template === 'occupied-rooms'): ?>
            <!-- Phòng đang sử dụng -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Phòng</th>
                            <th>Tầng</th>
                            <th>Loại</th>
                            <th>Khách hàng</th>
                            <th>Liên lạc</th>
                            <th>Check-out</th>
                            <th>Tình trạng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tasks) > 0): ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><strong><?php echo esc($task['room_number']); ?></strong></td>
                                    <td>Tầng <?php echo $task['floor']; ?></td>
                                    <td><?php echo esc($task['type_name']); ?></td>
                                    <td><?php echo esc($task['full_name']); ?></td>
                                    <td>
                                        <small>
                                            <i class="fas fa-envelope"></i> <?php echo esc($task['email']); ?><br>
                                            <i class="fas fa-phone"></i> <?php echo esc($task['phone'] ?? 'N/A'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo formatDate($task['check_out']); ?></strong>
                                        <?php if ($task['days_until_checkout'] == 0): ?>
                                            <br><span class="badge bg-danger">Hôm nay</span>
                                        <?php elseif ($task['days_until_checkout'] < 0): ?>
                                            <br><span class="badge bg-danger">Quá hạn <?php echo abs($task['days_until_checkout']); ?> ngày</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?php echo $task['adults']; ?> người lớn
                                            <?php if ($task['children'] > 0): ?>
                                                , <?php echo $task['children']; ?> trẻ em
                                            <?php endif; ?>
                                        </small>
                                        <?php if (!empty($task['special_requests'])): ?>
                                            <br><span class="badge bg-info">Yêu cầu: <?php echo esc(substr($task['special_requests'], 0, 20)); ?>...</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../admin/bookings/view.php?id=<?php echo $task['booking_id']; ?>" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">Không có phòng đang sử dụng</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($task_template === 'cleaning-rooms'): ?>
            <!-- Phòng cần dọn dẫu -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Phòng</th>
                            <th>Tầng</th>
                            <th>Loại</th>
                            <th>Khách trước</th>
                            <th>Số lượng khách</th>
                            <th>Check-out lúc</th>
                            <th>Ưu tiên</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tasks) > 0): ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr class="table-warning">
                                    <td><strong><?php echo esc($task['room_number']); ?></strong></td>
                                    <td>Tầng <?php echo $task['floor']; ?></td>
                                    <td><?php echo esc($task['type_name']); ?></td>
                                    <td><?php echo esc($task['full_name']); ?></td>
                                    <td><?php echo $task['adults']; ?> người lớn<?php if ($task['children'] > 0): ?>, <?php echo $task['children']; ?> trẻ<?php endif; ?></td>
                                    <td><?php echo formatDate($task['check_out'], 'H:i'); ?></td>
                                    <td>
                                        <span class="badge bg-success">Cần dọn</span>
                                    </td>
                                    <td>
                                        <a href="../admin/bookings/view.php?id=<?php echo $task['booking_id']; ?>" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết booking">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">Không có phòng cần dọn dẫu</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($task_template === 'upcoming-checkins'): ?>
            <!-- Booking sắp check-in -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Booking</th>
                            <th>Khách hàng</th>
                            <th>Liên lạc</th>
                            <th>Phòng</th>
                            <th>Check-in</th>
                            <th>Đến trong</th>
                            <th>Người</th>
                            <th>Cọc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($tasks) > 0): ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><strong><?php echo esc($task['booking_code']); ?></strong></td>
                                    <td><?php echo esc($task['full_name']); ?></td>
                                    <td>
                                        <small>
                                            <i class="fas fa-envelope"></i> <?php echo esc($task['email']); ?><br>
                                            <i class="fas fa-phone"></i> <?php echo esc($task['phone'] ?? 'N/A'); ?>
                                        </small>
                                    </td>
                                    <td><?php echo esc($task['room_number']); ?> (<?php echo esc($task['type_name']); ?>)</td>
                                    <td><?php echo formatDate($task['check_in']); ?></td>
                                    <td>
                                        <?php if ($task['days_until_checkin'] == 0): ?>
                                            <span class="badge bg-danger">Hôm nay</span>
                                        <?php elseif ($task['days_until_checkin'] == 1): ?>
                                            <span class="badge bg-warning">Ngày mai</span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?php echo $task['days_until_checkin']; ?> ngày</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $task['adults']; ?> người<?php if ($task['children'] > 0): ?> + <?php echo $task['children']; ?> trẻ<?php endif; ?></td>
                                    <td><?php echo formatCurrency($task['deposit_required']); ?></td>
                                    <td>
                                        <a href="../admin/bookings/view.php?id=<?php echo $task['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                                    <p class="text-muted mt-2">Không có booking sắp check-in</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="card-body text-center py-4">
                <p class="text-muted">Chọn một công việc từ dashboard để xem chi tiết</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
