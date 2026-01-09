# Hotel Management System - Apache Configuration Guide

## 1. Ensure mod_rewrite is enabled
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 2. Ensure AllowOverride is set correctly in Apache config
Make sure your VirtualHost or <Directory> block has:
```apache
<Directory /var/www/html/hotel-management-system-main>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

## 3. For XAMPP (Windows)
Edit: `D:\xampp\apache\conf\httpd.conf`
Find: `<Directory "D:/xampp/htdocs">`
Ensure: `AllowOverride All`

Restart Apache from XAMPP Control Panel.

## 4. Verify .htaccess is working
Create test file: `htaccess_test.php`
```php
<?php echo "mod_rewrite is enabled!"; ?>
```

Visit: `http://localhost/hotel-management-system-main/htaccess_test.php`
If you see the message, .htaccess works.

## 5. Debug BASE_URL
Visit: `http://localhost/hotel-management-system-main/scripts/debug_base_url.php`
Check if BASE_URL, CSS, JS URLs are correct.
