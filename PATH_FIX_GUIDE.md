# 🔧 Path Fix Guide - v1.1.1

## ❌ Lỗi Báo Cáo

Người dùng báo cáo 3 vấn đề nghiêm trọng:

### **Lỗi 1: Include Path Sai trong Quản Lý Khách Hàng**
```
Warning: require_once(../../config/constants.php): Failed to open stream
modules/admin/customers/index.php line 6
```

**Nguyên nhân:** File trong `modules/admin/customers/` dùng `../../` nhưng cần `../../../`

**Cấu trúc:**
```
hotel-management-system-main/
├── config/constants.php
└── modules/admin/customers/index.php
    ↑
    Cần: ../../../config/constants.php (3 level)
    Sai: ../../config/constants.php (2 level)
```

### **Lỗi 2: Include Path Sai trong Báo Cáo**
```
Warning: require_once(../../config/constants.php): Failed to open stream
modules/admin/reports/index.php line 7
```

**Cách Fix:** Thay `../../` → `../../../`

### **Lỗi 3: URL Bị Lặp**
```
http://localhost/TienDat123/hotel-management-system-main/modules/admin/services/modules/admin/dashboard.php
                                                                                  ↑ LẠP!
```

**Nguyên nhân:** Breadcrumb dùng relative path `../dashboard.php` nhưng file ở trong thư mục con

**Cách Fix:** Dùng URL tuyệt đối với `ADMIN_URL` constant

---

## ✅ Giải Pháp Đã Áp Dụng

### **Fix 1: Correct Include Paths**

**Files đã sửa:**
- `modules/admin/customers/index.php` - Line 6-9
- `modules/admin/customers/view.php` - Line 6-9
- `modules/admin/reports/index.php` - Line 7-10

**Thay đổi:**
```php
// TRƯỚC (Sai)
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth_check.php';

// SAU (Đúng)
require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../includes/functions.php';
require_once '../../../includes/auth_check.php';
```

### **Fix 2: Use Absolute URLs for Breadcrumbs**

**Files đã sửa:**
- `modules/admin/customers/index.php` - Line 58
- `modules/admin/customers/view.php` - Line 58
- `modules/admin/reports/index.php` - Line 27

**Thay đổi:**
```php
// TRƯỚC (Relative - BỊ LẠP)
<a href="../dashboard.php">Dashboard</a>
// Nếu file ở /modules/admin/services/xxx/view.php
// → /modules/admin/services/../dashboard.php
// → /modules/admin/services/dashboard.php (SAI!)

// SAU (Absolute - ĐÚNG)
<a href="<?php echo ADMIN_URL; ?>dashboard.php">Dashboard</a>
// → /TienDat123/hotel-management-system-main/modules/admin/dashboard.php (OK!)
```

---

## 📋 Checklist Path Levels

### Quy Tắc Chung:

**Level 1 - Direct under modules/admin/:**
- `modules/admin/dashboard.php`
- `modules/admin/index.php`
- Dùng: `../../config/constants.php` ✅

**Level 2 - Inside modules/admin/*/:**
- `modules/admin/rooms/index.php`
- `modules/admin/bookings/index.php`
- `modules/admin/services/index.php`
- Dùng: `../../../config/constants.php` ✅

**Level 3 - Inside modules/admin/*/*/:**
- `modules/admin/customers/index.php` ⚠️ Sai: `../../`, Đúng: `../../../`
- `modules/admin/reports/index.php` ⚠️ Sai: `../../`, Đúng: `../../../`
- Dùng: `../../../config/constants.php` ✅

### Quy Tắc URL:

**Relative URLs (Tránh!):**
```php
<a href="../dashboard.php">       // Dễ bị lặp nếu file ở thư mục con
<a href="index.php">              // OK nếu cùng thư mục
```

**Absolute URLs (Nên!):**
```php
<a href="<?php echo ADMIN_URL; ?>dashboard.php">     // Luôn đúng
<a href="<?php echo BASE_URL; ?>">                   // Luôn đúng
<a href="<?php echo CUSTOMER_URL; ?>">               // Luôn đúng
```

---

## 🧪 Test Kết Quả

### Test 1: Quản Lý Khách Hàng
```
✅ Admin Dashboard → Quản Lý Khách Hàng
✅ Trang load không lỗi
✅ Click "Dashboard" breadcrumb → Back to dashboard (không bị lặp)
```

### Test 2: Báo Cáo
```
✅ Admin Dashboard → Báo Cáo
✅ Trang load không lỗi
✅ Click "Dashboard" breadcrumb → Back to dashboard (không bị lặp)
```

### Test 3: URL
```
✅ http://localhost/TienDat123/hotel-management-system-main/modules/admin/dashboard.php ✓
❌ http://localhost/TienDat123/hotel-management-system-main/modules/admin/services/modules/admin/dashboard.php ✗
```

---

## 📊 Files Thay Đổi

| File | Thay Đổi |
|------|---------|
| `modules/admin/customers/index.php` | ✏️ Include path (2 → 3 levels) + Breadcrumb URL |
| `modules/admin/customers/view.php` | ✏️ Include path (2 → 3 levels) + Breadcrumb URL |
| `modules/admin/reports/index.php` | ✏️ Include path (2 → 3 levels) + Breadcrumb URL |

---

## 💡 Best Practices for Future

### ✅ DO:
```php
// 1. Use absolute paths for includes (dùng ROOT_PATH constant)
require_once ROOT_PATH . 'config/constants.php';

// 2. Use constants for URLs
<a href="<?php echo ADMIN_URL; ?>dashboard.php">
<a href="<?php echo BASE_URL; ?>index.php">

// 3. Organize files properly
modules/
├── admin/
│   ├── dashboard.php (level 1 - use ../../)
│   ├── rooms/
│   │   ├── index.php (level 2 - use ../../../)
│   │   └── ...
│   ├── customers/
│   │   ├── index.php (level 2 - use ../../../)
│   │   ├── view.php  (level 2 - use ../../../)
│   │   └── ...
│   └── ...
```

### ❌ DON'T:
```php
// 1. Don't use relative paths for includes
require_once '../../config/constants.php';  // Can break!

// 2. Don't use relative URLs in navbar/breadcrumb
<a href="../dashboard.php">                // Can create loops!

// 3. Don't mix relative and absolute paths
require_once '../config/constants.php';   // Too complex!
```

---

## 📝 Git Commit

```
commit 1158c26
Author: Tien Dat <tiendat2k44@gmail.com>
Date:   [timestamp]

fix(paths): Correct include paths for admin subdirectories (customers, reports) & fix relative breadcrumb links

- Fix require_once paths: ../../ → ../../../ for nested directories
- Use absolute URLs with ADMIN_URL constant instead of relative paths
- Prevent URL duplication issues
```

---

## ✨ Summary

| Vấn Đề | Nguyên Nhân | Giải Pháp |
|--------|-----------|---------|
| Include lỗi | Path sai (2 vs 3 levels) | Thêm 1 level: `../../` → `../../../` |
| URL bị lặp | Relative paths trong sub-dirs | Dùng ADMIN_URL constant |
| Navigation lỗi | Không có breadcrumb tuyệt đối | Sử dụng `<?php echo ADMIN_URL; ?>` |

---

**Hệ thống đã sẵn sàng!** 🚀

Pull code mới:
```bash
git pull origin main
```

Kiểm tra xem tất cả paths đã hoạt động đúng chưa!
