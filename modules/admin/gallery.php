<?php
/**
 * Thư viện ảnh phòng - Admin quản lý
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

try {
    // Lấy danh sách phòng có ảnh
    $stmt = $pdo->prepare("
        SELECT r.*, rt.type_name, rt.base_price, rt.capacity
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.image_url IS NOT NULL AND r.image_url != ''
        ORDER BY r.floor, r.room_number
    ");
    $stmt->execute();
    $rooms = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $rooms = [];
}

$page_title = 'Thư viện ảnh phòng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-images"></i> Thư viện ảnh phòng</h2>
                <a href="<?php echo ADMIN_URL; ?>rooms/" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <?php if (empty($rooms)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Chưa có phòng nào có hình ảnh
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm" style="cursor: pointer; transition: all 0.3s;">
                        <!-- Hình ảnh -->
                        <div style="height: 250px; overflow: hidden; background-color: #f0f0f0; position: relative;">
                            <?php if (strpos($room['image_url'], 'http') === 0): ?>
                                <img src="<?php echo esc($room['image_url']); ?>" 
                                     alt="<?php echo esc($room['room_number']); ?>" 
                                     class="w-100 h-100"
                                     style="object-fit: cover; transition: transform 0.3s;"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal" 
                                     onclick="showImage('<?php echo esc($room['image_url']); ?>', '<?php echo esc($room['room_number']); ?>')"
                                     onmouseover="this.style.transform='scale(1.05)'" 
                                     onmouseout="this.style.transform='scale(1)'">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL . esc($room['image_url']); ?>" 
                                     alt="<?php echo esc($room['room_number']); ?>" 
                                     class="w-100 h-100"
                                     style="object-fit: cover; transition: transform 0.3s;"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#imageModal" 
                                     onclick="showImage('<?php echo BASE_URL . esc($room['image_url']); ?>', '<?php echo esc($room['room_number']); ?>')"
                                     onmouseover="this.style.transform='scale(1.05)'" 
                                     onmouseout="this.style.transform='scale(1)'"
                                     onerror="this.src='<?php echo BASE_URL; ?>assets/images/no-image.png'">
                            <?php endif; ?>
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge bg-<?php echo strpos($room['image_url'], 'http') === 0 ? 'info' : 'success'; ?>">
                                    <i class="fas fa-<?php echo strpos($room['image_url'], 'http') === 0 ? 'globe' : 'server'; ?>"></i>
                                    <?php echo strpos($room['image_url'], 'http') === 0 ? 'URL' : 'Server'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title mb-2">
                                <i class="fas fa-door-open text-primary"></i> 
                                <a href="<?php echo ADMIN_URL; ?>rooms/view.php?id=<?php echo $room['id']; ?>">
                                    Phòng <?php echo esc($room['room_number']); ?>
                                </a>
                            </h6>
                            
                            <div class="mb-2">
                                <small class="text-muted d-block">
                                    <strong><?php echo esc($room['type_name']); ?></strong> - Tầng <?php echo $room['floor']; ?>
                                </small>
                                <small class="text-success fw-bold">
                                    <?php echo formatCurrency($room['base_price']); ?>/đêm
                                </small>
                            </div>
                            
                            <div class="d-flex gap-1">
                                <a href="<?php echo ADMIN_URL; ?>rooms/edit.php?id=<?php echo $room['id']; ?>" 
                                   class="btn btn-sm btn-warning flex-grow-1">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <button class="btn btn-sm btn-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#imageModal"
                                        onclick="<?php if (strpos($room['image_url'], 'http') === 0): ?>showImage('<?php echo esc($room['image_url']); ?>', '<?php echo esc($room['room_number']); ?>')<?php else: ?>showImage('<?php echo BASE_URL . esc($room['image_url']); ?>', '<?php echo esc($room['room_number']); ?>')<?php endif; ?>">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem ảnh lớn -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageTitle">Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid rounded" style="max-height: 600px;">
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 8px;
    overflow: hidden;
}

.card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-4px);
}
</style>

<script>
function showImage(src, roomNumber) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageTitle').textContent = 'Phòng ' + roomNumber;
}
</script>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
