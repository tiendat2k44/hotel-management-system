<?php
/**
 * Sửa booking
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$booking_id = $_GET['id'] ?? '';
$errors = [];
$booking = null;

if (empty($booking_id)) {
    redirect('index.php', 'Booking không tồn tại', 'danger');
}

try {
    // Lấy thông tin booking
    $stmt = $pdo->prepare("
        SELECT b.*, u.full_name, r.room_number
        FROM bookings b
        JOIN customers c ON b.customer_id = c.id
        JOIN users u ON c.user_id = u.id
        JOIN rooms r ON b.room_id = r.id
        WHERE b.id = :id
    ");
    $stmt->execute(['id' => $booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        redirect('index.php', 'Booking không tồn tại', 'danger');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $adults = $_POST['adults'] ?? 1;
    $children = $_POST['children'] ?? 0;
    $special_requests = trim($_POST['special_requests'] ?? '');
    $deposit_amount = $_POST['deposit_amount'] ?? 0;
    
    // Validate
    if (empty($adults)) {
        $errors[] = 'Số người lớn không được để trống';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE bookings
                SET adults = :adults, children = :children, 
                    special_requests = :special_requests, deposit_amount = :deposit_amount
                WHERE id = :id
            ");
            
            $stmt->execute([
                'adults' => $adults,
                'children' => $children,
                'special_requests' => $special_requests,
                'deposit_amount' => $deposit_amount,
                'id' => $booking_id
            ]);
            
            setFlash('success', 'Cập nhật booking thành công');
            logActivity($pdo, $_SESSION['user_id'], 'UPDATE_BOOKING', 'Cập nhật booking ' . $booking['booking_code']);
            redirect('view.php?id=' . $booking_id);
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Chỉnh sửa booking';
?>

<?php include_once '../../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Chỉnh sửa booking <?php echo esc($booking['booking_code']); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle"></i> <?php echo esc($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Thông tin phòng -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2">Thông tin phòng</h6>
                        <p><strong>Phòng:</strong> <?php echo esc($booking['room_number']); ?></p>
                        <p><strong>Khách hàng:</strong> <?php echo esc($booking['full_name']); ?></p>
                        <p><strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?></p>
                        <p><strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?></p>
                    </div>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adults" class="form-label">Số người lớn *</label>
                                <input type="number" class="form-control" id="adults" name="adults" 
                                       value="<?php echo esc($_POST['adults'] ?? $booking['adults']); ?>" 
                                       min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="children" class="form-label">Số trẻ em</label>
                                <input type="number" class="form-control" id="children" name="children" 
                                       value="<?php echo esc($_POST['children'] ?? $booking['children']); ?>" 
                                       min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" 
                                      rows="4"><?php echo esc($_POST['special_requests'] ?? $booking['special_requests']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deposit_amount" class="form-label">Tiền cọc</label>
                            <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" 
                                   value="<?php echo esc($_POST['deposit_amount'] ?? $booking['deposit_amount']); ?>" 
                                   min="0" step="10000">
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                            <a href="view.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary">
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
