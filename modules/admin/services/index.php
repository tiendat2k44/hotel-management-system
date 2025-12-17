<?php
/**
 * Danh sách dịch vụ
 */

require_once '../../../../config/database.php';
require_once '../../../../config/constants.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

try {
    // Lấy danh sách dịch vụ
    $stmt = $pdo->prepare("
        SELECT * FROM services ORDER BY service_name
    ");
    $stmt->execute();
    $services = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $services = [];
}

$page_title = 'Quản lý dịch vụ';
?>

<?php include_once '../../../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-concierge-bell"></i> Quản lý dịch vụ</h5>
                </div>
                <div class="col-auto">
                    <a href="add.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Thêm dịch vụ
                    </a>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Tên dịch vụ</th>
                        <th>Mô tả</th>
                        <th>Giá</th>
                        <th>Đơn vị</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><strong><?php echo esc($service['service_name']); ?></strong></td>
                            <td><?php echo esc($service['description']); ?></td>
                            <td><?php echo formatCurrency($service['price']); ?></td>
                            <td><?php echo esc($service['unit']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $service['status'] == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo $service['status'] == 'active' ? 'Hoạt động' : 'Vô hiệu hóa'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn chắc chứ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($services)): ?>
            <div class="card-body">
                <div class="alert alert-info text-center mb-0">
                    <i class="fas fa-info-circle"></i> Không có dịch vụ nào
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../../../includes/footer.php'; ?>
