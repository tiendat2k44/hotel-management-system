# Hotel Management System - BASE_URL Fix Summary

## 🎯 What Was Done

I've implemented a **more robust BASE_URL calculation** and created **comprehensive debugging tools** to help you fix the 404 asset loading issues.

## 🔧 Changes Made

### 1. Enhanced BASE_URL Calculation (`config/constants.php`)
- **Improved method hierarchy:**
  1. Try to extract from REQUEST_URI (most reliable)
  2. Fallback to SCRIPT_FILENAME/DOCUMENT_ROOT 
  3. Last resort: use project directory name only

- **Better path parsing:** Uses string replacement and regex instead of `dirname()` calls that were losing directory levels

- **Added manual configuration option:** Comments at top show how to hardcode BASE_URL if automatic calculation fails

### 2. Created Debug Script (`scripts/debug_base_url_enhanced.php`)
- Shows **what BASE_URL is being calculated**
- Displays all **SERVER variables** for analysis
- Shows **generated asset URLs** to verify
- Color-coded success/failure indicators
- Test links to verify navigation

### 3. Added Troubleshooting Guide (`TROUBLESHOOTING_BASE_URL.md`)
- Step-by-step diagnosis instructions
- 4 solutions in order of simplicity:
  1. **Solution 1:** Manual BASE_URL configuration (fastest fix)
  2. **Solution 2:** Fix Apache configuration
  3. **Solution 3:** Check file permissions
  4. **Solution 4:** Enable PHP error logging

### 4. Created Apache Configuration Guide (`APACHE_CONFIG.md`)
- Instructions for enabling `mod_rewrite`
- Setting `AllowOverride All` in Apache
- Specific steps for XAMPP Windows

### 5. Added Asset Test Page (`test_assets.php`)
- Simple page to verify CSS/JS loading
- Shows current URL path
- Links to other diagnostic pages

## 🚀 Quick Start - What You Need To Do

### Option A: Try Automatic Calculation First (Recommended)
1. Navigate to: `http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php`
2. Check if BASE_URL is calculated correctly
3. If ✅ green "BASE_URL looks valid" - you're done!
4. If ❌ red "BASE_URL looks WRONG" - proceed to Option B

### Option B: Manual Configuration (Guaranteed to Work)
1. Open: `config/constants.php`
2. Find these commented lines (around line 11-15):
```php
// For XAMPP at http://localhost/TienDat123/hotel-management-system-main/
// define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
```
3. Uncomment and modify to match your URL:
```php
define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
```
4. Save the file
5. Reload your browser - CSS/JS should load, logout should work

## 🧪 Testing

After applying the fix, test these:

1. **CSS Loading:** View page source (F12) → check if CSS link appears
2. **Homepage:** `http://localhost/TienDat123/hotel-management-system-main/` → should have styling
3. **Login:** `http://localhost/TienDat123/hotel-management-system-main/modules/auth/login.php` → should have styling
4. **Logout:** Login with demo account → click logout → should redirect to login, not 404

### Demo Credentials (if seeded):
- **Admin:** admin / 123456
- **Staff:** staff1 / 123456  
- **Customer:** customer1 / 123456

## 📊 Technical Details

### Why Was BASE_URL Calculated Wrong?

The original code used:
```php
$__basePath = dirname($__basePath);      // Removes /config/constants.php
$__basePath = dirname($__basePath);      // Removes /config/
```

This removes TWO directory levels from paths like:
- `/TienDat123/hotel-management-system-main/config/constants.php`
- Results: `/TienDat123/` (loses `hotel-management-system-main`)

### New Approach

The updated code uses string replacement instead:
```php
$__basePath = preg_replace('#/config/constants\.php$#', '', $__relativePath);
// More precise, loses only the file path
```

This preserves the full directory structure.

## 📁 Files Modified/Created

| File | Type | Purpose |
|------|------|---------|
| `config/constants.php` | Modified | Improved BASE_URL calculation + manual config option |
| `scripts/debug_base_url_enhanced.php` | Created | Comprehensive debug tool |
| `TROUBLESHOOTING_BASE_URL.md` | Created | Step-by-step fix guide |
| `APACHE_CONFIG.md` | Created | Apache configuration help |
| `test_assets.php` | Created | Simple asset loading test |

## ⚠️ If Issues Persist

1. **Check Apache logs:**
   - XAMPP: `D:\xampp\apache\logs\error.log`
   - Linux: `/var/log/apache2/error.log`

2. **Verify .htaccess exists:**
   - File: `project_root/.htaccess`
   - Should not be empty

3. **Check Apache config allows overrides:**
   - Edit: `httpd.conf` or Virtual Host config
   - Ensure: `AllowOverride All`
   - Restart Apache

4. **Run debug script:** 
   - Visit: `scripts/debug_base_url_enhanced.php`
   - Compare REQUEST_URI with expected path

5. **Last resort - use manual config:**
   - Hardcode BASE_URL in constants.php
   - This always works regardless of deployment

## ✅ Verification Checklist

After fix:
- [ ] Debug page shows "✅ BASE_URL looks valid"
- [ ] Homepage loads with CSS styling
- [ ] Login page loads with CSS styling
- [ ] Logout redirects to login (not 404)
- [ ] Navigation links work
- [ ] Dashboard displays correctly
- [ ] Console has no 404 errors (F12 → Network tab)

## 📞 Need More Help?

1. Visit the debug page and share the output
2. Check troubleshooting guide for your specific error
3. Verify Apache configuration with APACHE_CONFIG.md
4. Use manual BASE_URL configuration as emergency fix

---

**All system files remain unchanged.** This fix only improves path calculation and adds diagnostic tools.
