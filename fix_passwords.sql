-- ============================================
-- FIX PASSWORD CHO TÀI KHOẢN DEMO
-- Chạy file này nếu đã import database.sql cũ
-- ============================================

USE hotel_management_db;

-- Update password cho 3 tài khoản demo (password: 123456)
UPDATE users SET password = '$2y$10$EeT0xFdRYL0GQHeA0EcPb.e/Yqd3jvtK9HcN2zf2VijXC2cYkByz6' WHERE username = 'admin';
UPDATE users SET password = '$2y$10$B4VrXAyAJR6TQCs6bpMTt.Oj9ZCIgwT1BVHnFIlGmadYvdrDDMPNS' WHERE username = 'staff1';
UPDATE users SET password = '$2y$10$gRHOYm8efcz4iLC729Fxvu0.cFQrc1iDVTF8Yh4/zq/XpKic936c.' WHERE username = 'customer1';

-- Kiểm tra kết quả
SELECT username, email, role, 
       CASE 
           WHEN username = 'admin' AND password = '$2y$10$EeT0xFdRYL0GQHeA0EcPb.e/Yqd3jvtK9HcN2zf2VijXC2cYkByz6' THEN 'OK'
           WHEN username = 'staff1' AND password = '$2y$10$B4VrXAyAJR6TQCs6bpMTt.Oj9ZCIgwT1BVHnFIlGmadYvdrDDMPNS' THEN 'OK'
           WHEN username = 'customer1' AND password = '$2y$10$gRHOYm8efcz4iLC729Fxvu0.cFQrc1iDVTF8Yh4/zq/XpKic936c.' THEN 'OK'
           ELSE 'OUTDATED'
       END as status
FROM users 
WHERE username IN ('admin', 'staff1', 'customer1');
