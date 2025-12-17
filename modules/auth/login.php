<?php
/**
 * Trang đăng nhập
 */

// Khởi tạo
// Load constants first
require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

// Nếu đã đăng nhập, redirect tới dashboard
if (isLoggedIn()) {
    if (hasRole(ROLE_ADMIN)) {
        redirect(ADMIN_URL . 'dashboard.php');
    } elseif (hasRole(ROLE_STAFF)) {
        redirect(STAFF_URL . 'dashboard.php');
    } else {
        redirect(CUSTOMER_URL . 'dashboard.php');
    }
}

$errors = [];
$success = false;

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate
    if (empty($username)) {
        $errors[] = 'Tên đăng nhập không được để trống';
    }
    if (empty($password)) {
        $errors[] = 'Mật khẩu không được để trống';
    }
    
    if (empty($errors)) {
        try {
            // Kiểm tra user
            $stmt = $pdo->prepare("
                SELECT u.*, 
                CASE WHEN c.id IS NOT NULL THEN c.id ELSE NULL END as customer_id
                FROM users u
                LEFT JOIN customers c ON u.id = c.user_id
                WHERE (u.username = :username OR u.email = :email) 
                AND u.status = 'active'
            ");
            
            $stmt->execute([
                'username' => $username,
                'email' => $username
            ]);
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Tái tạo session ID sau khi xác thực để tránh session fixation
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['customer_id'] = $user['customer_id'];
                
                // Remember me
                if ($remember) {
                    setcookie('remember_token', hash('sha256', $user['id'] . $user['username']), 
                              time() + REMEMBER_ME_DURATION, '/');
                }
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $stmt->execute(['id' => $user['id']]);
                
                logActivity($pdo, $user['id'], 'LOGIN', 'Đăng nhập thành công');
                
                // Redirect
                if ($user['role'] == 'admin') {
                    redirect(ADMIN_URL . 'dashboard.php', 'Đăng nhập thành công');
                } elseif ($user['role'] == 'staff') {
                    redirect(STAFF_URL . 'dashboard.php', 'Đăng nhập thành công');
                } else {
                    redirect(CUSTOMER_URL . 'dashboard.php', 'Đăng nhập thành công');
                }
            } else {
                $errors[] = 'Tên đăng nhập hoặc mật khẩu không chính xác';
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Đăng nhập';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </h3>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><i class="fas fa-exclamation-circle"></i> <?php echo esc($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Tên đăng nhập hoặc Email</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo esc($_POST['username'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ghi nhớ đăng nhập
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </form>
                    
                    <hr>
                    
                    <p class="text-center mb-0">
                        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                    </p>
                </div>
            </div>
            
            <!-- Demo credentials -->
            <div class="card mt-3 bg-light">
                <div class="card-body">
                    <h6 class="card-title">Tài khoản demo:</h6>
                    <ul class="list-unstyled small mb-2">
                        <li><strong>Admin:</strong> admin / 123456</li>
                        <li><strong>Staff:</strong> staff1 / 123456</li>
                        <li><strong>Customer:</strong> customer1 / 123456</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
