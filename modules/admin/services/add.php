<?php
/**
 * Thêm dịch vụ
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$errors = [];

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
                INSERT INTO services (service_name, description, price, unit, status)
                VALUES (:service_name, :description, :price, :unit, :status)
            ");
            
            $stmt->execute([
                'service_name' => $service_name,
                'description' => $description,
                'price' => $price,
                'unit' => $unit,
                'status' => $status
            ]);
            
            setFlash('success', 'Thêm dịch vụ thành công');
            logActivity($pdo, $_SESSION['user_id'], 'ADD_SERVICE', 'Thêm dịch vụ ' . $service_name);
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Thêm dịch vụ';
?>

<?php include_once '../../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Thêm dịch vụ mới</h5>
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
                                   value="<?php echo esc($_POST['service_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?php echo esc($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Giá *</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo esc($_POST['price'] ?? ''); ?>" 
                                       step="10000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit" class="form-label">Đơn vị *</label>
                                <input type="text" class="form-control" id="unit" name="unit" 
                                       value="<?php echo esc($_POST['unit'] ?? 'lần'); ?>" 
                                       placeholder="lần, giờ, ngày" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" selected>Hoạt động</option>
                                <option value="inactive">Vô hiệu hóa</option>
                            </select>
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
