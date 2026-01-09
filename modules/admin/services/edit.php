<?php
/**
 * Chỉnh sửa dịch vụ
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$service_id = $_GET['id'] ?? 0;
$service = null;
$errors = [];

try {
    // Lấy thông tin dịch vụ
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute(['id' => $service_id]);
    $service = $stmt->fetch();
    
    if (!$service) {
        die('Dịch vụ không tồn tại');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Lỗi: ' . $e->getMessage());
}

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_name = trim($_POST['service_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $unit = trim($_POST['unit'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Validate
    if (empty($service_name)) {
        $errors[] = 'Tên dịch vụ không được để trống';
    }
    if (empty($price) || $price <= 0) {
        $errors[] = 'Giá dịch vụ phải lớn hơn 0';
    }
    if (empty($unit)) {
        $errors[] = 'Đơn vị không được để trống';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE services 
                SET service_name = :service_name, 
                    description = :description, 
                    price = :price, 
                    unit = :unit, 
                    status = :status
                WHERE id = :id
            ");
            
            $stmt->execute([
                'service_name' => $service_name,
                'description' => $description,
                'price' => $price,
                'unit' => $unit,
                'status' => $status,
                'id' => $service_id
            ]);
            
            setFlash('success', 'Cập nhật dịch vụ thành công');
            logActivity($pdo, $_SESSION['user_id'], 'EDIT_SERVICE', 'Sửa dịch vụ ' . $service_name);
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
    
    // Cập nhật dữ liệu hiển thị
    $service = [
        'id' => $service_id,
        'service_name' => $service_name,
        'description' => $description,
        'price' => $price,
        'unit' => $unit,
        'status' => $status
    ];
}

$page_title = 'Chỉnh sửa dịch vụ';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Chỉnh sửa dịch vụ</h5>
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
                        <div class="mb-3">
                            <label for="service_name" class="form-label">Tên dịch vụ *</label>
                            <input type="text" class="form-control" id="service_name" name="service_name" 
                                   value="<?php echo esc($service['service_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo esc($service['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Giá (VND) *</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo esc($service['price']); ?>" min="0" step="1000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit" class="form-label">Đơn vị (vd: lần, ngày, buổi) *</label>
                                <input type="text" class="form-control" id="unit" name="unit" 
                                       value="<?php echo esc($service['unit']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo ($service['status'] === 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="inactive" <?php echo ($service['status'] === 'inactive') ? 'selected' : ''; ?>>Vô hiệu hóa</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
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

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
