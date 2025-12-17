<?php
/**
 * Trang chủ công khai
 */

require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth_check.php';

try {
    // Lấy thông tin khách sạn
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT id) as total_rooms,
               COUNT(CASE WHEN status = 'available' THEN 1 END) as available
        FROM rooms
    ");
    $stmt->execute();
    $hotel_stats = $stmt->fetch();
    
    // Lấy danh sách loại phòng
    $stmt = $pdo->prepare("
        SELECT * FROM room_types WHERE status = 'active' ORDER BY type_name
    ");
    $stmt->execute();
    $room_types = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$page_title = 'Trang chủ';
?>

<?php include_once 'includes/header.php'; ?>

<!-- Hero section -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 4rem 0;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4" style="color: white;"><i class="fas fa-hotel"></i> <?php echo APP_NAME; ?></h1>
                <p class="lead" style="color: rgba(255,255,255,0.9);">
                    Hệ thống quản lý khách sạn hiện đại, toàn diện và dễ sử dụng.
                </p>
                <p style="color: rgba(255,255,255,0.8);">
                    Cho phép quản lý phòng, booking, thanh toán và báo cáo một cách hiệu quả.
                </p>
                <div class="mt-4">
                    <?php if (!isLoggedIn()): ?>
                        <a href="modules/auth/login.php" class="btn btn-light btn-lg me-2">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="modules/auth/register.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    <?php else: ?>
                        <a href="modules/customer/dashboard.php" class="btn btn-light btn-lg">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-center">
                    <i class="fas fa-building" style="font-size: 8rem; color: rgba(255,255,255,0.3);"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <!-- Thống kê nhanh -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="text-center">
                <h2 style="color: #667eea; font-size: 2.5em;"><?php echo $hotel_stats['total_rooms'] ?? 0; ?></h2>
                <p class="text-muted">Tổng phòng</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <h2 style="color: #28a745; font-size: 2.5em;"><?php echo $hotel_stats['available'] ?? 0; ?></h2>
                <p class="text-muted">Phòng trống</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <h2 style="color: #17a2b8; font-size: 2.5em;"><?php echo count($room_types); ?></h2>
                <p class="text-muted">Loại phòng</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center">
                <h2 style="color: #ffc107; font-size: 2.5em;">24/7</h2>
                <p class="text-muted">Hỗ trợ khách hàng</p>
            </div>
        </div>
    </div>
    
    <!-- Danh sách loại phòng -->
    <section class="mb-5">
        <h2 class="mb-4 text-center">Loại Phòng Của Chúng Tôi</h2>
        <div class="row">
            <?php foreach ($room_types as $type): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo esc($type['type_name']); ?></h5>
                            <p class="card-text text-muted"><?php echo esc($type['description']); ?></p>
                            <p class="mb-2">
                                <span class="badge bg-primary p-2">Sức chứa: <?php echo $type['capacity']; ?> người</span>
                            </p>
                            <h4 class="text-primary"><?php echo formatCurrency($type['base_price']); ?>/đêm</h4>
                            <?php if (!empty($type['amenities'])): ?>
                                <div class="mt-3 text-start">
                                    <small class="text-muted"><?php echo esc($type['amenities']); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Booking form -->
    <section class="mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Tìm Phòng</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="modules/customer/search_rooms.php">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label for="search_check_in" class="form-label">Check-in</label>
                                    <input type="date" class="form-control" id="search_check_in" name="check_in" 
                                           value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="search_check_out" class="form-label">Check-out</label>
                                    <input type="date" class="form-control" id="search_check_out" name="check_out" 
                                           value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label for="search_guests" class="form-label">Số người</label>
                                    <input type="number" class="form-control" id="search_guests" name="guests" 
                                           value="1" min="1">
                                </div>
                            </div>
                            <div class="mt-3">
                                <?php if (isLoggedIn()): ?>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Tìm phòng trống
                                    </button>
                                <?php else: ?>
                                    <a href="modules/auth/login.php" class="btn btn-primary w-100">
                                        <i class="fas fa-sign-in-alt"></i> Đăng nhập để đặt phòng
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features -->
    <section class="mb-5">
        <h2 class="mb-4 text-center">Tính Năng Chính</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-calendar-check" style="font-size: 3rem; color: #667eea;"></i>
                    <h5 class="mt-3">Đặt Phòng Online</h5>
                    <p class="text-muted">Đặt phòng dễ dàng chỉ trong vài bước, không cần đến trực tiếp.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-lock" style="font-size: 3rem; color: #28a745;"></i>
                    <h5 class="mt-3">Thanh Toán An Toàn</h5>
                    <p class="text-muted">Thanh toán an toàn bằng nhiều phương thức khác nhau.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="fas fa-users" style="font-size: 3rem; color: #17a2b8;"></i>
                    <h5 class="mt-3">Hỗ Trợ 24/7</h5>
                    <p class="text-muted">Đội ngũ hỗ trợ khách hàng sẵn sàng phục vụ bạn mọi lúc.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contact -->
    <section class="mb-5 bg-light p-5 rounded">
        <h2 class="mb-4">Liên Hệ Với Chúng Tôi</h2>
        <div class="row">
            <div class="col-md-4 mb-3">
                <h6><i class="fas fa-phone"></i> Điện thoại</h6>
                <p>Hotline: <strong>1900-1234</strong></p>
            </div>
            <div class="col-md-4 mb-3">
                <h6><i class="fas fa-envelope"></i> Email</h6>
                <p><a href="mailto:info@hotel.com">info@hotel.com</a></p>
            </div>
            <div class="col-md-4 mb-3">
                <h6><i class="fas fa-map-marker-alt"></i> Địa chỉ</h6>
                <p>Hà Nội, Việt Nam</p>
            </div>
        </div>
    </section>
</div>

<?php include_once 'includes/footer.php'; ?>
