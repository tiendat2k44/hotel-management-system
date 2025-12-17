<?php
/**
 * Trang báo cáo - Admin
 * Báo cáo doanh thu, bookings, khách hàng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_ADMIN);

$report_type = $_GET['type'] ?? 'revenue';
$date_from = $_GET['from'] ?? date('Y-m-01');
$date_to = $_GET['to'] ?? date('Y-m-d');

$page_title = 'Báo cáo';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Báo cáo</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Báo cáo Hệ Thống</h2>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="report_type" class="form-label">Loại báo cáo</label>
                    <select class="form-select" name="type" id="report_type">
                        <option value="revenue" <?php if ($report_type === 'revenue') echo 'selected'; ?>>Doanh thu</option>
                        <option value="bookings" <?php if ($report_type === 'bookings') echo 'selected'; ?>>Bookings</option>
                        <option value="customers" <?php if ($report_type === 'customers') echo 'selected'; ?>>Khách hàng</option>
                        <option value="occupancy" <?php if ($report_type === 'occupancy') echo 'selected'; ?>>Tỷ lệ chiếm dụng</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" name="from" id="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" name="to" id="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-outline-success" onclick="exportToExcel()">
                        <i class="fas fa-download"></i> Xuất Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Xuất PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Content -->
    <div id="report-content">
        <?php
        try {
            if ($report_type === 'revenue') {
                // Báo cáo doanh thu
                ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Báo Cáo Doanh Thu</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <?php
                            // Tổng doanh thu
                            $stmt = $pdo->prepare("
                                SELECT 
                                    SUM(amount) as total_revenue,
                                    COUNT(DISTINCT booking_id) as total_payments,
                                    COUNT(DISTINCT payment_type) as payment_types
                                FROM payments
                                WHERE status = 'completed' 
                                AND DATE(created_at) BETWEEN :from AND :to
                            ");
                            $stmt->execute(['from' => $date_from, 'to' => $date_to]);
                            $revenue = $stmt->fetch();
                            ?>
                            <div class="col-md-4">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h6 class="card-title">Tổng Doanh Thu</h6>
                                        <h3><?php echo formatCurrency($revenue['total_revenue'] ?? 0); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h6 class="card-title">Số Lần Thanh Toán</h6>
                                        <h3><?php echo $revenue['total_payments'] ?? 0; ?> lần</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">Trung Bình/Lần</h6>
                                        <h3><?php echo formatCurrency(($revenue['total_revenue'] ?? 0) / max(1, $revenue['total_payments'] ?? 1)); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue by Payment Type -->
                        <h6 class="mb-3">Doanh thu theo phương thức thanh toán</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Phương Thức</th>
                                        <th>Số Lần</th>
                                        <th>Doanh Thu</th>
                                        <th>Phần Trăm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            payment_method,
                                            COUNT(*) as count,
                                            SUM(amount) as total
                                        FROM payments
                                        WHERE status = 'completed'
                                        AND DATE(created_at) BETWEEN :from AND :to
                                        GROUP BY payment_method
                                        ORDER BY total DESC
                                    ");
                                    $stmt->execute(['from' => $date_from, 'to' => $date_to]);
                                    $payment_methods = [
                                        'cash' => 'Tiền mặt',
                                        'bank_transfer' => 'Chuyển khoản',
                                        'credit_card' => 'Thẻ tín dụng'
                                    ];
                                    $grand_total = $revenue['total_revenue'] ?? 0;
                                    
                                    while ($row = $stmt->fetch()) {
                                        $percentage = $grand_total > 0 ? ($row['total'] / $grand_total * 100) : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $payment_methods[$row['payment_method']] ?? $row['payment_method']; ?></strong></td>
                                            <td><?php echo $row['count']; ?></td>
                                            <td><?php echo formatCurrency($row['total']); ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo round($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Revenue by Payment Type -->
                        <h6 class="mb-3 mt-4">Doanh thu theo loại thanh toán</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Loại</th>
                                        <th>Số Lần</th>
                                        <th>Doanh Thu</th>
                                        <th>Phần Trăm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            payment_type,
                                            COUNT(*) as count,
                                            SUM(amount) as total
                                        FROM payments
                                        WHERE status = 'completed'
                                        AND DATE(created_at) BETWEEN :from AND :to
                                        GROUP BY payment_type
                                        ORDER BY total DESC
                                    ");
                                    $stmt->execute(['from' => $date_from, 'to' => $date_to]);
                                    $payment_types = ['deposit' => 'Tiền cọc', 'final' => 'Thanh toán cuối'];
                                    
                                    while ($row = $stmt->fetch()) {
                                        $percentage = $grand_total > 0 ? ($row['total'] / $grand_total * 100) : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $payment_types[$row['payment_type']] ?? $row['payment_type']; ?></strong></td>
                                            <td><?php echo $row['count']; ?></td>
                                            <td><?php echo formatCurrency($row['total']); ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%">
                                                        <?php echo round($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
            } elseif ($report_type === 'bookings') {
                // Báo cáo bookings
                ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Báo Cáo Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT 
                                    COUNT(*) as total_bookings,
                                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                                    SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
                                    SUM(CASE WHEN status = 'checked_out' THEN 1 ELSE 0 END) as checked_out
                                FROM bookings
                                WHERE DATE(check_in) BETWEEN :from AND :to
                            ");
                            $stmt->execute(['from' => $date_from, 'to' => $date_to]);
                            $booking_stats = $stmt->fetch();
                            ?>
                            <div class="col-md-3">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">Tổng Bookings</h6>
                                        <h3><?php echo $booking_stats['total_bookings'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h6 class="card-title">Đã Xác Nhận</h6>
                                        <h3><?php echo $booking_stats['confirmed'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">Chờ Xác Nhận</h6>
                                        <h3><?php echo $booking_stats['pending'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h6 class="card-title">Đã Trả Phòng</h6>
                                        <h3><?php echo $booking_stats['checked_out'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Details -->
                        <h6 class="mb-3">Chi tiết bookings</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã Booking</th>
                                        <th>Phòng</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Khách</th>
                                        <th>Giá</th>
                                        <th>Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT b.*, r.room_number, rt.type_name
                                        FROM bookings b
                                        JOIN rooms r ON b.room_id = r.id
                                        JOIN room_types rt ON r.room_type_id = rt.id
                                        WHERE DATE(b.check_in) BETWEEN :from AND :to
                                        ORDER BY b.check_in DESC
                                        LIMIT 100
                                    ");
                                    $stmt->execute(['from' => $date_from, 'to' => $date_to]);
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
                                    
                                    while ($booking = $stmt->fetch()) {
                                        ?>
                                        <tr>
                                            <td><code><?php echo esc($booking['booking_code']); ?></code></td>
                                            <td><?php echo esc($booking['room_number']); ?> (<?php echo esc($booking['type_name']); ?>)</td>
                                            <td><?php echo formatDate($booking['check_in']); ?></td>
                                            <td><?php echo formatDate($booking['check_out']); ?></td>
                                            <td><?php echo $booking['adults']; ?> người</td>
                                            <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_badges[$booking['status']] ?? 'secondary'; ?>">
                                                    <?php echo $status_texts[$booking['status']] ?? 'N/A'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
            } elseif ($report_type === 'customers') {
                // Báo cáo khách hàng
                ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Báo Cáo Khách Hàng</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <?php
                            $stmt = $pdo->prepare("
                                SELECT 
                                    COUNT(DISTINCT c.id) as total_customers,
                                    COUNT(DISTINCT b.id) as total_bookings,
                                    ROUND(AVG(b.total_amount), 0) as avg_booking_value
                                FROM customers c
                                LEFT JOIN bookings b ON c.id = b.customer_id 
                                    AND DATE(b.check_in) BETWEEN :from AND :to
                            ");
                            $stmt->execute(['from' => $date_from, 'to' => $date_to]);
                            $customer_stats = $stmt->fetch();
                            ?>
                            <div class="col-md-4">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">Tổng Khách Hàng</h6>
                                        <h3><?php echo $customer_stats['total_customers'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h6 class="card-title">Bookings</h6>
                                        <h3><?php echo $customer_stats['total_bookings'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h6 class="card-title">Giá Trung Bình</h6>
                                        <h3><?php echo formatCurrency($customer_stats['avg_booking_value'] ?? 0); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Customers -->
                        <h6 class="mb-3">Top khách hàng (theo doanh thu)</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên</th>
                                        <th>Email</th>
                                        <th>Số Bookings</th>
                                        <th>Tổng Chi</th>
                                        <th>Ngày Tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            c.id,
                                            u.full_name,
                                            u.email,
                                            COUNT(b.id) as booking_count,
                                            SUM(b.total_amount) as total_spent
                                        FROM customers c
                                        JOIN users u ON c.user_id = u.id
                                        LEFT JOIN bookings b ON c.id = b.customer_id 
                                            AND DATE(b.check_in) BETWEEN :from AND :to
                                        GROUP BY c.id, u.full_name, u.email
                                        HAVING booking_count > 0 OR total_spent > 0
                                        ORDER BY total_spent DESC
                                        LIMIT 50
                                    ");
                                    $stmt->execute(['from' => $date_from, 'to' => $date_to]);
                                    
                                    while ($customer = $stmt->fetch()) {
                                        ?>
                                        <tr>
                                            <td><strong><?php echo esc($customer['full_name']); ?></strong></td>
                                            <td><?php echo esc($customer['email']); ?></td>
                                            <td><span class="badge bg-primary"><?php echo $customer['booking_count'] ?? 0; ?></span></td>
                                            <td><?php echo formatCurrency($customer['total_spent'] ?? 0); ?></td>
                                            <td><?php echo formatDate($customer['created_at'] ?? ''); ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
            } elseif ($report_type === 'occupancy') {
                // Báo cáo tỷ lệ chiếm dụng
                ?>
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Báo Cáo Tỷ Lệ Chiếm Dụng</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Tính số ngày trong khoảng
                        $days = (strtotime($date_to) - strtotime($date_from)) / (60 * 60 * 24) + 1;
                        $stmt = $pdo->query("SELECT COUNT(*) as total_rooms FROM rooms WHERE status = 'available'");
                        $total_rooms = $stmt->fetch()['total_rooms'];
                        
                        // Tổng số "phòng-ngày" có thể bán
                        $total_room_days = $total_rooms * $days;
                        
                        // Số "phòng-ngày" đã đặt
                        $stmt = $pdo->prepare("
                            SELECT SUM(
                                DATEDIFF(
                                    LEAST(check_out, :to_date),
                                    GREATEST(check_in, :from_date)
                                ) + 1
                            ) as booked_room_days
                            FROM bookings
                            WHERE status IN ('pending', 'confirmed', 'checked_in', 'checked_out')
                            AND check_in <= :to_date
                            AND check_out >= :from_date
                        ");
                        $stmt->execute([
                            'from_date' => $date_from,
                            'to_date' => $date_to
                        ]);
                        $occupancy_data = $stmt->fetch();
                        $booked_room_days = max(0, $occupancy_data['booked_room_days'] ?? 0);
                        $occupancy_rate = $total_room_days > 0 ? ($booked_room_days / $total_room_days * 100) : 0;
                        $available_room_days = $total_room_days - $booked_room_days;
                        ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">Tổng Phòng</h6>
                                        <h3><?php echo $total_rooms; ?></h3>
                                        <small><?php echo $days; ?> ngày = <?php echo $total_room_days; ?> phòng-ngày</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-success">
                                    <div class="card-body">
                                        <h6 class="card-title">Đã Đặt</h6>
                                        <h3><?php echo $booked_room_days; ?></h3>
                                        <small>phòng-ngày</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning">
                                    <div class="card-body">
                                        <h6 class="card-title">Còn Trống</h6>
                                        <h3><?php echo $available_room_days; ?></h3>
                                        <small>phòng-ngày</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info">
                                    <div class="card-body">
                                        <h6 class="card-title">Tỷ Lệ Chiếm Dụng</h6>
                                        <h3><?php echo round($occupancy_rate, 1); ?>%</h3>
                                        <div class="progress mt-2">
                                            <div class="progress-bar" style="width: <?php echo $occupancy_rate; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Occupancy by Room Type -->
                        <h6 class="mb-3">Tỷ lệ chiếm dụng theo loại phòng</h6>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Loại Phòng</th>
                                        <th>Số Phòng</th>
                                        <th>Phòng-Ngày Có Sẵn</th>
                                        <th>Phòng-Ngày Đã Đặt</th>
                                        <th>Tỷ Lệ (%)</th>
                                        <th>Chart</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            rt.id,
                                            rt.type_name,
                                            COUNT(DISTINCT r.id) as room_count,
                                            (COUNT(DISTINCT r.id) * :days) as total_room_days,
                                            COALESCE(SUM(
                                                DATEDIFF(
                                                    LEAST(b.check_out, :to_date),
                                                    GREATEST(b.check_in, :from_date)
                                                ) + 1
                                            ), 0) as booked_room_days
                                        FROM room_types rt
                                        LEFT JOIN rooms r ON rt.id = r.room_type_id AND r.status = 'available'
                                        LEFT JOIN bookings b ON r.id = b.room_id 
                                            AND b.status IN ('pending', 'confirmed', 'checked_in', 'checked_out')
                                            AND b.check_in <= :to_date
                                            AND b.check_out >= :from_date
                                        GROUP BY rt.id, rt.type_name
                                        ORDER BY rt.type_name
                                    ");
                                    $stmt->execute([
                                        'days' => $days,
                                        'from_date' => $date_from,
                                        'to_date' => $date_to
                                    ]);
                                    
                                    while ($room_type = $stmt->fetch()) {
                                        $total_rd = $room_type['total_room_days'];
                                        $booked_rd = max(0, $room_type['booked_room_days']);
                                        $rate = $total_rd > 0 ? ($booked_rd / $total_rd * 100) : 0;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo esc($room_type['type_name']); ?></strong></td>
                                            <td><?php echo $room_type['room_count']; ?></td>
                                            <td><?php echo $total_rd; ?></td>
                                            <td><?php echo $booked_rd; ?></td>
                                            <td><?php echo round($rate, 1); ?>%</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $rate; ?>%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
            }
        } catch (Exception $e) {
            ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Lỗi: <?php echo esc($e->getMessage()); ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<script>
function exportToExcel() {
    const table = document.querySelector('.table');
    let html = '<table>';
    table.querySelectorAll('tr').forEach(row => {
        html += '<tr>';
        row.querySelectorAll('td, th').forEach(cell => {
            html += '<td>' + cell.innerText + '</td>';
        });
        html += '</tr>';
    });
    html += '</table>';
    
    const data = new Blob([html], {type: 'application/vnd.ms-excel'});
    const url = URL.createObjectURL(data);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'report-<?php echo date('Y-m-d'); ?>.xls';
    link.click();
}

function exportToPDF() {
    alert('Chức năng xuất PDF sẽ được cập nhật sau. Hiện tại vui lòng sử dụng chức năng In (Ctrl+P) của trình duyệt.');
}
</script>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
