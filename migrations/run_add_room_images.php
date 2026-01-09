<?php
/**
 * Migration: Thêm cột image_url vào bảng rooms
 */

require_once __DIR__ . '/../config/database.php';

try {
    echo "Bắt đầu migration...\n";
    
    // Kiểm tra xem cột đã tồn tại chưa
    $stmt = $pdo->query("SHOW COLUMNS FROM rooms LIKE 'image_url'");
    if ($stmt->rowCount() > 0) {
        echo "Cột image_url đã tồn tại, bỏ qua...\n";
    } else {
        // Thêm cột image_url
        $pdo->exec("ALTER TABLE rooms ADD COLUMN image_url VARCHAR(500) AFTER notes");
        echo "✓ Đã thêm cột image_url vào bảng rooms\n";
    }
    
    // Cập nhật một số phòng mẫu với ảnh
    $images = [
        'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=800',
        'https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800'
    ];
    
    $stmt = $pdo->query("SELECT id FROM rooms WHERE image_url IS NULL OR image_url = '' LIMIT 10");
    $rooms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $count = 0;
    foreach ($rooms as $room_id) {
        $image_url = $images[$count % count($images)];
        $stmt = $pdo->prepare("UPDATE rooms SET image_url = :image_url WHERE id = :id");
        $stmt->execute(['image_url' => $image_url, 'id' => $room_id]);
        $count++;
    }
    
    echo "✓ Đã cập nhật $count phòng với hình ảnh mẫu\n";
    echo "\nMigration hoàn thành!\n";
    
} catch (PDOException $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
