# Hướng dẫn sử dụng Hệ thống Hình ảnh Phòng

## 📸 Tính năng mới

### 1. Upload hình ảnh từ máy tính
- Admin có thể upload hình ảnh JPG, PNG, GIF, hoặc WebP (tối đa 5MB)
- Hình ảnh được lưu trên server tại `/assets/uploads/rooms/`
- Tự động tạo preview trước khi lưu

### 2. Thêm hình ảnh từ URL internet
- Nếu muốn dùng ảnh từ các trang như Unsplash, Pexels, v.v.
- Chỉ cần nhập URL, không cần upload

### 3. Thư viện ảnh
- **Admin**: `/admin/gallery.php` - Xem tất cả ảnh phòng, quản lý
- **Khách**: `/customer/gallery.php` - Xem ảnh phòng trước khi đặt

### 4. Xem ảnh lớn
- Click vào ảnh thumbnail hoặc nút "Xem ảnh lớn"
- Modal popup hiển thị ảnh ở kích thước full

---

## 🚀 Cách sử dụng

### A. Admin thêm/sửa hình ảnh phòng

#### Thêm phòng mới:
1. Vào **Quản lý phòng** → **Thêm phòng**
2. Điền thông tin phòng
3. Ở phần "Hình ảnh phòng":
   - **Cách 1**: Upload file ảnh từ máy (khuyến nghị)
     - Click "Chọn file" → Chọn ảnh JPG/PNG/GIF/WebP (tối đa 5MB)
     - Xem preview
     - Click "Thêm"
   
   - **Cách 2**: Nhập URL ảnh từ internet
     - Nếu không upload file, nhập URL vào trường "Hoặc nhập URL hình ảnh"
     - Click "Thêm"

#### Sửa phòng:
1. Vào **Quản lý phòng** → Click **Sửa** (<i class="fas fa-edit"></i>)
2. Ở phần "Hình ảnh phòng":
   - **Thay ảnh mới**: Upload file mới (ảnh cũ sẽ tự động xóa)
   - **Giữ ảnh cũ**: Để trống field upload
   - **Đổi sang URL**: Nhập URL mới vào trường "URL hình ảnh"

### B. Xem danh sách phòng với thumbnail

1. Vào **Quản lý phòng**
2. Cột đầu tiên hiển thị thumbnail ảnh (60x60px)
3. Click vào ảnh hoặc nút **Xem chi tiết** → Trang chi tiết phòng
4. Xem ảnh phòng ở kích thước lớn

### C. Thư viện ảnh Admin

1. Vào **Admin** → **Thư viện ảnh**
2. Xem tất cả ảnh phòng dạng grid
3. Badge "URL" hoặc "Server" cho biết ảnh từ đâu
4. Click vào ảnh → Xem ảnh lớn
5. Nút "Sửa" để chỉnh sửa phòng
6. Nút expand để xem ảnh lớn

### D. Khách hàng xem ảnh phòng

1. Vào **Thư viện ảnh phòng** (không cần đăng nhập)
2. Xem tất cả phòng có ảnh
3. Xem thông tin: loại phòng, tầng, sức chứa, giá
4. Click vào ảnh hoặc nút "Xem ảnh lớn" → Modal popup

---

## 📁 Cấu trúc thư mục

```
assets/
├── uploads/
│   └── rooms/          # Lưu ảnh phòng được upload
│       ├── room_1703...jpg
│       ├── room_1703...png
│       └── ...
└── images/

modules/
├── admin/
│   ├── rooms/
│   │   ├── add.php     # Thêm phòng (có upload ảnh)
│   │   ├── edit.php    # Sửa phòng (có upload ảnh)
│   │   ├── index.php   # Danh sách phòng (hiển thị thumbnail)
│   │   └── view.php    # Chi tiết phòng (hiển thị ảnh lớn)
│   └── gallery.php     # Thư viện ảnh admin
└── customer/
    └── gallery.php     # Thư viện ảnh khách hàng
```

---

## ✅ Yêu cầu kỹ thuật

- **Định dạng ảnh hỗ trợ**: JPG, PNG, GIF, WebP
- **Kích thước tối đa**: 5MB
- **Thư mục upload phải có quyền ghi**: `assets/uploads/rooms/`

### Thiết lập quyền thư mục (Linux/Mac):
```bash
chmod 755 assets/uploads/rooms/
```

---

## 🔍 Xử lý lỗi

### Lỗi: "Lỗi khi lưu file hình ảnh"
- Kiểm tra thư mục `assets/uploads/rooms/` có tồn tại không
- Kiểm tra quyền ghi của thư mục (phải 755 trở lên)

### Lỗi: "Định dạng hình ảnh không hợp lệ"
- Chỉ hỗ trợ: JPG, PNG, GIF, WebP
- Kiểm tra lại định dạng file

### Lỗi: "Kích thước hình ảnh quá lớn"
- File > 5MB
- Nén ảnh trước khi upload

### Ảnh không hiển thị
- Nếu ảnh từ URL internet: kiểm tra URL có đúng không, có bị chặn không
- Nếu ảnh từ server: kiểm tra file có tồn tại ở `/assets/uploads/rooms/`

---

## 💡 Mẹo sử dụng

1. **Upload ảnh từ server sẽ nhanh hơn** URL internet
2. **Nén ảnh trước khi upload** để tăng tốc độ tải
3. **Sử dụng ảnh cùng tỷ lệ** (vd: 4:3 hoặc 16:9) để giao diện đẹp hơn
4. **Backup ảnh** trước khi sửa phòng có ảnh cũ

---

## 🎨 Tùy chỉnh

### Thay đổi kích thước ảnh tối đa

Sửa file `modules/admin/rooms/add.php` dòng ~35:
```php
$max_size = 10 * 1024 * 1024; // 10MB
```

### Thêm loại file được phép

Sửa file `modules/admin/rooms/add.php` dòng ~32:
```php
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
```

---

**Được tạo ngày**: 17-12-2025
**Phiên bản**: 1.0
