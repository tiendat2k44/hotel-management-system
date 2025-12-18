<?php
/**
 * Các hàm chung của hệ thống
 */

/**
 * Kiểm tra xem người dùng đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kiểm tra quyền của người dùng
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] == $role;
}

/**
 * Redirect tới trang đăng nhập nếu chưa đăng nhập
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'modules/auth/login.php');
        exit();
    }
}

/**
 * Kiểm tra quyền truy cập (admin, staff, customer)
 * Chuyển hướng về trang chủ nếu không đủ quyền
 */
function requireRole($roles) {
    requireLogin();
    
    // Chuẩn hóa role về chữ thường để so sánh không phân biệt hoa/thường
    $normalize = function ($value) {
        return strtolower(trim($value));
    };
    
    $userRole = isset($_SESSION['role']) ? $normalize($_SESSION['role']) : '';
    
    if (is_string($roles)) {
        $roles = array($roles);
    }
    
    $allowedRoles = array_map($normalize, $roles);
    
    if (!in_array($userRole, $allowedRoles, true)) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

/**
 * Escape HTML output để tránh XSS (Cross-Site Scripting)
 * Chuyển các ký tự đặc biệt thành HTML entities
 * VD: <script> thành &lt;script&gt;
 */
function esc($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Format tiền tệ Việt Nam
 * VD: 1000000 -> 1.000.000 đ
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' ' . CURRENCY_SYMBOL;
}

/**
 * Format ngày theo định dạng Việt Nam
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format ngày giờ
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Validate email
 * Kiểm tra email có đúng định dạng hay không
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Việt Nam)
 * Chấp nhận: 0123456789 hoặc +84123456789 (9-10 số)
 */
function validatePhone($phone) {
    return preg_match('/^(0|\+84)(\d{9,10})$/', $phone);
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

/**
 * Generate unique booking code
 * Tạo mã đặt phòng duy nhất dạng: BK20231215143022[random]
 */
function generateBookingCode() {
    return 'BK' . date('YmdHis') . rand(1000, 9999);
}

/**
 * Generate unique payment code
 * Tạo mã thanh toán duy nhất dạng: PY20231215143022[random]
 */
function generatePaymentCode() {
    return 'PY' . date('YmdHis') . rand(1000, 9999);
}

/**
 * Generate unique invoice code
 * Tạo mã hóa đơn dạng: INV202312[random]
 */
function generateInvoiceCode() {
    return 'INV' . date('Ym') . rand(10000, 99999);
}

/**
 * Kiểm tra phòng còn trống trong khoảng thời gian check_in -> check_out
 * Return: true nếu phòng trống, false nếu đã có booking trùng
 */
function isRoomAvailable($pdo, $room_id, $check_in, $check_out) {
    try {
        // Đếm số booking trùng ngày với phòng này
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE room_id = :room_id 
            AND status IN ('confirmed', 'checked_in')  -- Chỉ kiểm tra booking đang hoạt động
            AND (
                (check_in < :check_out AND check_out > :check_in)  -- Điều kiện trùng khoảng thời gian
            )
        ");
        
        $stmt->execute([
            'room_id' => $room_id,
            'check_in' => $check_in,
            'check_out' => $check_out
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] == 0;
        
    } catch (PDOException $e) {
        error_log("Lỗi kiểm tra phòng: " . $e->getMessage());
        return false;
    }
}

/**
 * Tính số đêm ở
 */
function calculateNights($check_in, $check_out) {
    $from = new DateTime($check_in);
    $to = new DateTime($check_out);
    $interval = $from->diff($to);
    return $interval->days;
}

/**
 * Tính tổng tiền phòng
 */
function calculateRoomTotal($price, $nights) {
    return $price * $nights;
}

/**
 * Flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Lấy flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect
 */
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        setFlash($type, $message);
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Validate CSRF token
 */
function validateCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF token
 */
function generateCsrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Lấy user info từ database
 */
function getUserInfo($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Lỗi lấy user info: " . $e->getMessage());
        return null;
    }
}

/**
 * Lấy customer info từ database
 */
function getCustomerInfo($pdo, $customer_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
        $stmt->execute(['id' => $customer_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Lỗi lấy customer info: " . $e->getMessage());
        return null;
    }
}

/**
 * Log activity
 */
function logActivity($pdo, $user_id, $action, $description = '') {
    // Có thể thêm bảng activity log nếu cần
    error_log("[" . date('Y-m-d H:i:s') . "] User {$user_id}: {$action} - {$description}");
}

/**
 * Tính tiền cọc (30% giá phòng)
 */
function calculateDeposit($base_price, $nights = 1) {
    return $base_price * $nights * 0.3;
}

/**
 * Tính tổng hóa đơn (gồm VAT 10%)
 */
function calculateInvoiceTotal($base_amount) {
    return $base_amount * 1.1;
}

/**
 * Format status badge cho booking
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => ['class' => 'warning', 'text' => 'Chờ xác nhận'],
        'confirmed' => ['class' => 'info', 'text' => 'Đã xác nhận'],
        'checked_in' => ['class' => 'success', 'text' => 'Đã nhận phòng'],
        'checked_out' => ['class' => 'secondary', 'text' => 'Đã trả phòng'],
        'cancelled' => ['class' => 'danger', 'text' => 'Đã hủy']
    ];
    
    $badge = $badges[$status] ?? ['class' => 'secondary', 'text' => 'Không xác định'];
    return '<span class="badge bg-' . $badge['class'] . '">' . $badge['text'] . '</span>';
}

/**
 * Format payment method
 */
function getPaymentMethodLabel($method) {
    $methods = [
        'cash' => 'Tiền mặt',
        'bank_transfer' => 'Chuyển khoản',
        'credit_card' => 'Thẻ tín dụng'
    ];
    return $methods[$method] ?? 'N/A';
}

/**
 * Format payment type
 */
function getPaymentTypeLabel($type) {
    $types = [
        'deposit' => 'Tiền cọc',
        'final' => 'Thanh toán cuối'
    ];
    return $types[$type] ?? 'N/A';
}

/**
 * Check booking conflict (kiểm tra phòng có bị trùng không)
 */
function checkBookingConflict($pdo, $room_id, $check_in, $check_out, $exclude_booking_id = null) {
    try {
        $query = "
            SELECT COUNT(*) as count FROM bookings
            WHERE room_id = :room_id
            AND status IN ('pending', 'confirmed', 'checked_in')
            AND check_in < :check_out
            AND check_out > :check_in
        ";
        
        $params = [
            'room_id' => $room_id,
            'check_in' => $check_in,
            'check_out' => $check_out
        ];
        
        if ($exclude_booking_id) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_booking_id;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch()['count'] > 0;
    } catch (PDOException $e) {
        error_log("Lỗi kiểm tra conflict: " . $e->getMessage());
        return false;
    }
}


?>
