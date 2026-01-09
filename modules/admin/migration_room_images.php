<?php
/**
 * Migration Tool - Thêm image_url cho rooms
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['run_migration'])) {
    try {
        // Kiểm tra xem cột đã tồn tại chưa
        $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'image_url'");
        if ($stmt->rowCount() > 0) {
            $messages[] = 'Cột image_url đã tồn tại trong bảng rooms';
        } else {
            // Thêm cột image_url
            $pdo->exec("ALTER TABLE rooms ADD COLUMN image_url VARCHAR(500) AFTER notes");
            $messages[] = '✓ Đã thêm cột image_url vào bảng rooms';
        }
        
        // Cập nhật một số phòng mẫu với ảnh
        $images = [
            'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800',
            'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800',
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800',
            'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800',
            'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800'
        ];
        
        $stmt = $pdo->query("SELECT id, room_number FROM rooms WHERE image_url IS NULL OR image_url = '' LIMIT 10");
        $rooms = $stmt->fetchAll();
        
        $count = 0;
        foreach ($rooms as $room) {
            $image_url = $images[$count % count($images)];
            $stmt = $pdo->prepare("UPDATE rooms SET image_url = :image_url WHERE id = :id");
            $stmt->execute(['image_url' => $image_url, 'id' => $room['id']]);
            $messages[] = "  - Cập nhật ảnh cho phòng {$room['room_number']}";
            $count++;
        }
        
        $messages[] = "✓ Đã cập nhật $count phòng với hình ảnh mẫu";
        $messages[] = '<strong>Migration hoàn thành thành công!</strong>';
        
        logActivity($pdo, $_SESSION['user_id'], 'RUN_MIGRATION', 'Chạy migration add_room_images');
        
    } catch (PDOException $e) {
        $errors[] = 'Lỗi: ' . $e->getMessage();
    }
}

$page_title = 'Migration Tool';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-database"></i> Database Migration Tool</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($messages)): ?>
                        <div class="alert alert-success">
                            <?php foreach ($messages as $message): ?>
                                <div><?php echo $message; ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="<?php echo ADMIN_URL; ?>rooms/" class="btn btn-success">
                                <i class="fas fa-bed"></i> Đi tới Quản lý phòng
                            </a>
                        </div>
                    <?php else: ?>
                        <h6 class="mb-3">Migration: Thêm hình ảnh cho phòng</h6>
                        <p>Migration này sẽ:</p>
                        <ul>
                            <li>Thêm cột <code>image_url</code> vào bảng <code>rooms</code></li>
                            <li>Cập nhật hình ảnh mẫu cho các phòng hiện có (tối đa 10 phòng)</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Lưu ý:</strong> Việc thay đổi cấu trúc database không thể hoàn tác. 
                            Đảm bảo bạn đã backup database trước khi chạy migration.
                        </div>
                        
                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" name="run_migration" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play"></i> Chạy Migration
                                </button>
                                <a href="<?php echo ADMIN_URL; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
