<?php
/**
 * Chi tiết booking - Khách hàng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$booking_id = $_GET['id'] ?? 0;
$booking = null;
$services_used = [];
$payment_history = [];

try {
    // Lấy thông tin booking
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, rt.type_name, rt.base_price, u.full_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        JOIN users u ON b.created_by = u.id
        WHERE b.id = :id AND b.customer_id = :customer_id
    ");
    $stmt->execute([
        'id' => $booking_id,
        'customer_id' => $_SESSION['customer_id']
    ]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        die('Booking không tồn tại hoặc bạn không có quyền truy cập');
    }
    
    // Khách tự xác nhận đã đến
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_arrived') {
        if (in_array($booking['status'], ['pending', 'confirmed'], true)) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'checked_in' WHERE id = :id");
            $stmt->execute(['id' => $booking_id]);
            logActivity($pdo, $_SESSION['user_id'], 'CHECKIN_SELF', 'Khách tự báo đã nhận phòng ' . $booking['booking_code']);
            setFlash('success', 'Bạn đã xác nhận đã đến nhận phòng.');
        } else {
            setFlash('warning', 'Booking không thể chuyển sang trạng thái đã nhận phòng.');
        }
        redirect('booking_detail.php?id=' . $booking_id);
    }
    
    // Lấy danh sách dịch vụ đã sử dụng
    $stmt = $pdo->prepare("
        SELECT su.*, s.service_name, s.price, s.unit
        FROM service_usage su
        JOIN services s ON su.service_id = s.id
        WHERE su.booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $booking_id]);
    $services_used = $stmt->fetchAll();
    
    // Lấy lịch sử thanh toán
    $stmt = $pdo->prepare("
        SELECT * FROM payments
        WHERE booking_id = :booking_id
        ORDER BY payment_date DESC
    ");
    $stmt->execute(['booking_id' => $booking_id]);
    $payment_history = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Lỗi: ' . $e->getMessage());
}

// Tính toán hóa đơn
$nights = calculateNights($booking['check_in'], $booking['check_out']);
$room_total = $nights * $booking['base_price'];
$service_total = array_sum(array_column($services_used, 'total_price'));
$subtotal = $room_total + $service_total;
$tax_amount = $subtotal * (VAT_RATE / 100);
$total_invoice = $subtotal + $tax_amount;
$remaining = $total_invoice - array_sum(array_column($payment_history, 'amount'));

$page_title = 'Chi tiết booking';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Thông tin booking -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0"><i class="fas fa-door-open"></i> Thông tin đặt phòng</h5>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-light text-dark"><?php echo esc($booking['booking_code']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-door-open"></i> Phòng:</strong> 
                                <?php echo esc($booking['room_number']); ?> - <?php echo esc($booking['type_name']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar"></i> Trạng thái:</strong>
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
                            </p>
                            <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                <form method="POST" class="mt-2">
                                    <input type="hidden" name="action" value="mark_arrived">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-walking"></i> Tôi đã đến nhận phòng
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-sign-in-alt"></i> Check-in:</strong> 
                                <?php echo formatDate($booking['check_in']); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-sign-out-alt"></i> Check-out:</strong> 
                                <?php echo formatDate($booking['check_out']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-users"></i> Số người:</strong> 
                                <?php echo $booking['adults']; ?> người lớn
                                <?php if ($booking['children'] > 0): ?>
                                    , <?php echo $booking['children']; ?> trẻ em
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-calendar-alt"></i> Số đêm:</strong> 
                                <?php echo $nights; ?> đêm
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($booking['special_requests'])): ?>
                        <div class="mb-3">
                            <p class="mb-2">
                                <strong><i class="fas fa-comment"></i> Yêu cầu đặc biệt:</strong>
                            </p>
                            <p class="text-muted"><?php echo esc($booking['special_requests']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Bảng giá tính hóa đơn -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Hóa đơn</h5>
                    <div class="btn-group" role="group">
                        <a href="invoice.php?id=<?php echo $booking_id; ?>&format=pdf" class="btn btn-sm btn-light" title="Xuất PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                        <a href="invoice.php?id=<?php echo $booking_id; ?>&format=excel" class="btn btn-sm btn-light" title="Xuất Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Phòng -->
                    <table class="table table-sm mb-3">
                        <tr>
                            <td><strong><?php echo esc($booking['type_name']); ?></strong> (<?php echo $nights; ?> đêm)</td>
                            <td class="text-end"><?php echo formatCurrency($booking['base_price']); ?>/đêm</td>
                            <td class="text-end"><strong><?php echo formatCurrency($room_total); ?></strong></td>
                        </tr>
                    </table>
                    
                    <!-- Dịch vụ sử dụng -->
                    <?php if (count($services_used) > 0): ?>
                        <h6 class="mb-2"><i class="fas fa-concierge-bell"></i> Dịch vụ sử dụng:</h6>
                        <table class="table table-sm mb-3">
                            <?php foreach ($services_used as $service): ?>
                                <tr>
                                    <td><?php echo esc($service['service_name']); ?> (x<?php echo $service['quantity']; ?>)</td>
                                    <td class="text-end"><?php echo formatCurrency($service['price']); ?>/<?php echo esc($service['unit']); ?></td>
                                    <td class="text-end"><?php echo formatCurrency($service['total_price']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <!-- Tính toán -->
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Cộng:</strong></td>
                            <td class="text-end"><strong><?php echo formatCurrency($subtotal); ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Thuế VAT (<?php echo VAT_RATE; ?>%):</strong></td>
                            <td class="text-end"><strong><?php echo formatCurrency($tax_amount); ?></strong></td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Tổng cộng:</strong></td>
                            <td class="text-end"><strong class="text-primary" style="font-size: 1.2em;"><?php echo formatCurrency($total_invoice); ?></strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Thanh toán & Hóa đơn -->
        <div class="col-md-4">
            <!-- Tóm tắt thanh toán -->
            <div class="card shadow mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-money-bill"></i> Thanh toán</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Tiền cọc:</strong><br>
                        <span class="text-info"><?php echo formatCurrency($booking['deposit_amount']); ?></span>
                    </p>
                    <p class="mb-2">
                        <strong>Tổng hóa đơn:</strong><br>
                        <span class="text-primary" style="font-size: 1.2em;"><?php echo formatCurrency($total_invoice); ?></span>
                    </p>
                    <p class="mb-3 pb-3 border-bottom">
                        <strong>Còn phải thanh toán:</strong><br>
                        <span class="text-danger" style="font-size: 1.1em;">
                            <i class="fas fa-exclamation-circle"></i> 
                            <?php echo formatCurrency($remaining); ?>
                        </span>
                    </p>
                    
                    <?php if ($remaining > 0): ?>
                        <a class="btn btn-warning w-100 mb-2" href="payment_confirmation.php?booking_id=<?php echo $booking_id; ?>&payment_method=bank_transfer">
                            <i class="fas fa-university"></i> Thanh toán chuyển khoản
                        </a>
                        <small class="text-muted d-block text-center mb-2">Chuyển khoản trước khi nhận phòng để xác nhận nhanh.</small>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Đã thanh toán đầy đủ
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Lịch sử thanh toán -->
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Lịch sử thanh toán</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (count($payment_history) > 0): ?>
                        <?php foreach ($payment_history as $payment): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <p class="mb-1">
                                    <strong><?php echo formatCurrency($payment['amount']); ?></strong>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> 
                                    <?php echo formatDate($payment['payment_date']); ?>
                                </small><br>
                                <small class="text-muted">
                                    <i class="fas fa-credit-card"></i> 
                                    <?php
                                    $methods = ['cash' => 'Tiền mặt', 'bank_transfer' => 'Chuyển khoản', 'credit_card' => 'Thẻ tín dụng'];
                                    echo $methods[$payment['payment_method']] ?? 'Khác';
                                    ?>
                                </small><br>
                                <small class="badge bg-<?php echo $payment['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo $payment['status'] === 'completed' ? 'Hoàn thành' : 'Chờ xử lý'; ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">Chưa có thanh toán</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại dashboard
            </a>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
