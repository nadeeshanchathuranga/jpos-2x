<?php
set_time_limit(0);
ini_set('memory_limit', '512M');

$ROOT = __DIR__;
$ENV_FILE = "$ROOT/.env";
$LOCK_FILE = "$ROOT/storage/install.lock";
$IS_WIN = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if (file_exists($LOCK_FILE)) {
    die("<h2 style='color:#e74c3c;text-align:center;margin-top:100px;'>❌ Application already installed.</h2>");
}

/* ---------------- UTILITIES ---------------- */
function js($s)
{
    echo "<script>$s</script>";
    @ob_flush();
    @flush();
}
function logMsg($m)
{
    js("log(" . json_encode($m) . ")");
}
function progress($p)
{
    js("setProgress($p)");
}

function execLogged($cmd, $cwd = null)
{
    if ($cwd) {
        $cmd = "cd " . escapeshellarg($cwd) . " && $cmd";
    }
    exec($cmd . " 2>&1", $out, $code);
    foreach ($out as $line) {
        //logMsg($line);
    }
    return $code === 0;
}

function fixPermissions($root, $win)
{
    $paths = ["$root/storage", "$root/bootstrap/cache"];
    foreach ($paths as $p) {
        if (!is_dir($p)) return false;
        if ($win) {
            if (!is_writable($p)) return false;
        } else {
            exec("chown -R www-data:www-data " . escapeshellarg($p));
            exec("chmod -R 775 " . escapeshellarg($p));
        }
    }
    return true;
}

function fixEnvPermissions($root, $win)
{
    if ($win) {
        if (!is_writable("$root/storage")) return false;
    } else {
        exec("chown -R www-data:www-data " . escapeshellarg("$root/storage"));
        exec("chmod -R 775 " . escapeshellarg("$root/storage"));
    }
    return true;
}

