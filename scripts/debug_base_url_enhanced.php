<?php
/**
 * Enhanced DEBUG script để kiểm tra BASE_URL calculation
 * Truy cập: http://localhost/TienDat123/hotel-management-system-main/scripts/debug_base_url_enhanced.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Capture BASE_URL calculation steps
$__debug_steps = [];

$__protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https://' : 'http://';
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__basePath = '';

// Log step 1: Try REQUEST_URI
$__requestUri = $_SERVER['REQUEST_URI'] ?? '';
$__debug_steps[] = [
    'method' => 'REQUEST_URI',
    'value' => $__requestUri,
    'status' => 'checking'
];

if (!empty($__requestUri)) {
    $__requestUri = parse_url($__requestUri, PHP_URL_PATH);
    
    if (strpos($__requestUri, '/config/constants.php') !== false) {
        $__basePath = str_replace('/config/constants.php', '', $__requestUri);
        $__basePath = trim($__basePath, '/');
        $__debug_steps[0]['status'] = 'found at /config/constants.php';
        $__debug_steps[0]['result'] = $__basePath;
    } elseif (strpos($__requestUri, '/index.php') !== false) {
        $__basePath = str_replace('/index.php', '', $__requestUri);
        $__basePath = trim($__basePath, '/');
        $__debug_steps[0]['status'] = 'found at /index.php';
        $__debug_steps[0]['result'] = $__basePath;
    } elseif (strpos($__requestUri, '/scripts/debug_base_url') !== false) {
        $__parts = explode('/scripts/', $__requestUri);
        $__basePath = trim($__parts[0], '/');
        $__debug_steps[0]['status'] = 'found at /scripts/';
        $__debug_steps[0]['result'] = $__basePath;
    } elseif (strpos($__requestUri, '/modules/') !== false) {
        $__parts = explode('/modules/', $__requestUri);
        $__basePath = trim($__parts[0], '/');
        $__debug_steps[0]['status'] = 'found at /modules/';
        $__debug_steps[0]['result'] = $__basePath;
    }
}

// Load constants for comparison
require_once __DIR__ . '/../config/constants.php';

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASE_URL Debug - Enhanced</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container { 
            max-width: 1100px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333; 
            border-bottom: 4px solid #667eea; 
            padding-bottom: 15px; 
            margin-bottom: 20px;
            font-size: 28px;
        }
        h2 { 
            color: #555; 
            margin-top: 30px; 
            margin-bottom: 15px;
            font-size: 18px;
            border-left: 4px solid #667eea;
            padding-left: 10px;
        }
        .section { 
            margin: 20px 0; 
            padding: 20px; 
            background: #f8f9fa; 
            border-left: 4px solid #667eea; 
            border-radius: 6px;
        }
        .var-line { 
            margin: 12px 0; 
            padding: 12px; 
            background: white; 
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            border: 1px solid #e9ecef;
        }
        .key { 
            font-weight: bold; 
            color: #667eea; 
            min-width: 200px;
        }
        .value { 
            color: #333; 
            font-family: 'Courier New', monospace; 
            flex: 1;
            word-break: break-all;
            background: #f0f2f5;
            padding: 4px 8px;
            border-radius: 3px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            margin-left: 10px;
        }
        .badge-checking { background: #cce5ff; color: #004085; }
        .badge-found { background: #d4edda; color: #155724; }
        .badge-failed { background: #f8d7da; color: #721c24; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }
        th, td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #e9ecef;
        }
        th { 
            background: #667eea; 
            color: white;
            font-weight: 600;
        }
        tr:hover { background: #f8f9fa; }
        code { 
            background: #f0f2f5; 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .test-link { 
            display: inline-block; 
            margin: 8px 8px 8px 0;
            padding: 10px 16px; 
            background: #667eea; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            font-size: 0.9em;
            transition: all 0.3s;
            border: 2px solid #667eea;
        }
        .test-link:hover { 
            background: #764ba2;
            border-color: #764ba2;
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .quick-test {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .test-box {
            background: white;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
        }
        .test-box h4 {
            color: #667eea;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        .test-box code {
            display: block;
            word-break: break-all;
            margin-top: 8px;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DEBUG: BASE_URL Calculation - Enhanced</h1>
        
        <?php
        // Check if BASE_URL looks correct
        $baseLooksCorrect = (strpos(BASE_URL, 'localhost') !== false || strpos(BASE_URL, 'http') !== false);
        $hasTienDat = strpos(BASE_URL, 'TienDat123') !== false;
        ?>
        
        <div class="alert alert-<?php echo $baseLooksCorrect ? 'success' : 'danger'; ?>">
            <?php if ($baseLooksCorrect): ?>
                ✅ <strong>BASE_URL looks valid!</strong> Calculated as: <code><?php echo htmlspecialchars(BASE_URL); ?></code>
                <?php if (!$hasTienDat): ?>
                    <br><em>Note: URL doesn't contain 'TienDat123', but may still be correct depending on deployment.</em>
                <?php endif; ?>
            <?php else: ?>
                ❌ <strong>BASE_URL looks WRONG!</strong> Check calculation steps below.
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>📊 Current Configuration</h2>
            <div class="var-line">
                <span class="key">BASE_URL:</span>
                <span class="value"><?php echo htmlspecialchars(BASE_URL); ?></span>
            </div>
            <div class="var-line">
                <span class="key">ADMIN_URL:</span>
                <span class="value"><?php echo htmlspecialchars(ADMIN_URL); ?></span>
            </div>
            <div class="var-line">
                <span class="key">CUSTOMER_URL:</span>
                <span class="value"><?php echo htmlspecialchars(CUSTOMER_URL); ?></span>
            </div>
            <div class="var-line">
                <span class="key">ROOT_PATH:</span>
                <span class="value"><?php echo htmlspecialchars(ROOT_PATH); ?></span>
            </div>
        </div>
        
        <div class="section">
            <h2>⚙️ Calculation Steps</h2>
            <table>
                <tr>
                    <th>Step</th>
                    <th>Method</th>
                    <th>Details</th>
                    <th>Result</th>
                </tr>
                <?php foreach ($__debug_steps as $i => $step): ?>
                <tr>
                    <td><strong><?php echo $i+1; ?></strong></td>
                    <td><?php echo htmlspecialchars($step['method']); ?></td>
                    <td>
                        <?php if (!empty($step['value'])): ?>
                            <code><?php echo htmlspecialchars($step['value']); ?></code>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge badge-<?php echo htmlspecialchars($step['status']); ?>">
                            <?php echo htmlspecialchars($step['status']); ?>
                        </span>
                        <?php if (!empty($step['result'])): ?>
                            <div style="margin-top: 8px; color: #28a745; font-weight: bold;">
                                → <code><?php echo htmlspecialchars($step['result']); ?></code>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="section">
            <h2>🌐 Server Variables</h2>
            <table>
                <tr>
                    <th>Variable</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td><code>REQUEST_URI</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <td><code>SCRIPT_NAME</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <td><code>SCRIPT_FILENAME</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <td><code>DOCUMENT_ROOT</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <td><code>HTTP_HOST</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <td><code>HTTPS</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['HTTPS'] ?? 'N/A'); ?></code></td>
                </tr>
                <tr>
                    <td><code>PHP_SELF</code></td>
                    <td><code><?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? 'N/A'); ?></code></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>🔗 Generated URLs (Quick Test)</h2>
            <div class="quick-test">
                <div class="test-box">
                    <h4>🎨 CSS File</h4>
                    <code><?php echo htmlspecialchars(BASE_URL . 'assets/css/style.css'); ?></code>
                </div>
                <div class="test-box">
                    <h4>📜 JavaScript</h4>
                    <code><?php echo htmlspecialchars(BASE_URL . 'assets/js/main.js'); ?></code>
                </div>
                <div class="test-box">
                    <h4>🏨 Admin Dashboard</h4>
                    <code><?php echo htmlspecialchars(ADMIN_URL . 'dashboard.php'); ?></code>
                </div>
                <div class="test-box">
                    <h4>👤 Customer Dashboard</h4>
                    <code><?php echo htmlspecialchars(CUSTOMER_URL . 'dashboard.php'); ?></code>
                </div>
                <div class="test-box">
                    <h4>🚪 Login Page</h4>
                    <code><?php echo htmlspecialchars(BASE_URL . 'modules/auth/login.php'); ?></code>
                </div>
                <div class="test-box">
                    <h4>🔐 Logout</h4>
                    <code><?php echo htmlspecialchars(BASE_URL . 'modules/auth/logout.php'); ?></code>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>✅ Test Navigation Links</h2>
            <p style="margin-bottom: 10px;">Click these to verify BASE_URL is working correctly:</p>
            <div>
                <a href="<?php echo htmlspecialchars(BASE_URL); ?>" class="test-link">🏠 Homepage</a>
                <a href="<?php echo htmlspecialchars(BASE_URL . 'modules/auth/login.php'); ?>" class="test-link">🚪 Login</a>
                <a href="<?php echo htmlspecialchars(BASE_URL . 'modules/admin/dashboard.php'); ?>" class="test-link">📊 Admin</a>
                <a href="<?php echo htmlspecialchars(BASE_URL . 'modules/customer/dashboard.php'); ?>" class="test-link">👤 Customer</a>
            </div>
        </div>
        
        <div class="section" style="background: #fff3cd; border-left-color: #ffc107;">
            <h2>💡 How to Fix BASE_URL if Wrong</h2>
            <p>If BASE_URL is not correct, manually set it in <code>config/constants.php</code>:</p>
            <pre style="background: white; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 0.9em;"><code>// ADD THIS LINE in config/constants.php BEFORE the fallback calculation:
if (!defined('BASE_URL')) {
    // For XAMPP at D:\xampp\htdocs\TienDat123\hotel-management-system-main\
    define('BASE_URL', 'http://localhost/TienDat123/hotel-management-system-main/');
}</code></pre>
            <p style="margin-top: 10px;">Or ensure your Apache has <code>AllowOverride All</code> configured in the VirtualHost/Directory block.</p>
        </div>
    </div>
</body>
</html>
