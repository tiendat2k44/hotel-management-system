<?php
/**
 * Tạo booking mới
 */

require_once '../../../../config/constants.php';
require_once '../../../../config/database.php';
require_once '../../../../includes/functions.php';
require_once '../../../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$errors = [];
$customers = [];
$available_rooms = [];

try {
    // Lấy danh sách khách hàng
    $stmt = $pdo->prepare("
        SELECT c.id, u.full_name, u.email, u.phone
        FROM customers c
        JOIN users u ON c.user_id = u.id
        ORDER BY u.full_name
    ");
    $stmt->execute();
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = $_POST['customer_id'] ?? '';
    $check_in = trim($_POST['check_in'] ?? '');
    $check_out = trim($_POST['check_out'] ?? '');
    $room_id = $_POST['room_id'] ?? '';
    $adults = $_POST['adults'] ?? 1;
    $children = $_POST['children'] ?? 0;
    $special_requests = trim($_POST['special_requests'] ?? '');
    $deposit_amount = $_POST['deposit_amount'] ?? 0;
    
    // Validate
    if (empty($customer_id)) {
        $errors[] = 'Khách hàng không được để trống';
    }
    if (empty($check_in) || !validateDate($check_in)) {
        $errors[] = 'Ngày check-in không hợp lệ';
    }
    if (empty($check_out) || !validateDate($check_out)) {
        $errors[] = 'Ngày check-out không hợp lệ';
    }
    if ($check_in >= $check_out) {
        $errors[] = 'Ngày check-out phải sau check-in';
    }
    if (empty($room_id)) {
        $errors[] = 'Phòng không được để trống';
    }
    
    if (empty($errors)) {
        try {
            // Kiểm tra phòng trống
            if (!isRoomAvailable($pdo, $room_id, $check_in, $check_out)) {
                $errors[] = 'Phòng này không có sẵn trong khoảng thời gian này';
            } else {
                // Lấy giá phòng
                $stmt = $pdo->prepare("
                    SELECT rt.base_price 
                    FROM rooms r
                    JOIN room_types rt ON r.room_type_id = rt.id
                    WHERE r.id = :room_id
                ");
                $stmt->execute(['room_id' => $room_id]);
                $room_data = $stmt->fetch();
                
                if (!$room_data) {
                    $errors[] = 'Phòng không tồn tại';
                } else {
                    $nights = calculateNights($check_in, $check_out);
                    $total_amount = $room_data['base_price'] * $nights;
                    $booking_code = generateBookingCode();
                    
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
                        'customer_id' => $customer_id,
                        'room_id' => $room_id,
                        'check_in' => $check_in,
                        'check_out' => $check_out,
                        'adults' => $adults,
                        'children' => $children,
                        'special_requests' => $special_requests,
                        'status' => 'confirmed',
                        'total_amount' => $total_amount,
                        'deposit_amount' => $deposit_amount,
                        'created_by' => $_SESSION['user_id']
                    ]);
                    
                    setFlash('success', 'Tạo booking thành công');
                    logActivity($pdo, $_SESSION['user_id'], 'CREATE_BOOKING', 'Tạo booking ' . $booking_code);
                    redirect('index.php');
                }
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Tạo booking mới';
?>

<?php include_once '../../../../includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus"></i> Tạo booking mới</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle"></i> <?php echo esc($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="bookingForm">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">Khách hàng *</label>
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>">
                                        <?php echo esc($customer['full_name']); ?> (<?php echo esc($customer['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="check_in" class="form-label">Ngày check-in *</label>
                                <input type="date" class="form-control" id="check_in" name="check_in" 
                                       value="<?php echo esc($_POST['check_in'] ?? date('Y-m-d')); ?>" required 
                                       onchange="updateAvailableRooms()">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="check_out" class="form-label">Ngày check-out *</label>
                                <input type="date" class="form-control" id="check_out" name="check_out" 
                                       value="<?php echo esc($_POST['check_out'] ?? date('Y-m-d', strtotime('+1 day'))); ?>" required 
                                       onchange="updateAvailableRooms()">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adults" class="form-label">Số người lớn</label>
                                <input type="number" class="form-control" id="adults" name="adults" 
                                       value="<?php echo esc($_POST['adults'] ?? '1'); ?>" min="1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="children" class="form-label">Số trẻ em</label>
                                <input type="number" class="form-control" id="children" name="children" 
                                       value="<?php echo esc($_POST['children'] ?? '0'); ?>" min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="room_id" class="form-label">Phòng *</label>
                            <select class="form-select" id="room_id" name="room_id" required>
                                <option value="">-- Chọn phòng --</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" rows="3"><?php echo esc($_POST['special_requests'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="deposit_amount" class="form-label">Tiền cọc</label>
                                <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" 
                                       value="<?php echo esc($_POST['deposit_amount'] ?? '0'); ?>" min="0" step="10000">
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Tạo booking
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

<script>
// Fetch available rooms via AJAX
function updateAvailableRooms() {
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    
    if (checkIn && checkOut) {
        fetch('<?php echo BASE_URL; ?>api/check_room_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'check_in=' + encodeURIComponent(checkIn) + '&check_out=' + encodeURIComponent(checkOut)
        })
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('room_id');
            select.innerHTML = '<option value="">-- Chọn phòng --</option>';
            
            data.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = room.room_number + ' (' + room.type_name + ' - ' + room.base_price.toLocaleString('vi-VN') + '₫)';
                select.appendChild(option);
            });
        });
    }
}

// Update on page load
document.addEventListener('DOMContentLoaded', updateAvailableRooms);
</script>

<?php include_once '../../../../includes/footer.php'; ?>