function testDb($h, $d, $u, $p)
{
    try {
        new PDO(
            "mysql:host=$h;dbname=$d;charset=utf8mb4",
            $u,
            $p,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return true;
    } catch (Throwable $e) {
        logMsg("❌ DB Error: " . $e->getMessage());
        return false;
    }
}

function getCurrentURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $url_without_install = str_replace('install.php', '', $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    $url_without_install = rtrim($url_without_install, '/');
    return $url_without_install;
}

/* ---------------- SYSTEM CHECK ---------------- */
function systemCheck(&$nodeAvailable)
{
    $r = [];
    $r['PHP'] = [
        'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
        'cur' => PHP_VERSION,
        'req' => '>= 8.2.0'
    ];

    foreach (['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'gd', 'zip'] as $e) {
        $r[$e] = [
            'ok' => extension_loaded($e),
            'cur' => extension_loaded($e) ? 'Enabled' : 'Missing',
            'req' => 'Enabled'
        ];
    }

    $nodeAvailable = false;
    $nodeCmds = [
        'node --version',
        '"C:\\Program Files\\nodejs\\node.exe" --version',
        '"C:\\Program Files (x86)\\nodejs\\node.exe" --version'
    ];
    foreach ($nodeCmds as $cmd) {
        exec($cmd, $no, $nc);
        if ($nc === 0) {
            $nodeAvailable = true;
            break;
        }
    }

    $r['Node.js'] = [
        'ok' => $nodeAvailable,
        'cur' => $nodeAvailable ? 'Detected' : 'Not detected',
        'req' => 'Optional'
    ];

    return $r;
}

/* ---------------- WIZARD ---------------- */
$STEP = $_POST['step'] ?? 1;
$nodeAvailable = false;
$sys = systemCheck($nodeAvailable);
$block = false;
foreach ($sys as $v) {
    if (!$v['ok'] && $v['req'] !== 'Optional') $block = true;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>JPOS System - Installation Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .installer-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .installer-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .installer-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .installer-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .installer-content {
            padding: 40px;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            position: relative;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 20px;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .step-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .step.active .step-label {
            color: #3498db;
        }

        .panel {
            display: none;
            animation: fadeIn 0.5s;
        }

        .panel.active {
            display: block;
        }

        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .requirements-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }

        .requirements-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .requirements-table tr:last-child td {
            border-bottom: none;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .input-group input[type="text"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .input-group input[type="text"]:focus,
        .input-group input[type="password"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .secondary-db {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #3498db;
            display: none;
        }

        .secondary-db.active {
            display: block;
            animation: slideDown 0.3s;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            min-width: 150px;
            margin-top: 20px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: #6c757d;
            margin-left: 10px;
        }

        .progress-container {
            margin: 30px 0;
        }

        .progress-bar {
            height: 12px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .progress-text {
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            font-weight: 600;
        }

        .log-container {
            background: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            height: 300px;
            overflow-y: auto;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            line-height: 1.5;
        }

        .log-line {
            color: #e0e0e0;
            margin-bottom: 4px;
            word-break: break-all;
        }

        .log-line.success {
            color: #2ecc71;
        }

        .log-line.error {
            color: #e74c3c;
        }

        .log-line.info {
            color: #3498db;
        }

        .log-line.warning {
            color: #f39c12;
        }

        .error-message {
            background: #fde8e8;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #c53030;
        }

        .success-message {
            background: #d4edda;
            border-left: 4px solid #2ecc71;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #155724;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }

            to {
                opacity: 1;
                max-height: 500px;
            }
        }

        .hidden {
            display: none;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
    </style>
    <script>
        function showPanel(panelId) {
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            document.getElementById(panelId).classList.add('active');
        }

        function log(message) {
            const logContainer = document.getElementById('log');
            const line = document.createElement('div');
            line.className = 'log-line';

            if (message.includes('✅') || message.includes('▶')) {
                line.classList.add('info');
            } else if (message.includes('❌')) {
                line.classList.add('error');
            } else if (message.includes('✓')) {
                line.classList.add('success');
            } else if (message.includes('⚠')) {
                line.classList.add('warning');
            }

            line.textContent = message;
            logContainer.appendChild(line);
            logContainer.scrollTop = logContainer.scrollHeight;
        }

        function setProgress(percent) {
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');

            if (progressFill) {
                progressFill.style.width = percent + '%';
            }
            if (progressText) {
                progressText.textContent = percent + '% Complete';
            }
        }

        function toggleSecondaryDB() {
            const secondaryDB = document.getElementById('secondaryDB');
            const checkbox = document.querySelector('input[name="use_db2"]');

            if (checkbox.checked) {
                secondaryDB.classList.add('active');
            } else {
                secondaryDB.classList.remove('active');
            }
        }

        function validateForm() {
            const host = document.querySelector('input[name="db_host"]').value;
            const database = document.querySelector('input[name="db_database"]').value;
            const username = document.querySelector('input[name="db_username"]').value;

            if (!host || !database || !username) {
                alert('Please fill in all required database fields');
                return false;
            }

            const useSecondary = document.querySelector('input[name="use_db2"]').checked;
            if (useSecondary) {
                const host2 = document.querySelector('input[name="db2_host"]').value;
                const database2 = document.querySelector('input[name="db2_database"]').value;
                const username2 = document.querySelector('input[name="db2_username"]').value;

                if (!host2 || !database2 || !username2) {
                    alert('Please fill in all secondary database fields when enabled');
                    return false;
                }
            }

            return true;
        }
    </script>
</head>

<body>
    <div class="installer-container">
        <div class="installer-header">
            <h1>JPOS System Installation</h1>
            <p>Complete the installation wizard to set up your point of sale system</p>
        </div>

        <div class="step-indicator">
            <div class="step <?= $STEP == 1 ? 'active' : '' ?>">
                <div class="step-number">1</div>
                <div class="step-label">System Check</div>
            </div>
            <div class="step <?= $STEP == 2 ? 'active' : '' ?>">
                <div class="step-number">2</div>
                <div class="step-label">Database Setup</div>
            </div>
            <div class="step <?= $STEP == 3 ? 'active' : '' ?>">
                <div class="step-number">3</div>
                <div class="step-label">Installation</div>
            </div>
        </div>

        <div class="installer-content">
            <?php if ($STEP == 1): ?>
                <div class="panel active" id="systemCheck">
                    <h2>System Requirements Check</h2>
                    <p>Verify that your server meets all requirements before proceeding.</p>

                    <table class="requirements-table">
                        <thead>
                            <tr>
                                <th>Requirement</th>
                                <th>Status</th>
                                <th>Current</th>
                                <th>Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sys as $k => $v): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($k) ?></strong></td>
                                    <td>
                                        <?php if ($v['ok']): ?>
                                            <span style="color:#2ecc71;">✅ Pass</span>
                                        <?php else: ?>
                                            <span style="color:<?= $v['req'] == 'Optional' ? '#f39c12' : '#e74c3c' ?>">
                                                <?= $v['req'] == 'Optional' ? '⚠ Optional' : '❌ Fail' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($v['cur']) ?></td>
                                    <td><?= htmlspecialchars($v['req']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($block): ?>
                        <div class="error-message">
                            <strong>❌ Requirements Not Met</strong>
                            <p>Please fix the failed requirements before proceeding with the installation.</p>
                        </div>
                        <button class="btn btn-secondary" onclick="location.reload()">Re-check System</button>
                    <?php else: ?>
                        <div class="success-message">
                            <strong>✅ All Requirements Met</strong>
                            <p>Your system meets all the necessary requirements. You can proceed to the next step.</p>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="step" value="2">
                            <button type="submit" class="btn">Continue to Database Setup →</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($STEP == 2): ?>
                <div class="panel active" id="databaseSetup">
                    <h2>Database Configuration</h2>
                    <p>Configure your primary and optional secondary database connections.</p>

                    <form method="POST" onsubmit="return validateForm()">
                        <input type="hidden" name="step" value="3">

                        <div class="input-group">
                            <label for="db_host">Primary Database Host *</label>
                            <input type="text" id="db_host" name="db_host" required placeholder="localhost">
                        </div>

                        <div class="input-group">
                            <label for="db_database">Primary Database Name *</label>
                            <input type="text" id="db_database" name="db_database" required placeholder="jpos_primary">
                        </div>

                        <div class="input-group">
                            <label for="db_username">Primary Database Username *</label>
                            <input type="text" id="db_username" name="db_username" required placeholder="root">
                        </div>

                        <div class="input-group">
                            <label for="db_password">Primary Database Password</label>
                            <input type="password" id="db_password" name="db_password" placeholder="(optional)">
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="use_db2" name="use_db2" onchange="toggleSecondaryDB()">
                            <label for="use_db2">Enable Secondary Database (for reports/analytics)</label>
                        </div>

                        <div class="secondary-db" id="secondaryDB">
                            <h3>Secondary Database (Optional)</h3>
                            <div class="input-group">
                                <label for="db2_host">Secondary Database Host</label>
                                <input type="text" id="db2_host" name="db2_host" placeholder="localhost">
                            </div>

                            <div class="input-group">
                                <label for="db2_database">Secondary Database Name</label>
                                <input type="text" id="db2_database" name="db2_database" placeholder="jpos_secondary">
                            </div>

                            <div class="input-group">
                                <label for="db2_username">Secondary Database Username</label>
                                <input type="text" id="db2_username" name="db2_username" placeholder="root">
                            </div>

                            <div class="input-group">
                                <label for="db2_password">Secondary Database Password</label>
                                <input type="password" id="db2_password" name="db2_password" placeholder="(optional)">
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                            <button type="submit" class="btn">Start Installation →</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($STEP == 3):
                if (!testDb($_POST['db_host'], $_POST['db_database'], $_POST['db_username'], $_POST['db_password'])) {
                    echo "<script>alert('❌ Primary database connection failed! Please check your credentials.');window.history.back();</script>";
                    exit;
                }

                if (isset($_POST['use_db2'])) {
                    if (!testDb($_POST['db2_host'], $_POST['db2_database'], $_POST['db2_username'], $_POST['db2_password'])) {
                        echo "<script>alert('❌ Secondary database connection failed! Please check your credentials.');window.history.back();</script>";
                        exit;
                    }
                }

                $db_host = $_POST['db_host'];
                $db_database = $_POST['db_database'];
                $db_username = $_POST['db_username'];
                $db_password = $_POST['db_password'];

                $db2_host = $_POST['db2_host'] ?? '';
                $db2_database = $_POST['db2_database'] ?? '';
                $db2_username = $_POST['db2_username'] ?? '';
                $db2_password = $_POST['db2_password'] ?? '';

                $appUrl = getCurrentURL();
            ?>
                <div class="panel active" id="installationProgress">
                    <h2>Installation in Progress</h2>
                    <p>Please wait while we set up your JPOS System. This may take a few minutes.</p>

                    <div class="progress-container">
                        <div class="progress-text" id="progressText">0% Complete</div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                    </div>

                    <div class="log-container" id="log"></div>

                    <div class="success-message hidden" id="completionMessage">
                        <strong>✅ Installation Complete!</strong>
                        <p>Your JPOS System has been successfully installed. You will be redirected to the login page shortly.</p>
                    </div>
                </div>


                <?php
                // Start installation process
                logMsg("▶ Starting JPOS System Installation...");
                progress(10);

                logMsg("▶ Generating environment configuration...");
                progress(30);

                $env = <<<ENV
APP_NAME="JPOS System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=$appUrl

DB_CONNECTION=mysql
DB_HOST=$db_host
DB_PORT=3306
DB_DATABASE=$db_database
DB_USERNAME=$db_username
DB_PASSWORD=$db_password

DB_SECOND_CONNECTION=mysql
DB_HOST_SECOND=$db2_host
DB_PORT_SECOND=3306
DB_DATABASE_SECOND=$db2_database
DB_USERNAME_SECOND=$db2_username
DB_PASSWORD_SECOND=$db2_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_DRIVER=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@jpos-system.com
MAIL_FROM_NAME="JPOS System"

VITE_APP_NAME="JPOS System"
ENV;

                file_put_contents($ENV_FILE, $env);
                logMsg("✅ Environment file created successfully");

                logMsg("▶ Generating application encryption key...");
                progress(40);
                execLogged("php artisan key:generate --force", $ROOT);
                logMsg("✅ Application encryption key generated successfully");

                logMsg("▶ Running database migrations...");
                progress(50);
                execLogged("php artisan migrate --force", $ROOT);
                logMsg("✅ Database migrations completed successfully");

                logMsg("▶ Seeding database with default data...");
                progress(55);
                execLogged("php artisan db:seed --force", $ROOT);
                logMsg("✅ Database seeded successfully");

                logMsg("▶ Creating storage symbolic link...");
                progress(65);
                execLogged("php artisan storage:link", $ROOT);
                logMsg("✅ Storage link created successfully");

                logMsg("▶ Setting up file permissions...");
                fixPermissions($ROOT, $IS_WIN);
                fixEnvPermissions($ROOT, $IS_WIN);
                logMsg("✅ Permissions configured successfully");

                logMsg("▶ Optimizing application performance...");
                execLogged("php artisan optimize:clear", $ROOT);
                progress(90);
                logMsg("✅ Application optimized successfully");

                file_put_contents($LOCK_FILE, 'installed at ' . date('Y-m-d H:i:s'));
                logMsg("✅ Installation lock file created");

                logMsg("🎉 Installation completed successfully!");
                progress(100);
                ?>
                <script>
                    // Show completion message and redirect
                    setTimeout(() => {
                        document.getElementById('completionMessage').classList.remove('hidden');
                        setTimeout(() => {
                            window.location.href = 'public/';
                        }, 3000);
                    }, 1000);
                </script>
                <?php
                if (file_exists(__FILE__)) {
                    if (unlink(__FILE__)) {
                        logMsg("✅ Installer script removed for security");
                    }
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
