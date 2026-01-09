-- Thêm cột image_url vào bảng rooms
ALTER TABLE rooms ADD COLUMN image_url VARCHAR(500) AFTER notes;

-- Update một số phòng mẫu với ảnh (dùng placeholder images)
UPDATE rooms SET image_url = 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800' WHERE room_type_id = 1 LIMIT 2;
UPDATE rooms SET image_url = 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=800' WHERE room_type_id = 2 LIMIT 2;
UPDATE rooms SET image_url = 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800' WHERE room_type_id = 3 LIMIT 2;
