# Các Tính Năng Chi Tiết - Hotel Management System

## 1. Hệ Thống Xác Thực & Phân Quyền

### 1.1 Đăng Ký Tài Khoản
- ✅ Đăng ký người dùng mới
- ✅ Validation email và username
- ✅ Validation password (ít nhất 6 ký tự)
- ✅ Tạo tài khoản khách hàng tự động
- ✅ Hashing password với BCRYPT

**Đường dẫn:** `/modules/auth/register.php`

### 1.2 Đăng Nhập
- ✅ Đăng nhập bằng username hoặc email
- ✅ Session management
- ✅ Remember me (30 ngày)
- ✅ Lưu thông tin đăng nhập gần đây
- ✅ Timeout session (30 phút)
- ✅ Redirect theo vai trò

**Đường dẫn:** `/modules/auth/login.php`

### 1.3 Quên Mật Khẩu
- Tính năng đang phát triển

### 1.4 Hồ Sơ Cá Nhân
- ✅ Xem thông tin cá nhân
- ✅ Chỉnh sửa thông tin (tên, số điện thoại)
- ✅ Đổi mật khẩu
- ✅ Thông tin khách hàng (CMND, Hộ chiếu, Địa chỉ)
- ✅ Validation dữ liệu

**Đường dẫn:** `/modules/auth/profile.php`

### 1.5 Phân Quyền

#### Admin (Quản trị viên)
- ✅ Quản lý toàn bộ hệ thống
- ✅ Thêm/sửa/xóa phòng
- ✅ Quản lý loại phòng
- ✅ Quản lý dịch vụ
- ✅ Xem tất cả booking
- ✅ Xem báo cáo doanh thu
- ✅ Thống kê chi tiết

#### Nhân Viên (Staff)
- ✅ Xem danh sách phòng
- ✅ Tạo booking
- ✅ Check-in / Check-out
- ✅ Xử lý thanh toán
- ✅ Ghi nhận dịch vụ sử dụng
- ✅ Xem booking gần đây

#### Khách Hàng (Customer)
- ✅ Xem thông tin phòng công khai
- ✅ Xem lịch sử booking
- ✅ Xem chi tiết booking
- ✅ Quản lý hồ sơ cá nhân

## 2. Quản Lý Phòng (Admin)

### 2.1 CRUD Phòng
- ✅ **Thêm phòng:**
  - Số phòng (unique)
  - Loại phòng
  - Tầng
  - Trạng thái
  - Ghi chú

- ✅ **Sửa phòng:**
  - Chỉnh sửa tất cả thông tin
  - Kiểm tra conflict

- ✅ **Xóa phòng:**
  - Kiểm tra booking đang hoạt động
  - Ngăn xóa nếu có booking chưa hoàn thành
  - Soft delete (tùy chọn)

- ✅ **Xem danh sách:**
  - Pagination
  - Tìm kiếm

**Đường dẫn:** `/modules/admin/rooms/`

### 2.2 Trạng Thái Phòng
- **Available:** Phòng trống, sẵn sàng cho đặt phòng
- **Occupied:** Khách đang sử dụng
- **Cleaning:** Đang được dọn dẹp
- **Maintenance:** Bảo trì, không thể đặt

### 2.3 Loại Phòng (Room Types)
- ✅ Standard: Phòng tiêu chuẩn
- ✅ Deluxe: Phòng sang trọng
- ✅ Suite: Phòng cao cấp
- ✅ Tùy chỉnh thêm loại phòng mới

Mỗi loại phòng có:
- Tên loại phòng
- Mô tả chi tiết
- Giá cơ bản (₫/đêm)
- Sức chứa (số người)
- Tiện nghi

### 2.4 Tìm Kiếm & Lọc Phòng
- ✅ Tìm theo số phòng
- ✅ Lọc theo loại phòng
- ✅ Lọc theo trạng thái
- ✅ Lọc theo tầng
- ✅ Kết hợp nhiều bộ lọc

## 3. Quản Lý Đặt Phòng

### 3.1 Tạo Booking (Staff/Admin)
- ✅ Chọn khách hàng từ danh sách
- ✅ Chọn ngày check-in/check-out
- ✅ Kiểm tra phòng trống (AJAX)
- ✅ Tính giá tự động
- ✅ Nhập số người lớn/trẻ em
- ✅ Yêu cầu đặc biệt
- ✅ Tiền cọc
- ✅ Tạo mã booking unique
- ✅ Ghi log hoạt động

