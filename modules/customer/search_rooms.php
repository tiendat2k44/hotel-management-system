<?php
/**
 * File: modules/customer/search_rooms.php
 * Description: Tìm kiếm phòng trống theo ngày
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

// Kiểm tra đăng nhập
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

// Lấy tham số tìm kiếm
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;

$rooms = [];
$error = '';

try {
    // Validate dates
    if (strtotime($check_out) <= strtotime($check_in)) {
        $error = 'Ngày check-out phải sau ngày check-in';
    } else {
        // Tìm phòng trống: tránh trùng khoảng ngày theo cột đúng (check_in, check_out)
        $stmt = $pdo->prepare("
            SELECT r.id, r.room_number, rt.type_name, rt.base_price, rt.capacity, rt.description, rt.amenities
            FROM rooms r
            JOIN room_types rt ON r.room_type_id = rt.id
            WHERE r.status = 'available'
              AND rt.capacity >= :guests
              AND r.id NOT IN (
                  SELECT room_id FROM bookings 
                  WHERE status IN ('pending','confirmed','checked_in')
                    AND (check_in < :check_out AND check_out > :check_in)
              )
            ORDER BY rt.base_price ASC
        ");
        $stmt->execute([
            'guests' => $guests,
            'check_in' => $check_in,
            'check_out' => $check_out
        ]);
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'Lỗi: ' . $e->getMessage();
}

$page_title = 'Tìm Kiếm Phòng';
require_once ROOT_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-search"></i> Tìm Kiếm Phòng</h2>
        </div>
    </div>
    
    <!-- Search form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="check_in" class="form-label">Check-in</label>
                    <input type="date" class="form-control" id="check_in" name="check_in" 
                           value="<?php echo htmlspecialchars($check_in); ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="check_out" class="form-label">Check-out</label>
                    <input type="date" class="form-control" id="check_out" name="check_out" 
                           value="<?php echo htmlspecialchars($check_out); ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="guests" class="form-label">Số người</label>
                    <input type="number" class="form-control" id="guests" name="guests" 
                           value="<?php echo $guests; ?>" min="1" required>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm Kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Error message -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Results -->
    <div class="row">
        <?php if (count($rooms) > 0): ?>
            <?php 
            $nights = calculateNights($check_in, $check_out);
            foreach ($rooms as $room):
                $room_total = $nights * $room['base_price'];
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-door-open"></i> 
                                Phòng <?php echo htmlspecialchars($room['room_number']); ?>
                            </h5>
                            <p class="card-text">
                                <strong><?php echo htmlspecialchars($room['type_name']); ?></strong>
                            </p>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars($room['description'] ?? ''); ?>
                            </p>
                            <div class="mb-3">
                                <span class="badge bg-info">Sức chứa: <?php echo $room['capacity']; ?> người</span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <strong>Giá:</strong> <?php echo formatCurrency($room['base_price']); ?>/đêm
                                </small><br>
                                <small class="text-muted">
                                    <strong>Số đêm:</strong> <?php echo $nights; ?> đêm
                                </small><br>
                                <h5 class="text-primary mt-2">
                                    Tổng: <?php echo formatCurrency($room_total); ?>
                                </h5>
                            </div>
                            
                            <a href="book_room.php?room_id=<?php echo $room['id']; ?>&check_in=<?php echo $check_in; ?>&check_out=<?php echo $check_out; ?>&guests=<?php echo $guests; ?>" 
                               class="btn btn-success btn-sm w-100">
                                <i class="fas fa-calendar-check"></i> Đặt Phòng
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle"></i>
                    Không tìm thấy phòng phù hợp với tiêu chí tìm kiếm của bạn.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once ROOT_PATH . 'includes/footer.php'; ?>
