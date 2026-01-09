# Troubleshooting Guide - BASE_URL 404 Issues

## Problem Summary
CSS/JS files returning 404 errors, and logout creating redirect loops. The BASE_URL is being calculated incorrectly in some scenarios, sometimes missing the `/TienDat123/` directory prefix.

## Quick Diagnosis Steps

### Step 1: Check DEBUG Page
**Visit this URL in your browser:**
```
http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php
```

This will show you:
- What BASE_URL is being calculated
- All SERVER variables
- Generated asset URLs
- Why the calculation succeeded or failed

### Step 2: Look for These Indicators

**✅ Good Signs:**
- BASE_URL shows: `http://localhost/TienDat123/hotel-management-system-main/`
- CSS URL shows: `http://localhost/TienDat123/hotel-management-system-main/assets/css/style.css`
- All test links work

**❌ Bad Signs:**
- BASE_URL missing `/TienDat123/` directory
- CSS URL shows: `http://localhost/hotel-management-system-main/assets/css/style.css`
- Test links show 404

## Solutions (Try in Order)

### Solution 1: Manual BASE_URL Configuration (Fastest Fix)

If automatic calculation is failing, manually set BASE_URL:

**Edit:** `config/constants.php`

**Add this at the very beginning (before line 11):**

```php
<?php
/**
 * Manual BASE_URL configuration
 * Comment this out if you want to use automatic calculation
 */

// FOR XAMPP Windows at D:\xampp\htdocs\TienDat123\hotel-management-system-main\
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
}

// ... rest of file continues
```

Then reload the page and test again.

**For Production:**
```php
// Change localhost/TienDat123 to your actual domain:
define('BASE_URL', 'https://yourdomain.com/hotel-management-system-main/');
```

### Solution 2: Fix Apache Configuration

If automatic calculation should work but isn't, check Apache config:

#### For XAMPP on Windows:
1. Open: `D:\xampp\apache\conf\httpd.conf`
2. Find the line: `<Directory "D:/xampp/htdocs">`
3. Change to:
```apache
<Directory "D:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```
4. Save and restart Apache from XAMPP Control Panel
5. Test again

#### For Linux/LAMP:
1. Edit your Apache VirtualHost config (usually in `/etc/apache2/sites-available/`)
2. Ensure the Directory block has:
```apache
<Directory /var/www/hotel-management-system-main>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```
3. Run: `sudo systemctl restart apache2`

### Solution 3: Check File Permissions

Ensure `.htaccess` exists and is readable:
```bash
ls -la /path/to/hotel-management-system-main/.htaccess
```

Should show something like:
```
-rw-r--r-- 1 user group 1234 date .htaccess
```

### Solution 4: Enable PHP Error Logging

To see if there are PHP errors preventing proper initialization:

**Edit:** `config/constants.php`

Uncomment the debug line:
```php
// Uncomment to debug BASE_URL calculation
// error_log("BASE_URL: " . BASE_URL);
// error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
```

Then check your Apache error log:
- **XAMPP Windows:** `D:\xampp\apache\logs\error.log`
- **Linux:** `/var/log/apache2/error.log`

## Understanding the BASE_URL Calculation

### How It Works:

1. **First Try (REQUEST_URI):** Parse the current request path
   - Example: `/TienDat123/hotel-management-system-main/index.php`
   - Result: `/TienDat123/hotel-management-system-main/`
   
2. **Second Try (SCRIPT_FILENAME):** Extract from file path
   - Example: `/xampp/htdocs/TienDat123/hotel-management-system-main/config/constants.php`
   - Relative: `TienDat123/hotel-management-system-main/config/constants.php`
   - Stripped: `TienDat123/hotel-management-system-main/`

3. **Fallback:** Use project directory name only
   - Result: `hotel-management-system-main/` (❌ Missing TienDat123)

### Common Causes of Wrong Calculation:

| Issue | Cause | Solution |
|-------|-------|----------|
| Missing `/TienDat123/` | Using fallback #3 | Use Solution 1 (manual config) |
| Empty BASE_URL | REQUEST_URI not detected | Enable error logging, check Apache |
| Wrong protocol (http vs https) | HTTPS not detected | Check `$_SERVER['HTTPS']` |
| Protocol missing | HTTP_HOST not set | Unlikely; check server config |

## Testing the Fix

### Test 1: CSS Loading
Visit: `http://localhost/TienDat123/hotel-management-system-main/index.php`

**Right-click → Inspect → Network tab**

Look for CSS file. Should see:
- ✅ Status: 200 OK
- ❌ Status: 404 Not Found (problem)

### Test 2: JavaScript Loading
Same page, look for `main.js`. Should be 200 OK.

### Test 3: Navigation Links
Click links in navbar - should not redirect to 404.

### Test 4: Logout
After login, click Logout. Should:
- ✅ Redirect to login page
- ❌ Show 404 redirect loop (problem)

## If Still Not Working

### Collect Debug Information

Run debug script and copy the output:
```
http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php
```

**Share:**
1. The calculated BASE_URL
2. The REQUEST_URI value
3. The SCRIPT_FILENAME value
4. Any error messages

### Check PHP Version

Make sure you're running PHP 7.4+:
```bash
php -v
```

### Check MySQL Connection

Assets might load but other errors prevent page load. Test database:
```bash
# From command line
mysql -u root -p hotel_management
SHOW TABLES;
EXIT;
```

## Prevention Tips

1. **Always test BASE_URL debug page** after deployment
2. **Use manual config** for production servers
3. **Keep .htaccess** in project root
4. **Document your BASE_URL** in deployment notes
5. **Test all navigation flows** before going live

## File Structure Reminder

```
/xampp/htdocs/
├── TienDat123/
│   └── hotel-management-system-main/     ← Project root
│       ├── index.php
│       ├── config/
│       │   └── constants.php             ← BASE_URL defined here
│       ├── modules/
│       │   └── auth/
│       │       └── login.php
│       ├── assets/
│       │   ├── css/
│       │   │   └── style.css
│       │   └── js/
│       │       └── main.js
│       └── .htaccess
```

**Expected BASE_URL:** `http://localhost/TienDat123/hotel-management-system-main/`
