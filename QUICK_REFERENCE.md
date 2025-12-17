# 🚀 Quick Reference - BASE_URL Fix

## The Problem
❌ CSS/JS returning 404 errors  
❌ Logout creates redirect loop  
❌ BASE_URL missing `/TienDat123/` directory prefix

## The Solution (3 Steps)

### Step 1: Check if it's Fixed
Visit: `http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php`

**If you see:** ✅ "BASE_URL looks valid!" → **You're done!**

### Step 2: If Not Fixed - Apply Manual Fix
1. Open file: `config/constants.php`
2. Find lines 15-16 (look for comments about XAMPP)
3. Change from:
```php
// define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
```
To:
```php
define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
```
(Just remove the `//` at the beginning)

4. Save the file
5. Reload browser

**Result:** ✅ CSS loads, logout works, all links function

### Step 3: Verify It Works
- Homepage: `http://localhost/TienDat123/hotel-management-system-main/`
  - Should have styling ✅
  - Should show hotel name, not blank
  
- Login: Click on a login link
  - Should redirect to login page
  - Should have styling ✅
  
- Test Login:
  - Username: `admin`
  - Password: `123456`
  - Click login
  
- Test Logout:
  - After login, click logout
  - Should redirect to login page ✅
  - Should NOT show 404 ❌

## If Manual Fix Didn't Work

1. **Restart Apache:**
   - XAMPP Control Panel → Apache → Restart

2. **Clear Browser Cache:**
   - Ctrl+Shift+Delete → Clear all → Reload

3. **Check File Saved:**
   - Open `config/constants.php` again
   - Verify your change is still there
   - (Not cleared by accident)

4. **Check Apache Logs:**
   - XAMPP: `D:\xampp\apache\logs\error.log`
   - See if there are PHP errors

5. **Last Resort - Hard Reset:**
   - Delete project folder
   - Re-extract from backup
   - Apply fix again

## Debug Tools Available

| Tool | URL | Purpose |
|------|-----|---------|
| **Enhanced Debug** | `/scripts/debug_base_url_enhanced.php` | Full diagnostic with step-by-step calculation |
| **Asset Test** | `/test_assets.php` | Simple test page for CSS/JS loading |
| **Troubleshooting** | `/TROUBLESHOOTING_BASE_URL.md` | Detailed guide for different scenarios |

## Directory Structure
```
http://localhost/
├── TienDat123/
│   └── hotel-management-system-main/  ← Project root
│       ├── index.php
│       ├── config/
│       │   └── constants.php
│       ├── modules/auth/login.php
│       ├── assets/
│       │   ├── css/style.css
│       │   └── js/main.js
│       └── scripts/
│           └── debug_base_url_enhanced.php
```

## Expected URLs After Fix

| Page | URL |
|------|-----|
| Homepage | `http://localhost/TienDat123/hotel-management-system-main/` |
| Login | `http://localhost/TienDat123/hotel-management-system-main/modules/auth/login.php` |
| Admin Dashboard | `http://localhost/TienDat123/hotel-management-system-main/modules/admin/dashboard.php` |
| Customer Dashboard | `http://localhost/TienDat123/hotel-management-system-main/modules/customer/dashboard.php` |
| CSS | `http://localhost/TienDat123/hotel-management-system-main/assets/css/style.css` |
| JavaScript | `http://localhost/TienDat123/hotel-management-system-main/assets/js/main.js` |

## Demo Login Credentials
```
Admin:
  Username: admin
  Password: 123456

Staff:
  Username: staff1
  Password: 123456

Customer:
  Username: customer1
  Password: 123456
```

## Files Changed
✏️ `config/constants.php` - Added manual configuration option, improved calculation

## Files Created
📄 `scripts/debug_base_url_enhanced.php` - Enhanced debug tool  
📄 `TROUBLESHOOTING_BASE_URL.md` - Detailed troubleshooting guide  
📄 `APACHE_CONFIG.md` - Apache configuration help  
📄 `test_assets.php` - Asset loading test page  
📄 `BASE_URL_FIX_SUMMARY.md` - Comprehensive summary  
📄 `QUICK_REFERENCE.md` - This file  

## Still Not Working?

Check in this order:

1. ✅ Have you restarted Apache? (XAMPP Control Panel)
2. ✅ Have you cleared browser cache? (Ctrl+Shift+Delete)
3. ✅ Did you check the debug page? (scripts/debug_base_url_enhanced.php)
4. ✅ Did you uncomment the BASE_URL line correctly? (No spaces, no // at start)
5. ✅ Did you save the file after editing?
6. ✅ Are you visiting the correct URL? (With TienDat123 in path)

**If all above checked:** Check Apache error log or read TROUBLESHOOTING_BASE_URL.md

---
**Last Updated:** After BASE_URL fix implementation  
**Status:** Ready to test
