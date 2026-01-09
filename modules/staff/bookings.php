<?php
/**
 * Staff - Danh sách bookings
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_STAFF, ROLE_ADMIN]);

$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

try {
    $query = "
        SELECT b.*, u.full_name, r.room_number, rt.type_name
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($status)) {
        $query .= " AND b.status = :status";
        $params['status'] = $status;
    }
    
    if (!empty($search)) {
        $query .= " AND (b.booking_code LIKE :search OR u.full_name LIKE :search OR r.room_number LIKE :search)";
        $params['search'] = "%{$search}%";
    }
    
    $query .= " ORDER BY b.check_in DESC LIMIT 50";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $bookings = [];
}

$page_title = 'Quản lý Bookings';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Quản lý Bookings</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Tìm booking code, khách, phòng..." 
                           value="<?php echo esc($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                        <option value="checked_in" <?php echo $status === 'checked_in' ? 'selected' : ''; ?>>Đã nhận phòng</option>
                        <option value="checked_out" <?php echo $status === 'checked_out' ? 'selected' : ''; ?>>Đã trả phòng</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã Booking</th>
                        <th>Khách hàng</th>
                        <th>Phòng</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><strong><?php echo esc($booking['booking_code']); ?></strong></td>
                                <td><?php echo esc($booking['full_name']); ?></td>
                                <td><?php echo esc($booking['room_number']); ?> (<?php echo esc($booking['type_name']); ?>)</td>
                                <td><?php echo formatDate($booking['check_in']); ?></td>
                                <td><?php echo formatDate($booking['check_out']); ?></td>
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
                                        <?php echo $status_texts[$booking['status']] ?? 'N/A'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../admin/bookings/view.php?id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">Không có booking nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
