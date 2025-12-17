<?php
/**
 * Trang đặt phòng - Khách hàng
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$room_id = $_GET['room_id'] ?? 0;
$check_in = $_GET['check_in'] ?? date('Y-m-d');
$check_out = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
$guests = $_GET['guests'] ?? 1;

$room = null;
$room_type = null;
$errors = [];
$success = false;

try {
    // Lấy thông tin phòng
    $stmt = $pdo->prepare("
        SELECT r.*, rt.type_name, rt.base_price, rt.capacity, rt.description, rt.amenities
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.id = :id AND r.status = 'available'
    ");
    $stmt->execute(['id' => $room_id]);
    $room = $stmt->fetch();
    
    if (!$room) {
        die('Phòng không tồn tại hoặc không còn trống');
    }
    
    // Kiểm tra phòng còn trống không
    if (!isRoomAvailable($pdo, $room_id, $check_in, $check_out)) {
        die('Phòng này không còn trống trong khoảng thời gian này. Vui lòng chọn phòng hoặc ngày khác.');
    }
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('Lỗi: ' . $e->getMessage());
}

// Xử lý tạo booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adults = $_POST['adults'] ?? 1;
    $children = $_POST['children'] ?? 0;
    $special_requests = trim($_POST['special_requests'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $deposit_amount = $_POST['deposit_amount'] ?? 0;
    
    // Validate
    if (empty($adults) || $adults < 1) {
        $errors[] = 'Số người lớn phải lớn hơn 0';
    }
    
    if (empty($errors)) {
        try {
            // Tính tiền
            $nights = calculateNights($check_in, $check_out);
            $total_amount = $room['base_price'] * $nights;
            $booking_code = generateBookingCode();
            
            // Tạo booking
            $stmt = $pdo->prepare("
                INSERT INTO bookings (
                    booking_code, customer_id, room_id, check_in, check_out,
                    adults, children, special_requests, status, total_amount,
                    deposit_amount, created_by
                ) VALUES (
                    :booking_code, :customer_id, :room_id, :check_in, :check_out,
                    :adults, :children, :special_requests, :status, :total_amount,
                    :deposit_amount, :created_by
                )
            ");
            
            $stmt->execute([
                'booking_code' => $booking_code,
                'customer_id' => $_SESSION['customer_id'],
                'room_id' => $room_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'adults' => $adults,
                'children' => $children,
                'special_requests' => $special_requests,
                'status' => 'pending',
                'total_amount' => $total_amount,
                'deposit_amount' => $deposit_amount,
                'created_by' => $_SESSION['user_id']
            ]);
            
            $booking_id = $pdo->lastInsertId();
            
            logActivity($pdo, $_SESSION['user_id'], 'CREATE_BOOKING', 'Đặt phòng ' . $booking_code);
            
            // Redirect tới trang thanh toán
            redirect('payment_confirmation.php?booking_id=' . $booking_id . '&payment_method=' . urlencode($payment_method));
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
            error_log($e->getMessage());
        }
    }
}

$nights = calculateNights($check_in, $check_out);
$total_amount = $room['base_price'] * $nights;

$page_title = 'Xác nhận đặt phòng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Thông tin phòng -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-door-open"></i> Thông tin phòng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Phòng số:</strong> <?php echo esc($room['room_number']); ?></p>
                            <p class="mb-2"><strong>Loại phòng:</strong> <?php echo esc($room['type_name']); ?></p>
                            <p class="mb-2"><strong>Sức chứa:</strong> <?php echo $room['capacity']; ?> người</p>
                            <p class="mb-2"><strong>Giá:</strong> <?php echo formatCurrency($room['base_price']); ?>/đêm</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Check-in:</strong> <?php echo formatDate($check_in); ?></p>
                            <p class="mb-2"><strong>Check-out:</strong> <?php echo formatDate($check_out); ?></p>
                            <p class="mb-2"><strong>Số đêm:</strong> <?php echo $nights; ?> đêm</p>
                            <p class="mb-2"><strong>Tổng tiền:</strong> <span class="text-primary fs-5"><?php echo formatCurrency($total_amount); ?></span></p>
                        </div>
                    </div>
                    <?php if (!empty($room['description'])): ?>
                        <hr>
                        <p><strong>Mô tả:</strong> <?php echo esc($room['description']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($room['amenities'])): ?>
                        <p><strong>Tiện nghi:</strong> <?php echo esc($room['amenities']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Form đặt phòng -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Hoàn thành đặt phòng</h5>
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adults" class="form-label">Số người lớn *</label>
                                <input type="number" class="form-control" id="adults" name="adults" 
                                       value="<?php echo $guests; ?>" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="children" class="form-label">Số trẻ em</label>
                                <input type="number" class="form-control" id="children" name="children" 
                                       value="0" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" rows="3" placeholder="Vd: Phòng tầng cao, gần thang máy..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="deposit_amount" class="form-label">Tiền cọc (VND)</label>
                                <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" 
                                       value="0" min="0" step="10000">
                                <small class="text-muted">Tổng tiền: <?php echo formatCurrency($total_amount); ?></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Phương thức thanh toán *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                                    <option value="credit_card">Thẻ tín dụng</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Sau khi đặt phòng, bạn sẽ được chuyển tới trang thanh toán để xác nhận booking.
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Đặt phòng & Thanh toán
                            </button>
                            <a href="search_rooms.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tóm tắt -->
        <div class="col-md-4">
            <div class="card shadow border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-receipt"></i> Tóm tắt đơn hàng</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Giá/đêm:</strong></td>
                            <td class="text-end"><?php echo formatCurrency($room['base_price']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Số đêm:</strong></td>
                            <td class="text-end"><?php echo $nights; ?></td>
                        </tr>
                        <tr class="border-top pt-2">
                            <td><strong>Tạm tính:</strong></td>
                            <td class="text-end"><strong><?php echo formatCurrency($total_amount); ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Thuế VAT (10%):</strong></td>
                            <td class="text-end"><?php echo formatCurrency($total_amount * 0.1); ?></td>
                        </tr>
                        <tr class="table-primary fw-bold">
                            <td><strong>Tổng cộng:</strong></td>
                            <td class="text-end text-primary"><?php echo formatCurrency($total_amount * 1.1); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
