<?php
/**
 * DEMO: Cách sử dụng các hàm tối ưu mới
 * 
 * File này giải thích cách sử dụng các helper function vừa tạo
 */

// ============================================================
// 1. HELPER FUNCTIONS DEMO
// ============================================================

/**
 * 1a. calculateDeposit()
 * Tính tiền cọc (30% giá phòng)
 */
echo "📌 Ví Dụ 1: Tính Tiền Cọc\n";
echo "========================================\n";

$base_price = 1000000;  // 1 triệu/đêm
$nights = 3;

$deposit = calculateDeposit($base_price, $nights);
echo "Giá phòng: " . formatCurrency($base_price) . "/đêm\n";
echo "Số đêm: {$nights}\n";
echo "Tiền cọc (30%): " . formatCurrency($deposit) . "\n";
// Output:
// Giá phòng: 1,000,000 ₫/đêm
// Số đêm: 3
// Tiền cọc (30%): 900,000 ₫

echo "\n";

/**
 * 1b. calculateInvoiceTotal()
 * Tính tổng hóa đơn (gồm VAT 10%)
 */
echo "📌 Ví Dụ 2: Tính Tổng Hóa Đơn (Gồm VAT)\n";
echo "========================================\n";

$base_amount = 3000000;  // 3 triệu
$invoice_total = calculateInvoiceTotal($base_amount);

echo "Giá phòng: " . formatCurrency($base_amount) . "\n";
echo "Tổng hóa đơn (+VAT 10%): " . formatCurrency($invoice_total) . "\n";
// Output:
// Giá phòng: 3,000,000 ₫
// Tổng hóa đơn (+VAT 10%): 3,300,000 ₫

echo "\n";

/**
 * 1c. getStatusBadge()
 * Format status thành badge HTML
 */
echo "📌 Ví Dụ 3: Format Status Badge\n";
echo "========================================\n";

$statuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];

foreach ($statuses as $status) {
    $badge = getStatusBadge($status);
    echo "Status '{$status}': " . htmlspecialchars($badge) . "\n";
}
// Output:
// Status 'pending': <span class="badge bg-warning">Chờ xác nhận</span>
// Status 'confirmed': <span class="badge bg-info">Đã xác nhận</span>
// Status 'checked_in': <span class="badge bg-success">Đã nhận phòng</span>
// Status 'checked_out': <span class="badge bg-secondary">Đã trả phòng</span>
// Status 'cancelled': <span class="badge bg-danger">Đã hủy</span>

echo "\n";

/**
 * 1d. getPaymentMethodLabel()
 * Format phương thức thanh toán
 */
echo "📌 Ví Dụ 4: Format Payment Method\n";
echo "========================================\n";

$methods = ['cash', 'bank_transfer', 'credit_card'];

foreach ($methods as $method) {
    $label = getPaymentMethodLabel($method);
    echo "Method '{$method}': {$label}\n";
}
// Output:
// Method 'cash': Tiền mặt
// Method 'bank_transfer': Chuyển khoản
// Method 'credit_card': Thẻ tín dụng

echo "\n";

/**
 * 1e. getPaymentTypeLabel()
 * Format loại thanh toán
 */
echo "📌 Ví Dụ 5: Format Payment Type\n";
echo "========================================\n";

$types = ['deposit', 'final'];

foreach ($types as $type) {
    $label = getPaymentTypeLabel($type);
    echo "Type '{$type}': {$label}\n";
}
// Output:
// Type 'deposit': Tiền cọc
// Type 'final': Thanh toán cuối

echo "\n";

/**
 * 1f. checkBookingConflict()
 * Kiểm tra xem phòng có bị trùng booking không
 */
echo "📌 Ví Dụ 6: Kiểm Tra Booking Conflict\n";
echo "========================================\n";

$room_id = 1;
$check_in = '2025-12-20';
$check_out = '2025-12-23';

// Giả sử đã có query kết nối database
// if (checkBookingConflict($pdo, $room_id, $check_in, $check_out)) {
//     echo "❌ Phòng bị trùng booking\n";
// } else {
//     echo "✅ Phòng trống, có thể đặt\n";
// }

echo "\n";

// ============================================================
// 2. FLOW THANH TOÁN (PAYMENT FLOW)
// ============================================================

echo "🔄 FLOW THANH TOÁN MỚI\n";
echo "========================================\n";

$booking = [
    'booking_code' => 'BK-001',
    'room_number' => '101',
    'base_price' => 1000000,
    'check_in' => '2025-12-20',
    'check_out' => '2025-12-23'
];

