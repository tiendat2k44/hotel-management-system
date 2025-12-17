<?php
/**
 * Thêm phòng
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$errors = [];

try {
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
            // Kiểm tra số phòng đã tồn tại
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_number = :room_number");
            $stmt->execute(['room_number' => $room_number]);
            
            if ($stmt->fetch()['count'] > 0) {
                $errors[] = 'Số phòng này đã tồn tại';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO rooms (room_number, room_type_id, floor, status, notes)
                    VALUES (:room_number, :room_type_id, :floor, :status, :notes)
                ");
                
                $stmt->execute([
                    'room_number' => $room_number,
                    'room_type_id' => $room_type_id,
                    'floor' => $floor,
                    'status' => $status,
                    'notes' => $notes
                ]);
                
                setFlash('success', 'Thêm phòng thành công');
                logActivity($pdo, $_SESSION['user_id'], 'ADD_ROOM', 'Thêm phòng ' . $room_number);
                redirect('index.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Thêm phòng';
?>

<?php include_once '../../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Thêm phòng mới</h5>
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
                                       value="<?php echo esc($_POST['room_number'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="floor" class="form-label">Tầng *</label>
                                <input type="number" class="form-control" id="floor" name="floor" 
                                       value="<?php echo esc($_POST['floor'] ?? '1'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="room_type_id" class="form-label">Loại phòng *</label>
                                <select class="form-select" id="room_type_id" name="room_type_id" required>
                                    <option value="">-- Chọn loại phòng --</option>
                                    <?php foreach ($room_types as $type): ?>
                                        <option value="<?php echo $type['id']; ?>" 
                                                <?php echo $_POST['room_type_id'] == $type['id'] ? 'selected' : ''; ?>>
                                            <?php echo esc($type['type_name']); ?> - <?php echo formatCurrency($type['base_price']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="available" selected>Trống</option>
                                    <option value="occupied">Đã đặt</option>
                                    <option value="cleaning">Đang dọn</option>
                                    <option value="maintenance">Bảo trì</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo esc($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Thêm
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

<?php include_once '../../../includes/footer.php'; ?>
