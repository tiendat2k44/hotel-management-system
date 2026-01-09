<?php
/**
 * File kiểm tra hệ thống hình ảnh phòng
 */

echo "=== KIỂM TRA HỆ THỐNG HÌNH ẢNH PHÒNG ===\n\n";

// 1. Kiểm tra thư mục uploads
echo "1. THƯ MỤC UPLOADS\n";
echo "─────────────────\n";

$upload_path = __DIR__ . '/assets/uploads/rooms/';
if (is_dir($upload_path)) {
    echo "✓ Thư mục uploads/rooms tồn tại\n";
    
    if (is_writable($upload_path)) {
        echo "✓ Thư mục uploads/rooms có quyền ghi\n";
    } else {
        echo "✗ Thư mục uploads/rooms KHÔNG có quyền ghi\n";
        echo "   Chạy: chmod 755 assets/uploads/rooms/\n";
    }
} else {
    echo "✗ Thư mục uploads/rooms KHÔNG tồn tại\n";
    echo "   Chạy: mkdir -p assets/uploads/rooms/\n";
}

// 2. Kiểm tra file được tạo
echo "\n2. DANH SÁCH HÌNH ẢNH ĐÃ UPLOAD\n";
echo "─────────────────────────────────\n";

if (is_dir($upload_path)) {
    $files = array_diff(scandir($upload_path), ['.', '..']);
    if (count($files) > 0) {
        foreach ($files as $file) {
            $filepath = $upload_path . $file;
            $size = filesize($filepath);
            $size_kb = round($size / 1024, 2);
            echo "  • $file ($size_kb KB)\n";
        }
        echo "\n✓ Tổng cộng: " . count($files) . " file\n";
    } else {
        echo "  Chưa có file nào được upload\n";
    }
}

// 3. Kiểm tra PHP extensions
echo "\n3. PHP EXTENSIONS\n";
echo "──────────────────\n";

$extensions = ['gd', 'pdo', 'pdo_mysql', 'fileinfo'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ $ext\n";
    } else {
        echo "✗ $ext KHÔNG được cài đặt\n";
    }
}

// 4. Kiểm tra file PHP
echo "\n4. FILE ĐÃ TẠO\n";
echo "───────────────\n";

$files_to_check = [
    'modules/admin/rooms/add.php',
    'modules/admin/rooms/edit.php',
    'modules/admin/rooms/index.php',
    'modules/admin/rooms/view.php',
    'modules/admin/gallery.php',
    'modules/customer/gallery.php'
];

foreach ($files_to_check as $file) {
    $filepath = __DIR__ . '/' . $file;
    if (file_exists($filepath)) {
        $syntax = shell_exec("php -l $filepath 2>&1");
        if (strpos($syntax, 'No syntax errors') !== false) {
            echo "✓ $file\n";
        } else {
            echo "✗ $file - CÓ LỖI CÚ PHÁP\n";
        }
    } else {
        echo "✗ $file - KHÔNG TỒN TẠI\n";
    }
}

// 5. Kiểm tra database
echo "\n5. DATABASE\n";
echo "────────────\n";

try {
    require_once 'config/database.php';
    
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'image_url'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Cột image_url tồn tại trong bảng rooms\n";
    } else {
        echo "✗ Cột image_url KHÔNG tồn tại\n";
        echo "   Chạy migration: php migrations/run_add_room_images.php\n";
    }
    
    // Đếm phòng có ảnh
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms WHERE image_url IS NOT NULL AND image_url != ''");
    $count = $stmt->fetch()['count'];
    echo "✓ Phòng có ảnh: $count\n";
    
} catch (Exception $e) {
    echo "✗ Lỗi kết nối database: " . $e->getMessage() . "\n";
}

echo "\n=== KẾT THÚC KIỂM TRA ===\n";
echo "\nĐể sử dụng hệ thống:\n";
echo "1. Admin: Vào Quản lý phòng → Thêm/Sửa phòng → Upload hình ảnh\n";
echo "2. Xem danh sách: /admin/rooms/ (có thumbnail)\n";
echo "3. Thư viện ảnh admin: /admin/gallery.php\n";
echo "4. Thư viện ảnh khách: /customer/gallery.php\n";
?>
