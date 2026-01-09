# 🔧 BASE_URL 404 Error - Fix Package

## 📋 What This Package Contains

This package contains tools and documentation to fix the **BASE_URL path calculation issue** that's causing 404 errors on CSS/JS files and logout redirect loops.

## ⚡ Start Here (Choose One)

### For Busy People (5 minutes)
1. Read: [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
2. Follow the 3-step solution
3. Done!

### For Thorough People (20 minutes)
1. Read: [BASE_URL_FIX_SUMMARY.md](BASE_URL_FIX_SUMMARY.md)
2. Understand what changed
3. Apply the appropriate solution
4. Test using provided tools

### For Troubleshooting (As needed)
1. Visit: `scripts/debug_base_url_enhanced.php`
2. Read: [TROUBLESHOOTING_BASE_URL.md](TROUBLESHOOTING_BASE_URL.md)
3. Follow your specific error pattern
4. Apply the matching solution

## 🛠️ Available Tools

### 1. Enhanced Debug Script
**Location:** `scripts/debug_base_url_enhanced.php`  
**Access:** `http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php`

**Shows:**
- Current BASE_URL calculation
- Step-by-step calculation process
- All SERVER variables
- Generated asset URLs
- Color-coded success/failure indicators
- Test navigation links

### 2. Asset Test Page
**Location:** `test_assets.php`  
**Access:** `http://localhost/TienDat123/hotel-management-system-main/test_assets.php`

**Purpose:**
- Simple test for CSS loading
- Verifies browser console JavaScript
- Quick link navigation

### 3. Documentation

| Document | Purpose | Audience |
|----------|---------|----------|
| [QUICK_REFERENCE.md](QUICK_REFERENCE.md) | 3-step quick fix | Everyone |
| [BASE_URL_FIX_SUMMARY.md](BASE_URL_FIX_SUMMARY.md) | What changed & why | Developers |
| [TROUBLESHOOTING_BASE_URL.md](TROUBLESHOOTING_BASE_URL.md) | Detailed solutions | Troubleshooters |
| [APACHE_CONFIG.md](APACHE_CONFIG.md) | Server configuration | System admins |

## 🚀 The Fix (Summary)

### Problem
BASE_URL calculation loses `/TienDat123/` directory prefix in certain scenarios.

### Root Cause
Original code used `dirname(dirname())` which removes too many directory levels.

### Solution
Updated calculation uses precise string replacement and REQUEST_URI parsing instead.

### Files Modified
- `config/constants.php` - Improved BASE_URL calculation + manual configuration option

### Files Created
- `scripts/debug_base_url_enhanced.php` - Enhanced diagnostic tool
- `test_assets.php` - Simple asset loading test
- `TROUBLESHOOTING_BASE_URL.md` - Fix guide
- `APACHE_CONFIG.md` - Server config guide
- `BASE_URL_FIX_SUMMARY.md` - Comprehensive summary
- `QUICK_REFERENCE.md` - Quick fix steps
- `FIX_PACKAGE_README.md` - This file

## 💡 The Two-Pronged Approach

This package gives you TWO ways to fix the issue:

### Approach 1: Automatic (If Working)
- Improved calculation in `config/constants.php`
- More reliable REQUEST_URI parsing
- Better fallback handling
- **Try this first** - faster if it works

### Approach 2: Manual (Guaranteed)
- Uncomment the BASE_URL line in `config/constants.php`
- Hardcode your actual URL
- Always works regardless of deployment
- **Use this if Approach 1 doesn't work**

## ✅ Quick Diagnosis

**Follow these steps to determine which fix to use:**

1. **Visit debug page:**
   ```
   http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php
   ```

2. **Look for this message:**
   ```
   ✅ BASE_URL looks valid!
   ```

   - **If you see it:** Automatic fix worked! Continue to testing.
   - **If you don't see it:** Proceed to manual fix below.

3. **Manual fix (if needed):**
   - Open: `config/constants.php`
   - Uncomment line 15: `define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');`
   - Save file
   - Reload browser

4. **Test:**
   - Visit homepage - should have styling
   - Test login/logout - should work
   - Check F12 console - no 404 errors

## 📊 File Structure

```
/hotel-management-system-main/
├── config/
│   └── constants.php                    ← MODIFIED: Better BASE_URL calc
│
├── scripts/
│   ├── debug_base_url.php              ← Existing debug script
│   └── debug_base_url_enhanced.php      ← NEW: Enhanced debug tool
│
├── test_assets.php                      ← NEW: Asset loading test
│
├── FIX_PACKAGE_README.md               ← NEW: This file
├── QUICK_REFERENCE.md                  ← NEW: 3-step quick fix
├── BASE_URL_FIX_SUMMARY.md            ← NEW: Comprehensive summary
├── TROUBLESHOOTING_BASE_URL.md        ← NEW: Detailed fix guide
└── APACHE_CONFIG.md                    ← NEW: Server config guide
```

## 🎯 Success Criteria

After applying the fix, you should see:

- ✅ CSS/JS files load (status 200 in F12 Network tab)
- ✅ Homepage displays with styling
- ✅ Login page displays with styling
- ✅ Navigation links work without 404
- ✅ Logout redirects to login (not 404)
- ✅ Dashboard displays correctly
- ✅ Debug script shows "BASE_URL looks valid"

## ⚠️ Troubleshooting Checklist

If the fix didn't work, check:

- [ ] Did you restart Apache? (XAMPP Control Panel)
- [ ] Did you clear browser cache? (Ctrl+Shift+Delete)
- [ ] Did you save config/constants.php after editing?
- [ ] Did you uncomment the BASE_URL line correctly?
- [ ] Are you visiting the correct URL with `/TienDat123/` in path?
- [ ] Did you check the debug page for error details?
- [ ] Did you check Apache error logs?

If all above checked → Read [TROUBLESHOOTING_BASE_URL.md](TROUBLESHOOTING_BASE_URL.md)

## 🔍 Advanced Options

### Enable PHP Error Logging
In `config/constants.php`, uncomment:
```php
error_log("BASE_URL: " . BASE_URL);
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
```

Then check:
- **XAMPP:** `D:\xampp\apache\logs\error.log`
- **Linux:** `/var/log/apache2/error.log`

### Configure Apache Manually
Edit `httpd.conf` or VirtualHost config:
```apache
<Directory /path/to/project>
    AllowOverride All
    Require all granted
</Directory>
```

Then restart Apache.

## 📞 Getting Help

1. **For quick fix:** Use [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
2. **For debugging:** Visit `scripts/debug_base_url_enhanced.php`
3. **For detailed help:** Read [TROUBLESHOOTING_BASE_URL.md](TROUBLESHOOTING_BASE_URL.md)
4. **For server issues:** Check [APACHE_CONFIG.md](APACHE_CONFIG.md)

## ✨ Key Improvements

This fix package includes:

✅ **Better calculation logic** - More reliable path extraction  
✅ **Manual configuration option** - Guaranteed fallback  
✅ **Enhanced debugging tools** - Detailed diagnostic information  
✅ **Comprehensive documentation** - Step-by-step guides  
✅ **Multiple solution paths** - Choose what works for you  
✅ **Troubleshooting resources** - Handle any scenario  

## 🎓 How It Works

### Original Problem
```
REQUEST_URI: /TienDat123/hotel-management-system-main/config/constants.php
dirname() call 1: /TienDat123/hotel-management-system-main/config
dirname() call 2: /TienDat123/hotel-management-system-main
dirname() call 3: /TienDat123  ❌ WRONG - removed project name!
```

### New Solution
```
REQUEST_URI: /TienDat123/hotel-management-system-main/config/constants.php
str_replace() removes: /config/constants.php
Result: /TienDat123/hotel-management-system-main/ ✅ CORRECT!
```

---

**Ready to fix?** Start with [QUICK_REFERENCE.md](QUICK_REFERENCE.md) or the debug tool!