### 3.2 Xem Chi Tiết Booking
- ✅ Thông tin khách hàng
- ✅ Thông tin phòng
- ✅ Thời gian ở (số đêm)
- ✅ Dịch vụ sử dụng
- ✅ Tính toán tổng tiền
- ✅ Trạng thái thanh toán
- ✅ Ghi chú & yêu cầu đặc biệt

### 3.3 Cập Nhật Booking
- ✅ Chỉnh sửa thông tin khách
- ✅ Cập nhật số người
- ✅ Thay đổi yêu cầu đặc biệt
- ✅ Cập nhật tiền cọc

### 3.4 Check-in
- ✅ Xác nhận khách đến
- ✅ Cập nhật thời gian check-in thực tế
- ✅ Thay đổi trạng thái phòng → Occupied
- ✅ Ghi log hoạt động

### 3.5 Check-out
- ✅ Xác nhận khách trả phòng
- ✅ Cập nhật thời gian check-out thực tế
- ✅ Tính tổng tiền cuối cùng
- ✅ Tạo hóa đơn
- ✅ Thay đổi trạng thái phòng → Cleaning
- ✅ Ghi log hoạt động

### 3.6 Hủy Booking
- ✅ Hủy booking chưa hoàn thành
- ✅ Tính phí hủy (tùy chọn)
- ✅ Thay đổi trạng thái
- ✅ Cập nhật trạng thái phòng

### 3.7 Tìm Kiếm Booking
- ✅ Tìm theo mã booking
- ✅ Tìm theo tên khách hàng
- ✅ Lọc theo trạng thái
- ✅ Lọc theo ngày check-in
- ✅ Kết hợp nhiều điều kiện

**Đường dẫn:** `/modules/admin/bookings/`

## 4. Quản Lý Dịch Vụ

### 4.1 CRUD Dịch Vụ
- ✅ **Danh sách dịch vụ:**
  - Tên dịch vụ
  - Mô tả
  - Giá
  - Đơn vị
  - Trạng thái (hoạt động/vô hiệu)

- ✅ **Thêm dịch vụ:**
  - Ăn uống
  - Giặt ủi
  - Spa / Massage
  - Đưa đón sân bay
  - Internet
  - Tùy chỉnh thêm

- ✅ **Sửa/Xóa dịch vụ**

### 4.2 Ghi Nhận Dịch Vụ Sử Dụng
- ✅ Thêm dịch vụ vào booking
- ✅ Chọn số lượng
- ✅ Tính giá tự động (số lượng × đơn giá)
- ✅ Ngày sử dụng
- ✅ Ghi chú thêm
- ✅ Xóa dịch vụ đã thêm

### 4.3 Tính Giá Tự Động
- ✅ Công thức: Số lượng × Đơn giá
- ✅ Cộng dồn vào tổng tiền booking

**Đường dẫn:** `/modules/admin/services/`

## 5. Thanh Toán & Hóa Đơn

### 5.1 Ghi Nhận Thanh Toán
- ✅ Mã thanh toán unique
- ✅ Phương thức thanh toán:
  - Tiền mặt
  - Chuyển khoản
  - Thẻ tín dụng
- ✅ Loại thanh toán:
  - Cọc (deposit)
  - Thanh toán cuối cùng (final)
  - Hoàn tiền (refund)
- ✅ Trạng thái:
  - Đang chờ (pending)
  - Đã hoàn tất (completed)
  - Thất bại (failed)
- ✅ Ghi chú
- ✅ Người xử lý

### 5.2 Tạo Hóa Đơn
- ✅ **Tự động tạo khi check-out**
- ✅ Mã hóa đơn unique (INV-YYYYMM-XXXXX)
- ✅ **Nội dung hóa đơn:**
  - Thông tin khách hàng
  - Thông tin phòng
  - Chi tiết dịch vụ
  - Subtotal
  - VAT (10%)
  - Tổng tiền
  - Trạng thái thanh toán

### 5.3 Tính VAT
- ✅ VAT 10% tự động
- ✅ Công thức: (Subtotal × 10) / 100
- ✅ Cộng vào tổng tiền

### 5.4 Xuất PDF
- Tính năng đang phát triển
- Sử dụng TCPDF hoặc Dompdf

### 5.5 Nhiều Phương Thức Thanh Toán
- ✅ Tiền mặt
- ✅ Chuyển khoản ngân hàng
- ✅ Thẻ tín dụng
- ✅ Tích hợp payment gateway (trong tương lai)

## 6. Báo Cáo & Thống Kê

### 6.1 Dashboard Admin
- ✅ **Thống kê nhanh:**
  - Tổng phòng
  - Phòng trống
  - Booking hôm nay
  - Doanh thu tháng này

