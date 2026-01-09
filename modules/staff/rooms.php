<?php
/**
 * Staff - Quản lý phòng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_STAFF, ROLE_ADMIN]);

$status_filter = $_GET['status'] ?? '';
$floor_filter = $_GET['floor'] ?? '';

try {
    $query = "
        SELECT r.*, rt.type_name, rt.base_price,
               b.booking_code, b.check_out, u.full_name
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        LEFT JOIN bookings b ON r.id = b.room_id 
            AND b.status IN ('pending','confirmed','checked_in')
            AND b.check_out > NOW()
        LEFT JOIN customers c ON b.customer_id = c.id
        LEFT JOIN users u ON c.user_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($status_filter)) {
        $query .= " AND r.status = :status";
        $params['status'] = $status_filter;
    }
    
    if (!empty($floor_filter)) {
        $query .= " AND r.floor = :floor";
        $params['floor'] = $floor_filter;
    }
    
    $query .= " ORDER BY r.floor ASC, r.room_number ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
    
    // Lấy danh sách tầng
    $stmt = $pdo->query("SELECT DISTINCT floor FROM rooms ORDER BY floor ASC");
    $floors = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $rooms = [];
    $floors = [];
}

$page_title = 'Quản lý Phòng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-door-open"></i> Quản lý Phòng</h5>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Trống</option>
                        <option value="occupied" <?php echo $status_filter === 'occupied' ? 'selected' : ''; ?>>Đã đặt</option>
                        <option value="cleaning" <?php echo $status_filter === 'cleaning' ? 'selected' : ''; ?>>Đang dọn</option>
                        <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>Bảo trì</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="floor" class="form-select">
                        <option value="">-- Tất cả tầng --</option>
                        <?php foreach ($floors as $floor): ?>
                            <option value="<?php echo $floor; ?>" <?php echo $floor_filter == $floor ? 'selected' : ''; ?>>
                                Tầng <?php echo $floor; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Phòng</th>
                        <th>Loại</th>
                        <th>Tầng</th>
                        <th>Trạng thái</th>
                        <th>Booking</th>
                        <th>Khách hàng</th>
                        <th>Check-out</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rooms) > 0): ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong><?php echo esc($room['room_number']); ?></strong></td>
                                <td><?php echo esc($room['type_name']); ?></td>
                                <td>Tầng <?php echo $room['floor']; ?></td>
                                <td>
                                    <?php
                                    $status_colors = [
                                        'available' => 'success',
                                        'occupied' => 'info',
                                        'cleaning' => 'warning',
                                        'maintenance' => 'danger'
                                    ];
                                    $status_texts = [
                                        'available' => 'Trống',
                                        'occupied' => 'Đã đặt',
                                        'cleaning' => 'Đang dọn',
                                        'maintenance' => 'Bảo trì'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $status_colors[$room['status']] ?? 'secondary'; ?>">
                                        <?php echo $status_texts[$room['status']] ?? 'N/A'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($room['booking_code'])): ?>
                                        <strong><?php echo esc($room['booking_code']); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($room['full_name']) ? esc($room['full_name']) : '-'; ?></td>
                                <td>
                                    <?php if (!empty($room['check_out'])): ?>
                                        <?php echo formatDate($room['check_out']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="../admin/rooms/index.php?id=<?php echo $room['id']; ?>" 
                                       class="btn btn-sm btn-info" title="Xem chi tiết phòng">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">Không có phòng nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
