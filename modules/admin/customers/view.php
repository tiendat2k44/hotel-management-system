<?php
/**
 * Xem chi tiết khách hàng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$customer_id = $_GET['id'] ?? 0;
$customer = null;
$bookings = [];

try {
    // Lấy thông tin khách hàng
    $stmt = $pdo->prepare("
        SELECT c.*, u.id as user_id, u.username, u.email, u.phone, u.full_name, u.status, u.created_at as user_created_at
        FROM customers c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = :id
    ");
    $stmt->execute(['id' => $customer_id]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        die('Khách hàng không tồn tại');
    }
    
    // Lấy danh sách booking của khách hàng
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, rt.type_name, rt.base_price
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE b.customer_id = :customer_id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute(['customer_id' => $customer_id]);
    $bookings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Lỗi: ' . $e->getMessage());
}

$page_title = 'Chi tiết khách hàng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-4">
            <!-- Thông tin khách hàng -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Tên:</strong> <?php echo esc($customer['full_name']); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Username:</strong> <?php echo esc($customer['username']); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong> <a href="mailto:<?php echo esc($customer['email']); ?>"><?php echo esc($customer['email']); ?></a>
                    </p>
                    <p class="mb-2">
                        <strong>Điện thoại:</strong> <?php echo esc($customer['phone'] ?? 'Chưa cập nhật'); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Trạng thái:</strong>
                        <span class="badge bg-<?php echo $customer['status'] == 'active' ? 'success' : 'danger'; ?>">
                            <?php echo $customer['status'] == 'active' ? 'Hoạt động' : 'Vô hiệu hóa'; ?>
                        </span>
                    </p>
                    <hr>
                    <p class="mb-2">
                        <strong>Ngày sinh:</strong> <?php echo formatDate($customer['date_of_birth'] ?? 'Chưa cập nhật'); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Quốc tịch:</strong> <?php echo esc($customer['nationality'] ?? 'Chưa cập nhật'); ?>
                    </p>
                    <p class="mb-2">
                        <strong>CCCD:</strong> <?php echo esc($customer['id_card'] ?? 'Chưa cập nhật'); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Hộ chiếu:</strong> <?php echo esc($customer['passport'] ?? 'Chưa cập nhật'); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Địa chỉ:</strong> <?php echo esc($customer['address'] ?? 'Chưa cập nhật'); ?>
                    </p>
                    <p class="mb-3">
                        <strong>Ghi chú:</strong> <?php echo esc($customer['notes'] ?? '-'); ?>
                    </p>
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Lịch sử booking -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar"></i> Lịch sử booking (<?php echo count($bookings); ?>)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã booking</th>
                                <th>Phòng</th>
                                <th>Check-in / Check-out</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><strong><?php echo esc($booking['booking_code']); ?></strong></td>
                                        <td>
                                            <?php echo esc($booking['room_number']); ?><br>
                                            <small class="text-muted"><?php echo esc($booking['type_name']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo formatDate($booking['check_in']); ?><br>
                                            đến <?php echo formatDate($booking['check_out']); ?>
                                        </td>
                                        <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'pending' => 'warning',
                                                'confirmed' => 'info',
                                                'checked_in' => 'success',
                                                'checked_out' => 'secondary',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_texts = [
                                                'pending' => 'Chờ xác nhận',
                                                'confirmed' => 'Đã xác nhận',
                                                'checked_in' => 'Đã nhận phòng',
                                                'checked_out' => 'Đã trả phòng',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_badges[$booking['status']] ?? 'secondary'; ?>">
                                                <?php echo $status_texts[$booking['status']] ?? 'Không xác định'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="../../admin/bookings/view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">Khách hàng chưa có booking nào</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