- ✅ **Booking gần đây:**
  - Danh sách 10 booking mới nhất
  - Mã booking
  - Khách hàng
  - Phòng
  - Trạng thái

- ✅ **Phòng đang sử dụng:**
  - Danh sách phòng occupied
  - Khách tương ứng
  - Ngày check-out

### 6.2 Báo Cáo Doanh Thu
- ✅ Doanh thu theo ngày
- ✅ Doanh thu theo tháng
- ✅ Doanh thu theo năm
- ✅ Biểu đồ doanh thu
- ✅ So sánh giữa các kỳ

### 6.3 Báo Cáo Tỷ Lệ Lấp Đầy
- ✅ % phòng được đặt
- ✅ % phòng trống
- ✅ % phòng bảo trì

### 6.4 Báo Cáo Khách Hàng
- ✅ Khách hàng mới
- ✅ Khách hàng quay lại
- ✅ Top khách hàng (đặt nhiều)
- ✅ Tổng tiêu xài của khách

### 6.5 Báo Cáo Dịch Vụ
- ✅ Dịch vụ phổ biến
- ✅ Doanh thu từ dịch vụ
- ✅ Dịch vụ ít dùng

**Đường dẫn:** `/modules/admin/reports/` (đang phát triển)

## 7. Giao Diện Công Khai

### 7.1 Trang Chủ
- ✅ Header với logo & navigation
- ✅ Hero section
- ✅ Thống kê nhanh
- ✅ Danh sách loại phòng
- ✅ Form tìm phòng
- ✅ Tính năng chính
- ✅ Liên hệ
- ✅ Footer

### 7.2 Form Tìm Phòng
- ✅ Chọn ngày check-in
- ✅ Chọn ngày check-out
- ✅ Nhập số người
- ✅ Tìm phòng trống
- ✅ Redirect sang trang chi tiết nếu đã đăng nhập

## 8. Tính Năng Bảo Mật

### 8.1 SQL Injection Protection
- ✅ Sử dụng Prepared Statements (PDO)
- ✅ Parameterized queries

### 8.2 XSS Protection
- ✅ HTML escaping (htmlspecialchars)
- ✅ Content Security Policy (header)
- ✅ Validate tất cả input

### 8.3 CSRF Protection
- ✅ CSRF token generation
- ✅ Token validation
- ✅ Session-based tokens

### 8.4 Password Security
- ✅ BCRYPT hashing
- ✅ Cost factor: 12
- ✅ Không lưu password plain text

### 8.5 Session Security
- ✅ Session regeneration
- ✅ Session timeout (30 phút)
- ✅ Secure session cookie

### 8.6 Input Validation
- ✅ Validate email
- ✅ Validate phone
- ✅ Validate date
- ✅ Type casting
- ✅ Length checking

### 8.7 File Upload Security
- ✅ Whitelist file types
- ✅ File size limit
- ✅ Rename uploaded files
- ✅ Store outside web root (tùy chọn)

## 9. Giao Diện Responsive

### 9.1 Mobile-First Design
- ✅ Bootstrap 5 grid
- ✅ Responsive tables (horizontal scroll)
- ✅ Responsive navigation
- ✅ Touch-friendly buttons
- ✅ Mobile menu collapse

### 9.2 Breakpoints
- Extra small: < 576px
- Small: ≥ 576px
- Medium: ≥ 768px
- Large: ≥ 992px
- Extra large: ≥ 1200px
- XXL: ≥ 1400px

## 10. API Endpoints

### 10.1 Check Room Availability
```
POST /api/check_room_availability.php
Parameters: check_in, check_out
Response: JSON array of available rooms
```

### 10.2 Get Room Price
```
POST /api/get_room_price.php
Parameters: room_id
Response: JSON with price
```

### Các API khác (đang phát triển):
- Add service
- Remove service
- Calculate total
- Generate invoice

## 11. Tính Năng Đang Phát Triển

- [ ] Quên mật khẩu (email reset)
- [ ] Đánh giá khách hàng
- [ ] SMS notification
- [ ] Email notification
- [ ] Payment gateway integration
- [ ] Multi-language support
- [ ] Mobile app (React Native)
- [ ] Calendar view cho booking
- [ ] Bulk SMS/Email
- [ ] Customer loyalty program
- [ ] Dynamic pricing
- [ ] Room inventory management
- [ ] Staff scheduling
- [ ] Expense tracking
- [ ] Supplier management

---

**Phiên bản:** 1.0.0  
**Cập nhật lần cuối:** Tháng 12/2025
