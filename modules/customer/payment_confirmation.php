<?php
/**
 * Trang xác nhận thanh toán booking - Khách hàng
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$booking_id = $_GET['booking_id'] ?? 0;
$payment_method = $_GET['payment_method'] ?? 'cash';

$booking = null;
$customer_id = $_SESSION['customer_id'];
$errors = [];
$success = false;

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
        'customer_id' => $customer_id
    ]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        die('Booking không tồn tại');
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Lỗi: ' . $e->getMessage());
}

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_type = $_POST['payment_type'] ?? 'deposit';
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    if ($amount <= 0) {
        $errors[] = 'Số tiền phải lớn hơn 0';
    }
    
    $nights = calculateNights($booking['check_in'], $booking['check_out']);
    $deposit_required = calculateDeposit($booking['base_price'], $nights);
    $total_invoice = calculateInvoiceTotal($booking['total_amount']);
    
    // Validate theo loại thanh toán
    if ($payment_type === 'deposit') {
        // Cọc phải bằng 30% tổng tiền phòng
        if (abs($amount - $deposit_required) > 0.01) {
            $errors[] = 'Tiền cọc phải bằng 30% giá phòng (' . formatCurrency($deposit_required) . ')';
        }
    } elseif ($payment_type === 'final') {
        // Thanh toán final phải bằng tổng hóa đơn
        if (abs($amount - $total_invoice) > 0.01) {
            $errors[] = 'Thanh toán cuối cùng phải bằng tổng hóa đơn (' . formatCurrency($total_invoice) . ')';
        }
    }
    
    if (empty($errors)) {
        try {
            // Tạo record thanh toán
            $payment_code = 'PAY-' . date('YmdHis') . '-' . $booking_id;
            
            $stmt = $pdo->prepare("
                INSERT INTO payments (
                    payment_code, booking_id, amount, payment_method, payment_type, notes, status, processed_by
                ) VALUES (
                    :payment_code, :booking_id, :amount, :payment_method, :payment_type, :notes, :status, :processed_by
                )
            ");
            
            $stmt->execute([
                'payment_code' => $payment_code,
                'booking_id' => $booking_id,
                'amount' => $amount,
                'payment_method' => $payment_method,
                'payment_type' => $payment_type,
                'notes' => $notes,
                'status' => 'completed',
                'processed_by' => $_SESSION['user_id']
            ]);
            
            // Cập nhật trạng thái booking nếu thanh toán cuối cùng
            if ($payment_type === 'final') {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
                $stmt->execute(['id' => $booking_id]);
                $msg = 'Thanh toán thành công! Booking đã được xác nhận.';
            } else {
                $msg = 'Thanh toán cọc thành công! Vui lòng thanh toán phần còn lại khi nhận phòng.';
            }
            
            logActivity($pdo, $_SESSION['user_id'], 'PAYMENT', 'Thanh toán (' . $payment_type . ') booking ' . $booking['booking_code']);
            
            setFlash('success', $msg);
            redirect('booking_detail.php?id=' . $booking_id);
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
            error_log($e->getMessage());
        }
    }
}

$nights = calculateNights($booking['check_in'], $booking['check_out']);
$subtotal = $booking['total_amount'];
$tax = $subtotal * (VAT_RATE / 100);
$total_invoice = calculateInvoiceTotal($subtotal);
$deposit_required = calculateDeposit($booking['base_price'], $nights);

$page_title = 'Xác nhận thanh toán';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Thông tin booking -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Thông tin đặt phòng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Mã booking:</strong> <span class="badge bg-primary"><?php echo esc($booking['booking_code']); ?></span></p>
                            <p class="mb-2"><strong>Phòng:</strong> <?php echo esc($booking['room_number']); ?> (<?php echo esc($booking['type_name']); ?>)</p>
                            <p class="mb-2"><strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?></p>
                            <p class="mb-2"><strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Số người:</strong> <?php echo $booking['adults']; ?> người lớn<?php if ($booking['children'] > 0): ?>, <?php echo $booking['children']; ?> trẻ em<?php endif; ?></p>
                            <p class="mb-2"><strong>Số đêm:</strong> <?php echo $nights; ?></p>
                            <p class="mb-2"><strong>Giá/đêm:</strong> <?php echo formatCurrency($booking['base_price']); ?></p>
                            <p class="mb-2"><strong>Trạng thái:</strong> <span class="badge bg-warning">Chờ xác nhận</span></p>
                        </div>
                    </div>
                    <?php if (!empty($booking['special_requests'])): ?>
                        <hr>
                        <p><strong>Yêu cầu đặc biệt:</strong> <?php echo esc($booking['special_requests']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Form thanh toán -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-credit-card"></i> Xác nhận thanh toán</h5>
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
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="payment_type" class="form-label">Loại thanh toán *</label>
                                <select class="form-select" id="payment_type" name="payment_type" required onchange="updatePaymentAmount()">
                                    <option value="deposit">Thanh toán tiền cọc (30%)</option>
                                    <option value="final">Thanh toán cuối cùng</option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    <strong>Tiền cọc (30%):</strong> <?php echo formatCurrency($deposit_required); ?><br>
                                    <strong>Thanh toán cuối:</strong> <?php echo formatCurrency($total_invoice); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Phương thức thanh toán</label>
                                <input type="text" class="form-control" value="<?php echo getPaymentMethodLabel($payment_method); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Số tiền thanh toán (VND) *</label>
                            <input type="number" class="form-control form-control-lg" id="amount" name="amount" 
                                   value="<?php echo intval($deposit_required); ?>" 
                                   min="0" step="1000" required readonly>
                            <small class="text-muted d-block mt-1" id="amount_info">
                                Hệ thống sẽ tự động tính tiền dựa trên loại thanh toán bạn chọn.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Thêm ghi chú nếu cần..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Phương thức thanh toán:</strong> 
                            <?php 
                                if ($payment_method === 'cash') {
                                    echo 'Bạn sẽ thanh toán tiền mặt khi đến khách sạn.';
                                } elseif ($payment_method === 'bank_transfer') {
                                    echo 'Vui lòng chuyển khoản theo thông tin sau: <strong>Ngân hàng ABC - TK: 123456789</strong>';
                                } else {
                                    echo 'Bạn sẽ nhập thông tin thẻ trên trang thanh toán tiếp theo.';
                                }
                            ?>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Xác nhận thanh toán
                            </button>
                            <a href="booking_detail.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tóm tắt hóa đơn -->
        <div class="col-md-4">
            <div class="card shadow border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-receipt"></i> Hóa đơn</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td>Phòng <?php echo $nights; ?> đêm:</td>
                            <td class="text-end fw-bold"><?php echo formatCurrency($subtotal); ?></td>
                        </tr>
                        <tr>
                            <td>Thuế VAT (10%):</td>
                            <td class="text-end"><?php echo formatCurrency($tax); ?></td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Tổng cộng:</strong></td>
                            <td class="text-end"><strong class="text-primary"><?php echo formatCurrency($total_invoice); ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr class="table-light">
                            <td>Đã thanh toán:</td>
                            <td class="text-end text-success fw-bold">0 ₫</td>
                        </tr>
                        <tr class="table-light">
                            <td>Còn phải thanh toán:</td>
                            <td class="text-end text-danger fw-bold"><?php echo formatCurrency($total_invoice); ?></td>
                        </tr>
                    </table>
                    
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-shield-alt"></i> <strong>An toàn</strong><br>
                        <small>Tất cả giao dịch được bảo vệ bằng mã hóa SSL</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePaymentAmount() {
    const paymentType = document.getElementById('payment_type').value;
    const amountInput = document.getElementById('amount');
    const amountInfo = document.getElementById('amount_info');
    
    const baseAmount = <?php echo $booking['total_amount']; ?>;
    const totalInvoice = <?php echo $total_invoice; ?>;
    const depositAmount = <?php echo $deposit_required; ?>;
    
    if (paymentType === 'deposit') {
        amountInput.value = Math.round(depositAmount);
        amountInfo.textContent = 'Tiền cọc (30% giá phòng): ' + new Intl.NumberFormat('vi-VN').format(Math.round(depositAmount)) + ' ₫';
    } else {
        amountInput.value = Math.round(totalInvoice);
        amountInfo.textContent = 'Thanh toán toàn bộ hóa đơn (gồm VAT <?php echo VAT_RATE; ?>%): ' + new Intl.NumberFormat('vi-VN').format(Math.round(totalInvoice)) + ' ₫';
    }
}

// Gọi lần đầu khi load trang
document.addEventListener('DOMContentLoaded', updatePaymentAmount);
</script>

