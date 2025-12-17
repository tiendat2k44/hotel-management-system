<?php
/**
 * Trang đăng ký
 */

require_once '../../config/database.php';
require_once '../../config/constants.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

// Nếu đã đăng nhập, redirect
if (isLoggedIn()) {
    redirect(BASE_URL);
}

$errors = [];
$success = false;

// Xử lý form đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate
    if (empty($username)) {
        $errors[] = 'Tên đăng nhập không được để trống';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Tên đăng nhập phải ít nhất 3 ký tự';
    }
    
    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($password)) {
        $errors[] = 'Mật khẩu không được để trống';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Mật khẩu phải ít nhất 6 ký tự';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không trùng khớp';
    }
    
    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (!empty($phone) && !validatePhone($phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    // Kiểm tra username hoặc email đã tồn tại
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $errors[] = 'Tên đăng nhập hoặc email đã được sử dụng';
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống';
        }
    }
    
    // Tạo tài khoản
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, full_name, phone, role, status)
                VALUES (:username, :password, :email, :full_name, :phone, :role, :status)
            ");
            
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password,
                'email' => $email,
                'full_name' => $full_name,
                'phone' => $phone,
                'role' => 'customer',
                'status' => 'active'
            ]);
            
            $user_id = $pdo->lastInsertId();
            
            // Tạo record khách hàng
            $stmt = $pdo->prepare("
                INSERT INTO customers (user_id)
                VALUES (:user_id)
            ");
            $stmt->execute(['user_id' => $user_id]);
            
            $success = true;
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi tạo tài khoản: ' . $e->getMessage();
        }
    }
}

$page_title = 'Đăng ký tài khoản';
?>

<?php include_once '../../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Đăng ký thành công! 
                    <a href="login.php">Đăng nhập ngay</a>
                </div>
            <?php else: ?>
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">
                            <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                        </h3>
                        
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
                                    <label for="full_name" class="form-label">Họ tên *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo esc($_POST['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo esc($_POST['phone'] ?? ''); ?>" 
                                           placeholder="Ví dụ: 0901234567">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo esc($_POST['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Tên đăng nhập *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo esc($_POST['username'] ?? ''); ?>" 
                                       placeholder="Ít nhất 3 ký tự" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Mật khẩu *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Ít nhất 6 ký tự" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu *</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </button>
                        </form>
                        
                        <hr>
                        
                        <p class="text-center mb-0">
                            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
