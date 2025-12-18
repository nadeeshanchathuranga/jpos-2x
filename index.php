<?php
define('BASE_PATH', __DIR__);
define('LOCK_FILE', BASE_PATH . '/storage/installed.lock');

// If installed, redirect automatically
if (file_exists(LOCK_FILE)) {
    header('Location: public/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JAAN SYSTEM Installer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            margin-bottom: 30px;
            color: #333;
        }
        .btn {
            display: block;
            width: 220px;
            margin: 15px auto;
            padding: 15px;
            text-decoration: none;
            background: #007bff;
            color: #fff;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to JAAN POS 2.x</h1>
        <a href="guides/install-guide" class="btn">Installation Guide</a>
        <a href="install.php" class="btn">Run Installer</a>
        <a href="guides/user-guide/home.html" class="btn">User Guide</a>
    </div>
</body>
</html>
