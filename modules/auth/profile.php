<?php
/**
 * Trang hồ sơ cá nhân
 */

// Load constants first
require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireLogin();

$errors = [];
$success = false;
$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$user_info = getUserInfo($pdo, $user_id);

// Xử lý form update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Validate
        if (empty($full_name)) {
            $errors[] = 'Họ tên không được để trống';
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'Số điện thoại không hợp lệ';
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET full_name = :full_name, phone = :phone
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'full_name' => $full_name,
                    'phone' => $phone,
                    'id' => $user_id
                ]);
                
                // Update session
                $_SESSION['full_name'] = $full_name;
                
                $success = true;
                $user_info = getUserInfo($pdo, $user_id);
                setFlash('success', 'Cập nhật thông tin thành công');
                
            } catch (PDOException $e) {
                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
            }
        }
    } 
    elseif ($action == 'change_password') {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($old_password)) {
            $errors[] = 'Mật khẩu cũ không được để trống';
        }
        
        if (empty($new_password)) {
            $errors[] = 'Mật khẩu mới không được để trống';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Mật khẩu mới phải ít nhất 6 ký tự';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không trùng khớp';
        }
        
        // Verify old password
        if (empty($errors) && !password_verify($old_password, $user_info['password'])) {
            $errors[] = 'Mật khẩu cũ không chính xác';
        }
        
        if (empty($errors)) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET password = :password
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'password' => $hashed_password,
                    'id' => $user_id
                ]);
                
                setFlash('success', 'Đổi mật khẩu thành công');
                $success = true;
                
            } catch (PDOException $e) {
                $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
            }
        }
    }
}

// Lấy thông tin khách hàng nếu là customer
$customer_info = null;
if ($_SESSION['role'] == 'customer' && isset($_SESSION['customer_id'])) {
    $customer_info = getCustomerInfo($pdo, $_SESSION['customer_id']);
}

$page_title = 'Hồ sơ cá nhân';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Hồ sơ cá nhân</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle"></i> <?php echo esc($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Thông tin cơ bản -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h6>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Username:</strong> <?php echo esc($user_info['username']); ?></p>
                                <p><strong>Email:</strong> <?php echo esc($user_info['email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Vai trò:</strong> 
                                    <?php 
                                    $roles = [
                                        'admin' => 'Quản trị viên',
                                        'staff' => 'Nhân viên',
                                        'customer' => 'Khách hàng'
                                    ];
                                    echo esc($roles[$user_info['role']] ?? 'Không xác định');
                                    ?>
                                </p>
                                <p><strong>Trạng thái:</strong> 
                                    <span class="badge bg-<?php echo $user_info['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo $user_info['status'] == 'active' ? 'Hoạt động' : 'Vô hiệu hóa'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form cập nhật thông tin -->
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo esc($user_info['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo esc($user_info['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thông tin
                        </button>
                    </form>
                    
                    <!-- Thông tin khách hàng (nếu là customer) -->
                    <?php if ($_SESSION['role'] == 'customer' && $customer_info): ?>
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2"><i class="fas fa-id-card"></i> Thông tin khách hàng</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>CMND/CCCD:</label>
                                    <p><?php echo esc($customer_info['id_card'] ?? 'Chưa cập nhật'); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Hộ chiếu:</label>
                                    <p><?php echo esc($customer_info['passport'] ?? 'Chưa cập nhật'); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Ngày sinh:</label>
                                    <p><?php echo !empty($customer_info['date_of_birth']) ? formatDate($customer_info['date_of_birth']) : 'Chưa cập nhật'; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Quốc tịch:</label>
                                    <p><?php echo esc($customer_info['nationality'] ?? 'Chưa cập nhật'); ?></p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label>Địa chỉ:</label>
                                    <p><?php echo esc($customer_info['address'] ?? 'Chưa cập nhật'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Form đổi mật khẩu -->
                    <div class="border-top pt-3">
                        <h6 class="mb-3"><i class="fas fa-lock"></i> Đổi mật khẩu</h6>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Mật khẩu cũ</label>
                                <input type="password" class="form-control" id="old_password" name="old_password" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
