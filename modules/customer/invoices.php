<?php
/**
 * Trang hóa đơn - Khách hàng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$customer_id = $_SESSION['customer_id'];
$invoices = [];

try {
    // Lấy danh sách hóa đơn (từ booking)
    $stmt = $pdo->prepare("
        SELECT 
            b.id as booking_id,
            b.booking_code,
            b.check_in,
            b.check_out,
            r.room_number,
            rt.type_name,
            b.total_amount,
            b.created_at,
            COUNT(DISTINCT p.id) as payment_count,
            COALESCE(SUM(p.amount), 0) as paid_amount
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.customer_id = :customer_id AND b.status IN ('confirmed', 'checked_in', 'checked_out')
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ");
    $stmt->execute(['customer_id' => $customer_id]);
    $invoices = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$page_title = 'Hóa đơn của tôi';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Hóa đơn của tôi</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã booking</th>
                        <th>Phòng</th>
                        <th>Thời gian</th>
                        <th>Tổng tiền</th>
                        <th>Đã thanh toán</th>
                        <th>Còn lại</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($invoices) > 0): ?>
                        <?php foreach ($invoices as $invoice): 
                            $remaining = $invoice['total_amount'] - $invoice['paid_amount'];
                        ?>
                            <tr>
                                <td><strong><?php echo esc($invoice['booking_code']); ?></strong></td>
                                <td>
                                    <?php echo esc($invoice['room_number']); ?><br>
                                    <small class="text-muted"><?php echo esc($invoice['type_name']); ?></small>
                                </td>
                                <td>
                                    <?php echo formatDate($invoice['check_in']); ?> - <br>
                                    <?php echo formatDate($invoice['check_out']); ?>
                                </td>
                                <td><strong><?php echo formatCurrency($invoice['total_amount']); ?></strong></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo formatCurrency($invoice['paid_amount']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($remaining > 0): ?>
                                        <span class="badge bg-danger">
                                            <?php echo formatCurrency($remaining); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Đã thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="booking_detail.php?id=<?php echo $invoice['booking_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">Bạn chưa có hóa đơn nào</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại dashboard
        </a>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
