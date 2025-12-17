<?php
/**
 * Danh sách phòng
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$page = max(1, $_GET['page'] ?? 1);
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

try {
    // Lấy danh sách phòng
    $query = "
        SELECT r.*, rt.type_name, rt.base_price
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND r.room_number LIKE :search";
        $params['search'] = "%{$search}%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND r.status = :status";
        $params['status'] = $status_filter;
    }
    
    if (!empty($type_filter)) {
        $query .= " AND r.room_type_id = :type_id";
        $params['type_id'] = $type_filter;
    }
    
    $query .= " ORDER BY r.floor, r.room_number";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
    
    // Lấy danh sách loại phòng
    $stmt = $pdo->prepare("SELECT * FROM room_types ORDER BY type_name");
    $stmt->execute();
    $room_types = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $rooms = [];
}

$page_title = 'Quản lý phòng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-bed"></i> Quản lý phòng</h5>
                </div>
                <div class="col-auto">
                    <a href="add.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Thêm phòng
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Bộ lọc -->
            <form method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tìm theo số phòng" 
                               value="<?php echo esc($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">-- Tất cả loại phòng --</option>
                            <?php foreach ($room_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>" 
                                        <?php echo $type_filter == $type['id'] ? 'selected' : ''; ?>>
                                    <?php echo esc($type['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?>>Trống</option>
                            <option value="occupied" <?php echo $status_filter == 'occupied' ? 'selected' : ''; ?>>Đã đặt</option>
                            <option value="cleaning" <?php echo $status_filter == 'cleaning' ? 'selected' : ''; ?>>Đang dọn</option>
                            <option value="maintenance" <?php echo $status_filter == 'maintenance' ? 'selected' : ''; ?>>Bảo trì</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                    </div>
                </div>
            </form>
            
            <!-- Bảng danh sách -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Số phòng</th>
                            <th>Loại phòng</th>
                            <th>Tầng</th>
                            <th>Giá cơ bản</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong><?php echo esc($room['room_number']); ?></strong></td>
                                <td><?php echo esc($room['type_name']); ?></td>
                                <td><?php echo $room['floor']; ?></td>
                                <td><?php echo formatCurrency($room['base_price']); ?></td>
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
                                <td>
                                    <a href="edit.php?id=<?php echo $room['id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $room['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn chắc chứ?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($rooms)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Không có phòng nào
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
