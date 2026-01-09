<?php
/**
 * Chỉnh sửa khách hàng
 */

require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';

requireRole([ROLE_ADMIN, ROLE_STAFF]);

$customer_id = $_GET['id'] ?? 0;
$errors = [];
$customer = null;

if (empty($customer_id)) {
    redirect('index.php', 'Khách hàng không tồn tại', 'danger');
}

try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.id as user_id, u.username, u.email, u.phone, u.full_name, u.status
        FROM customers c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = :id
    ");
    $stmt->execute(['id' => $customer_id]);
    $customer = $stmt->fetch();

    if (!$customer) {
        redirect('index.php', 'Khách hàng không tồn tại', 'danger');
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    redirect('index.php', 'Không thể tải dữ liệu khách hàng', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $status = $_POST['status'] ?? $customer['status'];

    $date_of_birth = $_POST['date_of_birth'] ?? null;
    $nationality = trim($_POST['nationality'] ?? '');
    $id_card = trim($_POST['id_card'] ?? '');
    $passport = trim($_POST['passport'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE users
                SET full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    status = :status
                WHERE id = :user_id
            ");
            $stmt->execute([
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'status' => $status,
                'user_id' => $customer['user_id']
            ]);

            $stmt = $pdo->prepare("
                UPDATE customers
                SET date_of_birth = :date_of_birth,
                    nationality = :nationality,
                    id_card = :id_card,
                    passport = :passport,
                    address = :address,
                    notes = :notes
                WHERE id = :id
            ");
            $stmt->execute([
                'date_of_birth' => $date_of_birth ?: null,
                'nationality' => $nationality,
                'id_card' => $id_card,
                'passport' => $passport,
                'address' => $address,
                'notes' => $notes,
                'id' => $customer_id
            ]);

            $pdo->commit();

            setFlash('success', 'Cập nhật khách hàng thành công');
            logActivity($pdo, $_SESSION['user_id'], 'EDIT_CUSTOMER', 'Chỉnh sửa khách hàng ' . $full_name);
            redirect('view.php?id=' . $customer_id);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}

$page_title = 'Chỉnh sửa khách hàng';
?>

<?php include_once ROOT_PATH . 'includes/header.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Chỉnh sửa khách hàng</h5>
                </div>
                <div class="card-body">
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
                                <label class="form-label">Họ tên *</label>
                                <input type="text" name="full_name" class="form-control" required
                                       value="<?php echo esc($_POST['full_name'] ?? $customer['full_name']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?php echo esc($_POST['email'] ?? $customer['email']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Điện thoại</label>
                                <input type="text" name="phone" class="form-control"
                                       value="<?php echo esc($_POST['phone'] ?? $customer['phone']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php echo ($_POST['status'] ?? $customer['status']) === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="inactive" <?php echo ($_POST['status'] ?? $customer['status']) === 'inactive' ? 'selected' : ''; ?>>Vô hiệu hóa</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                       value="<?php echo esc($_POST['date_of_birth'] ?? ($customer['date_of_birth'] ?? '')); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quốc tịch</label>
                                <input type="text" name="nationality" class="form-control"
                                       value="<?php echo esc($_POST['nationality'] ?? $customer['nationality']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CCCD</label>
                                <input type="text" name="id_card" class="form-control"
                                       value="<?php echo esc($_POST['id_card'] ?? $customer['id_card']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hộ chiếu</label>
                                <input type="text" name="passport" class="form-control"
                                       value="<?php echo esc($_POST['passport'] ?? $customer['passport']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="address" class="form-control"
                                   value="<?php echo esc($_POST['address'] ?? $customer['address']); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="3"><?php echo esc($_POST['notes'] ?? $customer['notes']); ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="view.php?id=<?php echo $customer_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_PATH . 'includes/footer.php'; ?>
