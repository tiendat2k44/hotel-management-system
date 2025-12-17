<?php
/**
 * Sửa phòng
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$room_id = $_GET['id'] ?? '';
$errors = [];
$room = null;

if (empty($room_id)) {
    redirect('index.php', 'Phòng không tồn tại', 'danger');
}

try {
    // Lấy thông tin phòng
    $stmt = $pdo->prepare("
        SELECT r.*, rt.type_name
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id = :id
    ");
    $stmt->execute(['id' => $room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        redirect('index.php', 'Phòng không tồn tại', 'danger');
    }
    
    // Lấy danh sách loại phòng
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE status = 'active' ORDER BY type_name");
    $stmt->execute();
    $room_types = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $room_types = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $room_number = trim($_POST['room_number'] ?? '');
    $room_type_id = $_POST['room_type_id'] ?? '';
    $floor = $_POST['floor'] ?? '';
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    if (empty($room_number)) {
        $errors[] = 'Số phòng không được để trống';
    }
    if (empty($room_type_id)) {
        $errors[] = 'Loại phòng không được để trống';
    }
    if (empty($floor)) {
        $errors[] = 'Tầng không được để trống';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE rooms
                SET room_number = :room_number, room_type_id = :room_type_id, 
                    floor = :floor, status = :status, notes = :notes
                WHERE id = :id
            ");
            
            $stmt->execute([
                'room_number' => $room_number,
                'room_type_id' => $room_type_id,
                'floor' => $floor,
                'status' => $status,
                'notes' => $notes,
                'id' => $room_id
            ]);
            
            setFlash('success', 'Cập nhật phòng thành công');
            logActivity($pdo, $_SESSION['user_id'], 'EDIT_ROOM', 'Sửa phòng ' . $room_number);
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Sửa phòng';
?>

<?php include_once '../../../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Sửa phòng <?php echo esc($room['room_number']); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle"></i> <?php echo esc($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="room_number" class="form-label">Số phòng *</label>
                                <input type="text" class="form-control" id="room_number" name="room_number" 
                                       value="<?php echo esc($_POST['room_number'] ?? $room['room_number']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="floor" class="form-label">Tầng *</label>
                                <input type="number" class="form-control" id="floor" name="floor" 
                                       value="<?php echo esc($_POST['floor'] ?? $room['floor']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="room_type_id" class="form-label">Loại phòng *</label>
                                <select class="form-select" id="room_type_id" name="room_type_id" required>
                                    <?php foreach ($room_types as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" 
                                                <?php echo ($_POST['room_type_id'] ?? $room['room_type_id']) == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo esc($type['type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="available" <?php echo ($_POST['status'] ?? $room['status']) == 'available' ? 'selected' : ''; ?>>Trống</option>
                                    <option value="occupied" <?php echo ($_POST['status'] ?? $room['status']) == 'occupied' ? 'selected' : ''; ?>>Đã đặt</option>
                                    <option value="cleaning" <?php echo ($_POST['status'] ?? $room['status']) == 'cleaning' ? 'selected' : ''; ?>>Đang dọn</option>
                                    <option value="maintenance" <?php echo ($_POST['status'] ?? $room['status']) == 'maintenance' ? 'selected' : ''; ?>>Bảo trì</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo esc($_POST['notes'] ?? $room['notes']); ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../../../includes/footer.php'; ?>
