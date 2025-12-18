<?php
/**
 * Trang xác nhận thanh toán booking - Khách hàng
 * Khách hàng có thể thanh toán: đặt cọc (deposit) hoặc thanh toán đủ (final)
 * Hỗ trợ các phương thức: tiền mặt, chuyển khoản, thẻ tín dụng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);  // Chỉ khách hàng mới thanh toán được

$booking_id = $_GET['booking_id'] ?? 0;
$payment_method = $_GET['payment_method'] ?? 'cash';
$allowed_methods = ['cash', 'bank_transfer', 'credit_card'];
if (!in_array($payment_method, $allowed_methods, true)) {
    $payment_method = 'cash';
}

$booking = null;
$customer_id = $_SESSION['customer_id'];
$errors = [];
$success = false;

try {
    // Lấy thông tin booking của khách hàng đang đăng nhập
    // Kiểm tra customer_id để đảm bảo khách chỉ thấy booking của mình
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_number, rt.type_name, rt.base_price, u.full_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id
        JOIN users u ON b.created_by = u.id
        WHERE b.id = :id AND b.customer_id = :customer_id  -- Bảo mật: chỉ lấy booking của mình
    ");
    $stmt->execute([
        'id' => $booking_id,
        'customer_id' => $customer_id
    ]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        die('Booking không tồn tại');
    }

    // Tính tổng tiền đã thanh toán (từ bảng payments)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total_paid FROM payments WHERE booking_id = :id AND status = 'completed'");
    $stmt->execute(['id' => $booking_id]);
    $total_paid = floatval($stmt->fetch()['total_paid'] ?? 0);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Lỗi: ' . $e->getMessage());
}

// Tính toán các số tiền
$nights = calculateNights($booking['check_in'], $booking['check_out']);  // Số đêm
$subtotal = $booking['total_amount'];                                   // Tổng tiền phòng
$tax = $subtotal * (VAT_RATE / 100);                                   // Thuế VAT
$total_invoice = calculateInvoiceTotal($subtotal);                     // Tổng cộng (bao gồm thuế)
$deposit_required = calculateDeposit($booking['base_price'], $nights); // Tiền đặt cọc yêu cầu
$remaining_amount = max(0, $total_invoice - $total_paid);              // Số tiền còn lại

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_type = $_POST['payment_type'] ?? 'deposit';
    $amount = floatval($_POST['amount'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate
    if ($amount <= 0) {
        $errors[] = 'Số tiền phải lớn hơn 0';
    }
    
    // Validate theo loại thanh toán
    if ($payment_type === 'deposit') {
        if ($total_paid > 0) {
            $errors[] = 'Bạn đã thanh toán, không cần đặt cọc thêm.';
        }
        if (abs($amount - $deposit_required) > 0.01) {
            $errors[] = 'Tiền cọc phải bằng 30% giá phòng (' . formatCurrency($deposit_required) . ')';
        }
    } elseif ($payment_type === 'final') {
        if ($remaining_amount <= 0) {
            $errors[] = 'Booking đã thanh toán đủ.';
        } elseif (abs($amount - $remaining_amount) > 0.01) {
            $errors[] = 'Thanh toán cuối cùng phải bằng số tiền còn lại (' . formatCurrency($remaining_amount) . ')';
        }
    }
    
    if (empty($errors)) {
        try {
            // Tạo record thanh toán
            $payment_code = 'PAY-' . date('YmdHis') . '-' . $booking_id;
            
            $stmt = $pdo->prepare("
                INSERT INTO payments (
                    payment_code, booking_id, amount, payment_method, payment_type, payment_date, notes, status, processed_by
                ) VALUES (
                    :payment_code, :booking_id, :amount, :payment_method, :payment_type, NOW(), :notes, :status, :processed_by
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
            
            // Cập nhật trạng thái booking và phòng sau khi thanh toán
            if ($payment_type === 'final') {
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
                $stmt->execute(['id' => $booking_id]);
                
                // Đánh dấu phòng đã được đặt sau khi thanh toán đủ
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = :room_id");
                $stmt->execute(['room_id' => $booking['room_id']]);
                
                $msg = 'Thanh toán thành công! Booking đã được xác nhận và phòng đã được giữ.';
            } else {
                // Deposit payment - confirm booking and mark room as occupied
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = :id");
                $stmt->execute(['id' => $booking_id]);
                
                $stmt = $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = :room_id");
                $stmt->execute(['room_id' => $booking['room_id']]);
                
                $msg = 'Thanh toán cọc thành công! Phòng đã được giữ cho bạn. Vui lòng thanh toán phần còn lại khi nhận phòng.';
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
                                    <option value="deposit" <?php echo $total_paid > 0 ? 'disabled' : 'selected'; ?>>Thanh toán tiền cọc (30%)</option>
                                    <option value="final" <?php echo $total_paid > 0 ? 'selected' : ''; ?>>Thanh toán phần còn lại</option>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    <strong>Tiền cọc (30%):</strong> <?php echo formatCurrency($deposit_required); ?><br>
                                    <strong>Thanh toán cuối:</strong> <?php echo formatCurrency($total_invoice); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Phương thức thanh toán</label>
                                <input type="text" class="form-control" value="<?php echo getPaymentMethodLabel($payment_method); ?>" disabled>
                                <input type="hidden" name="payment_method" value="<?php echo esc($payment_method); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Số tiền thanh toán (VND) *</label>
                            <input type="number" class="form-control form-control-lg" id="amount" name="amount" 
                                value="<?php echo intval($total_paid > 0 ? $remaining_amount : $deposit_required); ?>" 
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
                                    echo 'Vui lòng chuyển khoản tới <strong>Ngân hàng ABC</strong> - Số TK: <strong>123456789</strong> - Chủ TK: <strong>Công ty TNHH Khách Sạn</strong>. Nội dung: <strong>' . esc($booking['booking_code']) . '</strong>.';
                                } else {
                                    echo 'Bạn sẽ nhập thông tin thẻ trên trang thanh toán tiếp theo.';
                                }
                            ?>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg" <?php echo $remaining_amount <= 0 ? 'disabled' : ''; ?>>
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
                            <td class="text-end text-success fw-bold"><?php echo formatCurrency($total_paid); ?></td>
                        </tr>
                        <tr class="table-light">
                            <td>Còn phải thanh toán:</td>
                            <td class="text-end text-danger fw-bold"><?php echo formatCurrency($remaining_amount); ?></td>
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
    
    const depositAmount = <?php echo $deposit_required; ?>;
    const remainingAmount = <?php echo $remaining_amount; ?>;
    const totalPaid = <?php echo $total_paid; ?>;
    
    if (paymentType === 'deposit') {
        if (totalPaid > 0) {
            amountInput.value = 0;
            amountInfo.textContent = 'Bạn đã thanh toán, không cần đặt cọc thêm.';
        } else {
            amountInput.value = Math.round(depositAmount);
            amountInfo.textContent = 'Tiền cọc (30% giá phòng): ' + new Intl.NumberFormat('vi-VN').format(Math.round(depositAmount)) + ' ₫';
        }
    } else {
        amountInput.value = Math.round(remainingAmount);
        amountInfo.textContent = 'Thanh toán số tiền còn lại: ' + new Intl.NumberFormat('vi-VN').format(Math.round(remainingAmount)) + ' ₫';
    }
}

// Gọi lần đầu khi load trang
document.addEventListener('DOMContentLoaded', updatePaymentAmount);
</script>

