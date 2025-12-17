<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Loader Test</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-pass { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .test-fail { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .test-pending { background: #fff3cd; color: #856404; border-color: #ffeeba; }
        h1 { color: #333; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🧪 Asset Loader Test</h1>
    <p>This page tests if CSS and JavaScript files are loading correctly.</p>
    
    <div class="test-section test-pending">
        <strong>CSS File:</strong>
        If this box has green background, CSS loaded successfully ✅<br>
        If it has default background, CSS failed to load ❌
    </div>
    
    <div class="test-section">
        <strong>Expected CSS Location:</strong><br>
        <code><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></code> → 
        <code>assets/css/style.css</code>
    </div>
    
    <div class="test-section">
        <strong>Test Links:</strong><br>
        <ul>
            <li><a href="index.php">🏠 Go to Homepage</a></li>
            <li><a href="modules/auth/login.php">🚪 Go to Login</a></li>
            <li><a href="scripts/debug_base_url_enhanced.php">🔍 Check BASE_URL Debug</a></li>
        </ul>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Test if main.js loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded successfully');
            console.log('Current URL: ' + window.location.href);
            console.log('Current path: ' + window.location.pathname);
            
            // Try to access a function from main.js if it exists
            if (typeof closeAlert === 'function') {
                console.log('✅ main.js loaded successfully');
            } else {
                console.warn('⚠️ main.js may not have loaded, or closeAlert function not found');
            }
        });
    </script>
</body>
</html>
