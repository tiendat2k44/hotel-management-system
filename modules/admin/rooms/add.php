<?php
/**
 * Thêm phòng
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

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
    $image_url = '';
    
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
    
    // Xử lý upload file hình ảnh
    if (!empty($_FILES['room_image']['name'])) {
        $file = $_FILES['room_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Định dạng hình ảnh không hợp lệ. Chỉ hỗ trợ JPG, PNG, GIF, WebP';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Kích thước hình ảnh quá lớn (tối đa 5MB)';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Lỗi upload file: ' . $file['error'];
        } else {
            $upload_dir = UPLOAD_PATH . 'rooms/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'room_' . time() . '_' . uniqid() . '.' . $ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $image_url = 'assets/uploads/rooms/' . $filename;
            } else {
                $errors[] = 'Lỗi khi lưu file hình ảnh';
            }
        }
    } elseif (!empty($_POST['image_url_external'])) {
        $image_url = trim($_POST['image_url_external']);
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
                    INSERT INTO rooms (room_number, room_type_id, floor, status, notes, image_url)
                    VALUES (:room_number, :room_type_id, :floor, :status, :notes, :image_url)
                ");
                
                $stmt->execute([
                    'room_number' => $room_number,
                    'room_type_id' => $room_type_id,
                    'floor' => $floor,
                    'status' => $status,
                    'notes' => $notes,
                    'image_url' => $image_url
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

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

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
                    
                    <form method="POST" enctype="multipart/form-data">
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
                        
                        <!-- Hình ảnh phòng -->
                        <div class="mb-4">
                            <h6 class="mb-3"><i class="fas fa-image"></i> Hình ảnh phòng</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="room_image" class="form-label">Upload hình ảnh (JPG, PNG, GIF, WebP, tối đa 5MB)</label>
                                    <input type="file" class="form-control" id="room_image" name="room_image" 
                                           accept="image/jpeg,image/png,image/gif,image/webp">
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i> Preview hình ảnh sẽ hiển thị dưới đây
                                    </small>
                                    <div id="image_preview" class="mt-3">
                                        <!-- Preview sẽ hiển thị ở đây -->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="image_url_external" class="form-label">Hoặc nhập URL hình ảnh từ internet</label>
                                    <input type="url" class="form-control" id="image_url_external" name="image_url_external" 
                                           placeholder="https://example.com/room-image.jpg">
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle"></i> Bỏ qua nếu upload file ở phía trái
                                    </small>
                                </div>
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
                    
                    <script>
                    document.getElementById('room_image').addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        const preview = document.getElementById('image_preview');
                        
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                preview.innerHTML = `
                                    <div class="border rounded p-2">
                                        <img src="${event.target.result}" class="img-fluid rounded" style="max-height: 200px;">
                                        <small class="text-muted d-block mt-2">Kích thước file: ${(file.size / 1024).toFixed(2)} KB</small>
                                    </div>
                                `;
                            };
                            reader.readAsDataURL(file);
                        } else {
                            preview.innerHTML = '';
                        }
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
