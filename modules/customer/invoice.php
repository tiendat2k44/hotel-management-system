<?php
/**
 * Xuất hóa đơn - Khách hàng
 */

require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

requireRole(ROLE_CUSTOMER);

$booking_id = $_GET['id'] ?? 0;
$format = $_GET['format'] ?? 'pdf'; // pdf hoặc excel
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
$total_paid = array_sum(array_column($payment_history, 'amount'));
$remaining = max(0, $total_invoice - $total_paid);

// Lấy thông tin khách hàng
$stmt = $pdo->prepare("SELECT u.* FROM users u JOIN customers c ON u.id = c.user_id WHERE c.id = :id");
$stmt->execute(['id' => $booking['customer_id']]);
$customer_user = $stmt->fetch();

if ($format === 'excel') {
    // Xuất Excel (CSV)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="hoa-don-' . $booking['booking_code'] . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Tiêu đề
    fputcsv($output, ['HÓA ĐƠN KHÁCH SẠN'], ';');
    fputcsv($output, [], ';');
    
    // Thông tin booking
    fputcsv($output, ['Mã Booking:', $booking['booking_code']], ';');
    fputcsv($output, ['Phòng:', $booking['room_number'] . ' (' . $booking['type_name'] . ')'], ';');
    fputcsv($output, ['Check-in:', formatDate($booking['check_in'])], ';');
    fputcsv($output, ['Check-out:', formatDate($booking['check_out'])], ';');
    fputcsv($output, ['Số đêm:', $nights], ';');
    fputcsv($output, [], ';');
    
    // Thông tin khách
    fputcsv($output, ['Tên khách:', $customer_user['full_name']], ';');
    fputcsv($output, ['Email:', $customer_user['email']], ';');
    fputcsv($output, ['Điện thoại:', $customer_user['phone']], ';');
    fputcsv($output, [], ';');
    
    // Chi tiết hóa đơn
    fputcsv($output, ['DANH MỤC DỊCH VỤ'], ';');
    fputcsv($output, ['Mô tả', 'Đơn giá', 'Số lượng', 'Thành tiền'], ';');
    fputcsv($output, [$booking['type_name'] . ' (' . $nights . ' đêm)', formatCurrency($booking['base_price']), 1, formatCurrency($room_total)], ';');
    
    foreach ($services_used as $service) {
        fputcsv($output, [
            $service['service_name'],
            formatCurrency($service['price']) . '/' . $service['unit'],
            $service['quantity'],
            formatCurrency($service['total_price'])
        ], ';');
    }
    
    fputcsv($output, [], ';');
    fputcsv($output, ['Cộng:', '', '', formatCurrency($subtotal)], ';');
    fputcsv($output, ['Thuế VAT (' . VAT_RATE . '%)', '', '', formatCurrency($tax_amount)], ';');
    fputcsv($output, ['TỔNG CỘNG:', '', '', formatCurrency($total_invoice)], ';');
    fputcsv($output, [], ';');
    
    // Thanh toán
    fputcsv($output, ['THANH TOÁN'], ';');
    fputcsv($output, ['Đã thanh toán:', '', '', formatCurrency($total_paid)], ';');
    fputcsv($output, ['Còn phải thanh toán:', '', '', formatCurrency($remaining)], ';');
    fputcsv($output, [], ';');
    
    // Lịch sử thanh toán
    if (count($payment_history) > 0) {
        fputcsv($output, ['LỊCH SỬ THANH TOÁN'], ';');
        fputcsv($output, ['Ngày', 'Số tiền', 'Phương thức', 'Trạng thái'], ';');
        foreach ($payment_history as $payment) {
            $methods = ['cash' => 'Tiền mặt', 'bank_transfer' => 'Chuyển khoản', 'credit_card' => 'Thẻ tín dụng'];
            fputcsv($output, [
                formatDate($payment['payment_date']),
                formatCurrency($payment['amount']),
                $methods[$payment['payment_method']] ?? $payment['payment_method'],
                $payment['status'] === 'completed' ? 'Hoàn thành' : 'Chờ xử lý'
            ], ';');
        }
    }
    
    fputcsv($output, [], ';');
    fputcsv($output, ['Ngày xuất: ' . formatDate(date('Y-m-d H:i:s'))], ';');
    
    fclose($output);
    exit;
    
} else {
    // Xuất PDF (HTML để in)
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hóa đơn <?php echo esc($booking['booking_code']); ?></title>
        <style>
            body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 20px;
                background: #f5f5f5;
                font-size: 13px;
                line-height: 1.5;
            }
            .invoice {
                max-width: 900px;
                margin: 0 auto;
                background: white;
                padding: 40px;
                border: 1px solid #ddd;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .invoice-header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #007bff;
                padding-bottom: 20px;
            }
            .invoice-header h1 {
                margin: 0;
                color: #007bff;
                font-size: 28px;
            }
            .invoice-header p {
                margin: 5px 0;
                color: #666;
            }
            .invoice-info {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 30px;
                border: 1px solid #eee;
                padding: 15px;
                background: #f9f9f9;
            }
            .info-block h3 {
                margin: 0 0 10px 0;
                color: #007bff;
                font-size: 13px;
                font-weight: bold;
            }
            .info-block p {
                margin: 5px 0;
            }
            .info-block strong {
                display: inline-block;
                width: 80px;
                color: #333;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .invoice-table {
                margin-bottom: 20px;
            }
            .invoice-table thead {
                background: #007bff;
                color: white;
            }
            .invoice-table th {
                padding: 10px;
                text-align: left;
                font-weight: bold;
                border: 1px solid #ddd;
            }
            .invoice-table td {
                padding: 10px;
                border: 1px solid #ddd;
            }
            .invoice-table tbody tr:nth-child(even) {
                background: #f9f9f9;
            }
            .text-right {
                text-align: right;
            }
            .text-center {
                text-align: center;
            }
            .totals {
                width: 100%;
                margin-top: 20px;
                margin-bottom: 30px;
            }
            .totals td:first-child {
                text-align: right;
                font-weight: bold;
                padding: 8px 15px;
                background: #f9f9f9;
                border: 1px solid #ddd;
            }
            .totals td:last-child {
                text-align: right;
                padding: 8px 15px;
                border: 1px solid #ddd;
            }
            .total-row {
                background: #007bff;
                color: white;
                font-weight: bold;
                font-size: 14px;
            }
            .payment-info {
                margin-top: 20px;
                padding: 15px;
                background: #e7f3ff;
                border-left: 4px solid #007bff;
            }
            .payment-info h4 {
                margin: 0 0 10px 0;
                color: #007bff;
            }
            .payment-history {
                width: 100%;
                margin-top: 20px;
            }
            .payment-history thead {
                background: #6c757d;
                color: white;
            }
            .payment-history th {
                padding: 10px;
                text-align: left;
                border: 1px solid #ddd;
                font-weight: bold;
            }
            .payment-history td {
                padding: 10px;
                border: 1px solid #ddd;
            }
            .footer {
                margin-top: 40px;
                border-top: 1px solid #ddd;
                padding-top: 15px;
                text-align: center;
                color: #999;
                font-size: 11px;
            }
            .button-group {
                margin-bottom: 20px;
                text-align: right;
            }
            .btn {
                padding: 10px 20px;
                margin-left: 10px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 13px;
                text-decoration: none;
            }
            .btn:hover {
                background: #0056b3;
            }
            .btn-secondary {
                background: #6c757d;
            }
            .btn-secondary:hover {
                background: #545b62;
            }
            @media print {
                body {
                    background: white;
                }
                .invoice {
                    box-shadow: none;
                    border: none;
                }
                .button-group {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="button-group">
            <button onclick="window.print()" class="btn">
                <i class="fas fa-print"></i> In hóa đơn
            </button>
            <a href="invoice.php?id=<?php echo $booking_id; ?>&format=excel" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </a>
            <a href="booking_detail.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="invoice">
            <!-- Header -->
            <div class="invoice-header">
                <h1>HÓA ĐƠN KHÁCH SẠN</h1>
                <p>Mã hóa đơn: <strong><?php echo esc($booking['booking_code']); ?></strong></p>
                <p>Ngày xuất: <strong><?php echo formatDate(date('Y-m-d H:i:s')); ?></strong></p>
            </div>

            <!-- Thông tin -->
            <div class="invoice-info">
                <div class="info-block">
                    <h3>THÔNG TIN ĐẶT PHÒNG</h3>
                    <p><strong>Phòng:</strong> <?php echo esc($booking['room_number']); ?></p>
                    <p><strong>Loại:</strong> <?php echo esc($booking['type_name']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo formatDate($booking['check_in']); ?></p>
                    <p><strong>Check-out:</strong> <?php echo formatDate($booking['check_out']); ?></p>
                    <p><strong>Số đêm:</strong> <?php echo $nights; ?></p>
                </div>
                <div class="info-block">
                    <h3>THÔNG TIN KHÁCH</h3>
                    <p><strong>Tên:</strong> <?php echo esc($customer_user['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo esc($customer_user['email']); ?></p>
                    <p><strong>Điện thoại:</strong> <?php echo esc($customer_user['phone'] ?? 'N/A'); ?></p>
                    <p><strong>Số người:</strong> <?php echo $booking['adults']; ?> người lớn<?php if ($booking['children'] > 0): ?>, <?php echo $booking['children']; ?> trẻ em<?php endif; ?></p>
                </div>
            </div>

            <!-- Chi tiết dịch vụ -->
            <h3 style="color: #007bff; margin-top: 30px;">CHI TIẾT DỊCH VỤ</h3>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Mô tả</th>
                        <th style="width: 100px;">Đơn giá</th>
                        <th style="width: 80px;" class="text-center">Số lượng</th>
                        <th style="width: 100px;" class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo esc($booking['type_name']); ?> (<?php echo $nights; ?> đêm)</td>
                        <td class="text-right"><?php echo formatCurrency($booking['base_price']); ?>/đêm</td>
                        <td class="text-center">1</td>
                        <td class="text-right"><strong><?php echo formatCurrency($room_total); ?></strong></td>
                    </tr>
                    <?php foreach ($services_used as $service): ?>
                        <tr>
                            <td><?php echo esc($service['service_name']); ?></td>
                            <td class="text-right"><?php echo formatCurrency($service['price']); ?>/<?php echo esc($service['unit']); ?></td>
                            <td class="text-center"><?php echo $service['quantity']; ?></td>
                            <td class="text-right"><strong><?php echo formatCurrency($service['total_price']); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Tính toán -->
            <table class="totals">
                <tr>
                    <td>Tổng tiền dịch vụ:</td>
                    <td><?php echo formatCurrency($subtotal); ?></td>
                </tr>
                <tr>
                    <td>Thuế VAT (<?php echo VAT_RATE; ?>%):</td>
                    <td><?php echo formatCurrency($tax_amount); ?></td>
                </tr>
                <tr class="total-row">
                    <td>TỔNG CỘNG:</td>
                    <td style="color: white;"><?php echo formatCurrency($total_invoice); ?></td>
                </tr>
            </table>

            <!-- Thông tin thanh toán -->
            <div class="payment-info">
                <h4>TÌNH TRẠNG THANH TOÁN</h4>
                <p><strong>Đã thanh toán:</strong> <?php echo formatCurrency($total_paid); ?></p>
                <p><strong>Còn phải thanh toán:</strong> <span style="color: #dc3545; font-weight: bold;"><?php echo formatCurrency($remaining); ?></span></p>
            </div>

            <!-- Lịch sử thanh toán -->
            <?php if (count($payment_history) > 0): ?>
                <h3 style="color: #007bff; margin-top: 30px;">LỊCH SỬ THANH TOÁN</h3>
                <table class="payment-history">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Ngày</th>
                            <th style="width: 100px;">Số tiền</th>
                            <th style="width: 120px;">Phương thức</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $methods = ['cash' => 'Tiền mặt', 'bank_transfer' => 'Chuyển khoản', 'credit_card' => 'Thẻ tín dụng'];
                        foreach ($payment_history as $payment):
                        ?>
                            <tr>
                                <td><?php echo formatDate($payment['payment_date']); ?></td>
                                <td class="text-right"><strong><?php echo formatCurrency($payment['amount']); ?></strong></td>
                                <td><?php echo $methods[$payment['payment_method']] ?? $payment['payment_method']; ?></td>
                                <td>
                                    <span style="padding: 3px 8px; border-radius: 3px; background: <?php echo $payment['status'] === 'completed' ? '#d4edda' : '#fff3cd'; ?>; color: <?php echo $payment['status'] === 'completed' ? '#155724' : '#856404'; ?>;">
                                        <?php echo $payment['status'] === 'completed' ? 'Hoàn thành' : 'Chờ xử lý'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Footer -->
            <div class="footer">
                <p>Cảm ơn bạn đã lưu trú tại khách sạn của chúng tôi!</p>
                <p>Hóa đơn này được xuất vào lúc <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}