$nights = calculateNights($booking['check_in'], $booking['check_out']);
$total_amount = $booking['base_price'] * $nights;
$deposit_amount = calculateDeposit($booking['base_price'], $nights);
$invoice_total = calculateInvoiceTotal($total_amount);

echo "Booking: " . htmlspecialchars($booking['booking_code']) . "\n";
echo "Phòng: " . htmlspecialchars($booking['room_number']) . "\n";
echo "Ngày: {$booking['check_in']} → {$booking['check_out']} ({$nights} đêm)\n";
echo "\n";

echo "💰 THANH TOÁN CỌCS (Deposit - 30%)\n";
echo "────────────────────────────────────\n";
echo "Giá/đêm: " . formatCurrency($booking['base_price']) . "\n";
echo "Số đêm: {$nights}\n";
echo "Subtotal: " . formatCurrency($total_amount) . "\n";
echo "Tiền cọc (30%): " . formatCurrency($deposit_amount) . " ✅\n";
echo "\n";

echo "💳 THANH TOÁN CUỐI (Final - 100% + VAT)\n";
echo "────────────────────────────────────────\n";
echo "Subtotal: " . formatCurrency($total_amount) . "\n";
echo "VAT (10%): " . formatCurrency($total_amount * 0.1) . "\n";
echo "Tổng Hóa Đơn: " . formatCurrency($invoice_total) . " ✅\n";
echo "\n";

echo "📊 TÓM TẮT\n";
echo "────────────────────────────────────────\n";
echo "Bước 1: Khách thanh toán cọc " . formatCurrency($deposit_amount) . "\n";
echo "Bước 2: Khách nhận phòng\n";
echo "Bước 3: Khách thanh toán phần còn lại " . formatCurrency($invoice_total - $deposit_amount) . "\n";
echo "Bước 4: Hoàn tất booking\n";
echo "\n";

// ============================================================
// 3. CODE REUSABILITY (TÁI SỬ DỤNG CODE)
// ============================================================

echo "📝 LỢI ÍCH CỦA HELPER FUNCTIONS\n";
echo "========================================\n";
echo "Trước:\n";
echo "  - Lặp lại công thức ở 10+ chỗ\n";
echo "  - Dễ nhầm lẫn (30% vs 0.3)\n";
echo "  - Khó bảo trì (thay đổi ở 10+ chỗ)\n";
echo "\n";
echo "Sau:\n";
echo "  - 1 hàm chính → tất cả sử dụng\n";
echo "  - Không nhầm lẫn\n";
echo "  - Thay đổi 1 chỗ → tất cả cập nhật\n";
echo "\n";

// ============================================================
// 4. SQL OPTIMIZATION
// ============================================================

echo "⚡ TỐI ƯU DATABASE QUERIES\n";
echo "========================================\n";
echo "Báo cáo dùng:\n";
echo "  - LEFT JOIN: Lấy tất cả phòng ngay cả khi chưa có booking\n";
echo "  - COUNT/SUM: Tính toán trên DB (nhanh hơn PHP)\n";
echo "  - GROUP BY: Nhóm dữ liệu theo loại phòng\n";
echo "  - DATEDIFF: Tính ngày trực tiếp trên MySQL\n";
echo "\n";

echo "Kết quả:\n";
echo "  ✅ Báo cáo load nhanh hơn\n";
echo "  ✅ Tiết kiệm memory\n";
echo "  ✅ Tăng accuracy\n";
echo "\n";

// ============================================================
// 5. SECURITY NOTES
// ============================================================

echo "🔒 BẢOẢM MẬT\n";
echo "========================================\n";
echo "Tất cả input được:\n";
echo "  ✓ esc() - HTML escape\n";
echo "  ✓ trim() - Xóa khoảng trắng\n";
echo "  ✓ Prepared Statements - SQL injection prevention\n";
echo "  ✓ Type casting - floatval(), intval()\n";
echo "\n";

echo "Tất cả payment logic được:\n";
echo "  ✓ Validate amount > 0\n";
echo "  ✓ Check payment_type có hợp lệ\n";
echo "  ✓ Verify booking ownership\n";
echo "  ✓ Log tất cả transactions\n";
echo "\n";

// ============================================================
// USAGE IN PRODUCTION
// ============================================================

echo "🚀 CÁCH SỬ DỤNG TRONG PRODUCTION\n";
echo "========================================\n";
echo "1. include 'includes/functions.php'\n";
echo "2. Gọi hàm:\n";
echo "   \$deposit = calculateDeposit(\$price, \$nights);\n";
echo "   \$total = calculateInvoiceTotal(\$subtotal);\n";
echo "   \$badge = getStatusBadge(\$status);\n";
echo "3. Không cần nhập công thức\n";
echo "\n";

?>
