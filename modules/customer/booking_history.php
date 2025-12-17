<?php
/**
 * Lịch sử booking
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$customer_id = $_SESSION['customer_id'];
$page = max(1, $_GET['page'] ?? 1);
$status_filter = $_GET['status'] ?? '';

try {
    // Lấy danh sách booking
    $query = "
        SELECT b.*, r.room_number, rt.type_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.customer_id = :customer_id
    ";
    
    $params = ['customer_id' => $customer_id];
    
    if (!empty($status_filter)) {
        $query .= " AND b.status = :status";
        $params['status'] = $status_filter;
    }
    
    $query .= " ORDER BY b.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $bookings = [];
}

$page_title = 'Lịch sử booking';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử booking</h5>
        </div>
        
        <div class="card-body">
            <!-- Bộ lọc -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-9">
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
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                </div>
            </form>
            
            <!-- Bảng danh sách -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã booking</th>
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
                                <td>
                                    <?php echo esc($booking['room_number']); ?><br>
                                    <small class="text-muted"><?php echo esc($booking['type_name']); ?></small>
                                </td>
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
                                    <a href="booking_detail.php?id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($bookings)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Bạn chưa có booking nào
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
