<?php
/**
 * Quick links - Hướng dẫn nhanh sử dụng
 */

$base_url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
$base_url = rtrim($base_url, '/') . '/';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Hình Ảnh Phòng - Hướng Dẫn Nhanh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 900px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            border: none;
        }
        .card-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        .card-header p {
            margin: 10px 0 0 0;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        .section {
            padding: 25px;
            border-bottom: 1px solid #eee;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section h3 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .link-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
        }
        .link-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        .link-item a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            flex-grow: 1;
        }
        .link-item a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        .link-item .badge {
            margin-left: 10px;
        }
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .feature-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .feature-item strong {
            color: #667eea;
        }
        .code-block {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
            margin: 10px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        footer {
            text-align: center;
            color: white;
            margin-top: 30px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-images"></i> Hệ Thống Hình Ảnh Phòng</h1>
                <p>Hướng dẫn nhanh - Nâng cấp hoàn tất 🎉</p>
            </div>

            <div class="card-body">
                <!-- Thông tin chung -->
                <div class="section">
                    <h3><i class="fas fa-info-circle"></i> Thông Tin Chung</h3>
                    <div class="feature-list">
                        <div class="feature-item">
                            <strong>Upload ảnh từ máy</strong>
                            <p class="mb-0 mt-1">JPG, PNG, GIF, WebP - tối đa 5MB</p>
                        </div>
                        <div class="feature-item">
                            <strong>Lưu trữ server</strong>
                            <p class="mb-0 mt-1">/assets/uploads/rooms/</p>
                        </div>
                        <div class="feature-item">
                            <strong>Hỗ trợ URL internet</strong>
                            <p class="mb-0 mt-1">https://example.com/image.jpg</p>
                        </div>
                        <div class="feature-item">
                            <strong>Mobile responsive</strong>
                            <p class="mb-0 mt-1">Tất cả trang đều responsive</p>
                        </div>
                    </div>
                </div>

                <!-- Links Admin -->
                <div class="section">
                    <h3><i class="fas fa-lock"></i> Liên Kết Admin</h3>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>modules/admin/rooms/add.php" target="_blank">
                            <i class="fas fa-plus"></i> Thêm Phòng Mới (upload ảnh)
                        </a>
                        <span class="badge bg-primary">NEW</span>
                    </div>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>modules/admin/rooms/index.php" target="_blank">
                            <i class="fas fa-list"></i> Danh Sách Phòng (có thumbnail)
                        </a>
                        <span class="badge bg-primary">UPDATE</span>
                    </div>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>modules/admin/gallery.php" target="_blank">
                            <i class="fas fa-images"></i> Thư Viện Ảnh Admin
                        </a>
                        <span class="badge bg-success">NEW</span>
                    </div>
                </div>

                <!-- Links Khách -->
                <div class="section">
                    <h3><i class="fas fa-eye"></i> Liên Kết Khách Hàng</h3>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>modules/customer/gallery.php" target="_blank">
                            <i class="fas fa-camera"></i> Thư Viện Ảnh Phòng (công khai)
                        </a>
                        <span class="badge bg-success">NEW</span>
                    </div>
                </div>

                <!-- Hướng dẫn nhanh -->
                <div class="section">
                    <h3><i class="fas fa-book"></i> Hướng Dẫn Nhanh</h3>
                    
                    <strong class="d-block mb-3">1️⃣ Thêm Ảnh Cho Phòng</strong>
                    <p>Vào <strong>Thêm Phòng</strong> hoặc <strong>Sửa Phòng</strong>:</p>
                    <div class="code-block">
Chọn loại phòng → Tầng → Phần "Hình ảnh phòng"
→ Upload file từ máy HOẶC nhập URL
→ Click "Thêm" hoặc "Cập nhật"
                    </div>

                    <strong class="d-block mb-3 mt-4">2️⃣ Xem Ảnh Admin</strong>
                    <p>Các cách xem:</p>
                    <ul class="mb-3">
                        <li>Danh sách phòng: Click thumbnail (60x60px)</li>
                        <li>Chi tiết phòng: Xem ảnh lớn (400px)</li>
                        <li>Thư viện ảnh: Grid view tất cả phòng</li>
                    </ul>

                    <strong class="d-block mb-3">3️⃣ Xem Ảnh Khách Hàng</strong>
                    <p>Truy cập: <strong>Thư Viện Ảnh Phòng</strong> (không cần đăng nhập)</p>
                </div>

                <!-- Tài liệu -->
                <div class="section">
                    <h3><i class="fas fa-file-alt"></i> Tài Liệu Chi Tiết</h3>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>QUICK_START_IMAGES.md" target="_blank">
                            <i class="fas fa-rocket"></i> Quick Start Guide
                        </a>
                        <span class="badge bg-info">README</span>
                    </div>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>ROOM_IMAGES_GUIDE.md" target="_blank">
                            <i class="fas fa-question-circle"></i> Hướng Dẫn Chi Tiết
                        </a>
                        <span class="badge bg-info">DOCS</span>
                    </div>
                    <div class="link-item">
                        <a href="<?php echo $base_url; ?>ROOM_IMAGES_UPGRADE_COMPLETE.md" target="_blank">
                            <i class="fas fa-check-circle"></i> Tóm Tắt Nâng Cấp
                        </a>
                        <span class="badge bg-info">SUMMARY</span>
                    </div>
                </div>

                <!-- Kiểm tra -->
                <div class="section">
                    <h3><i class="fas fa-stethoscope"></i> Kiểm Tra Hệ Thống</h3>
                    <p>Chạy lệnh để kiểm tra:</p>
                    <div class="code-block">
php check_room_images.php
                    </div>
                    <p class="text-muted small mt-2">Sẽ kiểm tra: thư mục, file, PHP extensions, database</p>
                </div>

                <!-- Status -->
                <div class="section bg-light">
                    <div class="text-center">
                        <div class="status-badge status-success">
                            <i class="fas fa-check-circle"></i> Hệ Thống Sẵn Sàng
                        </div>
                        <p class="text-muted mt-3 mb-0">Tất cả tính năng đã được kiểm tra và sẵn sàng sử dụng</p>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <p>
                Hotel Management System v1.0 | 
                Room Images Feature | 
                Created: 17-12-2025
            </p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
