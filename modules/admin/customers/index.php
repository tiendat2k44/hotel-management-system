<?php
/**
 * Danh sách khách hàng
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$page = max(1, $_GET['page'] ?? 1);
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

try {
    // Lấy danh sách khách hàng
    $query = "
        SELECT c.*, u.id as user_id, u.username, u.email, u.phone, u.full_name, u.status
        FROM customers c
        JOIN users u ON c.user_id = u.id
        WHERE u.role = 'customer'
    ";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " AND (u.full_name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
        $params['search'] = "%{$search}%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND u.status = :status";
        $params['status'] = $status_filter;
    }
    
    $query .= " ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $customers = [];
}

$page_title = 'Quản lý khách hàng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Quản Lý Khách Hàng</li>
        </ol>
    </nav>
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Quản lý khách hàng</h5>
                </div>
                <div class="col-auto">
                    <a href="add.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Thêm khách hàng
                    </a>
                </div>
            </div>
        </div>

        <!-- Bộ lọc tìm kiếm -->
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, email, điện thoại..." 
                           value="<?php echo esc($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="active" <?php echo ($status_filter === 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                        <option value="inactive" <?php echo ($status_filter === 'inactive') ? 'selected' : ''; ?>>Vô hiệu hóa</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>

        <!-- Danh sách khách hàng -->
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên khách hàng</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>CCCD/Hộ chiếu</th>
                        <th>Quốc tịch</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc($customer['full_name']); ?></strong><br>
                                    <small class="text-muted">@<?php echo esc($customer['username']); ?></small>
                                </td>
                                <td><?php echo esc($customer['email']); ?></td>
                                <td><?php echo esc($customer['phone'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($customer['id_card'])) {
                                        echo esc($customer['id_card']);
                                    } elseif (!empty($customer['passport'])) {
                                        echo esc($customer['passport']);
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc($customer['nationality'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $customer['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $customer['status'] == 'active' ? 'Hoạt động' : 'Vô hiệu hóa'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($customer['created_at']); ?></td>
                                <td>
                                    <a href="view.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" title="Xóa"
                                       onclick="return confirm('Bạn chắc chứ? Hành động này không thể hoàn tác!');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">Không có khách hàng nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
