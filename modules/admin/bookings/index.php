<?php
/**
 * Danh sách booking
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$page = max(1, $_GET['page'] ?? 1);
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

try {
    // Lấy danh sách booking
    $query = "
        SELECT b.*, u.full_name, r.room_number, rt.type_name, c.id as customer_id
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (b.booking_code LIKE :search OR u.full_name LIKE :search)";
        $params['search'] = "%{$search}%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND b.status = :status";
        $params['status'] = $status_filter;
    }
    
    $query .= " ORDER BY b.created_at DESC LIMIT 20";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $bookings = [];
}

$page_title = 'Quản lý đặt phòng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Quản lý đặt phòng</h5>
                </div>
                <div class="col-auto">
                    <a href="create.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Tạo booking
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Bộ lọc -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm theo mã booking hoặc tên khách" 
                               value="<?php echo esc($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="checked_in" <?php echo $status_filter == 'checked_in' ? 'selected' : ''; ?>>Đã nhận phòng</option>
                            <option value="checked_out" <?php echo $status_filter == 'checked_out' ? 'selected' : ''; ?>>Đã trả phòng</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                    </div>
                </div>
            </form>
            
            <!-- Bảng danh sách -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã booking</th>
                            <th>Khách hàng</th>
                            <th>Phòng</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><strong><?php echo esc($booking['booking_code']); ?></strong></td>
                                <td><?php echo esc($booking['full_name']); ?></td>
                                <td><?php echo esc($booking['room_number']); ?> (<?php echo esc($booking['type_name']); ?>)</td>
                                <td><?php echo formatDate($booking['check_in']); ?></td>
                                <td><?php echo formatDate($booking['check_out']); ?></td>
                                <td><?php echo formatCurrency($booking['total_amount']); ?></td>
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
                                    <a href="view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Không có booking nào
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
