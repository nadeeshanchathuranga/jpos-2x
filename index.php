<?php
define('BASE_PATH', __DIR__);
define('LOCK_FILE', BASE_PATH . '/storage/installed.lock');

$isInstalled = file_exists(LOCK_FILE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JPOS System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 480px;
            width: 100%;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .content {
            padding: 40px 30px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 16px;
            margin: 15px 0;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #2c3e50;
            border: 1px solid #e9ecef;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }

        .btn-installed {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-installed:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }

        .features {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .features h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .features ul {
            list-style: none;
            color: #555;
        }

        .features li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
        }

        .features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #3498db;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>JPOS System</h1>
            <p>Powerful Point of Sale Solution</p>
        </div>

        <div class="content">
            <?php if ($isInstalled): ?>
                <!-- Installed state -->
                <a href="public/" class="btn btn-installed">
                    🚀 Go to Application
                </a>
                <a href="guides/user-guide-pos-2/home.html" class="btn btn-secondary">
                    📚 User Guide
                </a>
            <?php else: ?>
                <!-- Installation state -->
                <a href="guides/install-guide" class="btn btn-primary">
                    📖 Installation Guide
                </a>
                <a href="install.php" class="btn btn-primary">
                    ⚙️ Run Installer
                </a>
            <?php endif; ?>

            <div class="features">
                <h3>Key Features</h3>
                <ul>
                    <li>Real-time Analytics Dashboard</li>
                    <li>Comprehensive Inventory Management</li>
                    <li>Secure Role-based Access Control</li>
                    <li>Detailed Financial Reporting</li>
                    <li>Multi-store Support</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            © <?php echo date('Y'); ?> JPOS System. All rights reserved.
        </div>
    </div>
</body>
</html>
