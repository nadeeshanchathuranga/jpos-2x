<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;

class InstallationController extends Controller
{
    private $timeout = 300;

    /**
     * Show system requirements check
     */
    public function systemCheck()
    {
        $phpCheck = $this->checkPHPRequirements();
        $composerCheck = $this->checkComposer();
        $nodeCheck = $this->checkNodeJS();
        $laravelCheck = $this->checkLaravelProject();
        $systemInfo = $this->getSystemInfo();

        return view('installation.system-check', compact(
            'phpCheck',
            'composerCheck',
            'nodeCheck',
            'laravelCheck',
            'systemInfo'
        ));
    }

    /**
     * Proceed with setup
     */
    public function proceedSetup()
    {
        if ($this->envExists()) {
            return redirect()->route('installation.complete')
                ->with('success', 'Setup already completed! Your Laravel project is ready.');
        }

        if (!is_dir(base_path('vendor'))) {
            return redirect()->route('installation.composer');
        } elseif (!is_dir(base_path('node_modules'))) {
            return redirect()->route('installation.npm-install');
        } else {
            return redirect()->route('installation.env-setup');
        }
    }

    /**
     * Show composer installation page
     */
    public function composerInstall()
    {
        return view('installation.composer');
    }

    /**
     * Execute composer install
     */
    public function executeComposerInstall()
    {
        try {
            $result = $this->execCommand('composer install', 600);

            if ($result['success']) {
                return redirect()->route('installation.npm-install')
                    ->with('success', 'Composer packages installed successfully!');
            }

            return back()->with('error', 'Error installing composer packages: ' . $result['output']);
        } catch (Exception $e) {
            return back()->with('error', 'An unexpected error occurred during Composer installation.');
        }
    }

    /**
     * Show NPM installation page
     */
    public function npmInstall()
    {
        return view('installation.npm-install');
    }

    /**
     * Execute NPM install
     */
    public function executeNpmInstall()
    {
        try {
            $result = $this->execCommand('npm install', 600);

            if ($result['success']) {
                return redirect()->route('installation.npm-build')
                    ->with('success', 'NPM packages installed successfully!');
            }

            return back()->with('error', 'Error installing npm packages: ' . $result['output']);
        } catch (Exception $e) {
            return back()->with('error', 'An unexpected error occurred during NPM installation.');
        }
    }

    /**
     * Show NPM build page
     */
    public function npmBuild()
    {
        return view('installation.npm-build');
    }

    /**
     * Execute NPM build
     */
    public function executeNpmBuild()
    {
        try {
            $result = $this->execCommand('npm run build', 300);

            if ($result['success']) {
                return redirect()->route('installation.env-setup')
                    ->with('success', 'Assets built successfully!');
            }

            return back()->with('error', 'Error building assets: ' . $result['output']);
        } catch (Exception $e) {
            return back()->with('error', 'An unexpected error occurred during asset building.');
        }
    }

    /**
     * Show environment setup page
     */
    public function envSetup()
    {
        return view('installation.env-setup');
    }

    /**
     * Create .env file
     */
    public function createEnv()
    {
        if (File::copy(base_path('.env.example'), base_path('.env'))) {
            return redirect()->route('installation.env-config')
                ->with('success', '.env file created from .env.example');
        }

        return back()->with('error', 'Error creating .env file');
    }

    /**
     * Show database configuration page
     */
    public function envConfig()
    {
        return view('installation.env-config');
    }

    /**
     * Update database configuration
     */
    public function updateEnv(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_name' => 'required|string',
            'db_user' => 'required|string',
            'db_pass' => 'nullable|string',
            'hibernate' => 'nullable|boolean',
            'remote_db_host' => 'required_if:hibernate,1|string',
            'remote_db_port' => 'required_if:hibernate,1|integer',
            'remote_db_name' => 'required_if:hibernate,1|string',
            'remote_db_user' => 'required_if:hibernate,1|string',
            'remote_db_pass' => 'nullable|string',
        ]);

        $envContent = File::get(base_path('.env'));

        // Update primary database configuration
        $envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $envContent);
        $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $request->db_host, $envContent);
        $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $request->db_port, $envContent);
        $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $request->db_name, $envContent);
        $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $request->db_user, $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $request->db_pass, $envContent);

        // Add hibernate configuration if enabled
        if ($request->hibernate) {
            $remoteConfig = "\n\n# Remote Database Configuration (Hibernate)\n";
            $remoteConfig .= "REMOTE_DB_HOST=" . $request->remote_db_host . "\n";
            $remoteConfig .= "REMOTE_DB_PORT=" . $request->remote_db_port . "\n";
            $remoteConfig .= "REMOTE_DB_DATABASE=" . $request->remote_db_name . "\n";
            $remoteConfig .= "REMOTE_DB_USERNAME=" . $request->remote_db_user . "\n";
            $remoteConfig .= "REMOTE_DB_PASSWORD=" . $request->remote_db_pass . "\n";
            $remoteConfig .= "HIBERNATE_ENABLED=true\n";

            $envContent .= $remoteConfig;

            session([
                'hibernate_enabled' => true,
                'remote_config' => [
                    'host' => $request->remote_db_host,
                    'port' => $request->remote_db_port,
                    'database' => $request->remote_db_name,
                    'username' => $request->remote_db_user,
                    'password' => $request->remote_db_pass
                ]
            ]);
        } else {
            session(['hibernate_enabled' => false]);
        }

        session([
            'local_config' => [
                'host' => $request->db_host,
                'port' => $request->db_port,
                'database' => $request->db_name,
                'username' => $request->db_user,
                'password' => $request->db_pass
            ]
        ]);

        File::put(base_path('.env'), $envContent);

        // Auto-test and create databases
        try {
            $autoCreateResults = [];
            $localConfig = session('local_config');

            // Test and create local database
            $localDsn = "mysql:host={$localConfig['host']};port={$localConfig['port']}";
            $localPdo = new PDO($localDsn, $localConfig['username'], $localConfig['password']);

            $stmt = $localPdo->query("SHOW DATABASES LIKE '{$localConfig['database']}'");
            $localDbExists = $stmt->rowCount() > 0;

            if (!$localDbExists) {
                $localPdo->exec("CREATE DATABASE IF NOT EXISTS `{$localConfig['database']}`");
                $autoCreateResults[] = "✅ Local database '{$localConfig['database']}' created automatically!";
            } else {
                $autoCreateResults[] = "✅ Local database '{$localConfig['database']}' already exists.";
            }

            // Test and create remote database if hibernate is enabled
            if ($request->hibernate && session('remote_config')) {
                $remoteConfig = session('remote_config');
                $remoteDsn = "mysql:host={$remoteConfig['host']};port={$remoteConfig['port']}";
                $remotePdo = new PDO($remoteDsn, $remoteConfig['username'], $remoteConfig['password']);

                $stmt = $remotePdo->query("SHOW DATABASES LIKE '{$remoteConfig['database']}'");
                $remoteDbExists = $stmt->rowCount() > 0;

                if (!$remoteDbExists) {
                    $remotePdo->exec("CREATE DATABASE IF NOT EXISTS `{$remoteConfig['database']}`");
                    $autoCreateResults[] = "✅ Remote database '{$remoteConfig['database']}' created automatically!";
                } else {
                    $autoCreateResults[] = "✅ Remote database '{$remoteConfig['database']}' already exists.";
                }
            }

            return redirect()->route('installation.migrate')
                ->with('success', 'Database configuration updated! ' . implode(' ', $autoCreateResults));

        } catch (Exception $e) {
            return redirect()->route('installation.db-test')
                ->with('warning', 'Database configuration updated, but connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Test database connections
     */
    public function dbTest()
    {
        $hibernateEnabled = session('hibernate_enabled', false);
        $localTest = null;
        $remoteTest = null;

        if ($hibernateEnabled && session('local_config') && session('remote_config')) {
            $localTest = $this->testDatabaseConnection(
                session('local_config.host'),
                session('local_config.port'),
                session('local_config.database'),
                session('local_config.username'),
                session('local_config.password')
            );

            $remoteTest = $this->testDatabaseConnection(
                session('remote_config.host'),
                session('remote_config.port'),
                session('remote_config.database'),
                session('remote_config.username'),
                session('remote_config.password')
            );
        } else {
            $localTest = $this->testDatabaseConnection();
        }

        return view('installation.db-test', compact('hibernateEnabled', 'localTest', 'remoteTest'));
    }

    /**
     * Create databases
     */
    public function createDatabase()
    {
        try {
            $hibernateEnabled = session('hibernate_enabled', false);
            $successMessages = [];

            // Create local database
            $localConfig = session('local_config');
            $dsn = "mysql:host={$localConfig['host']};port={$localConfig['port']}";
            $pdo = new PDO($dsn, $localConfig['username'], $localConfig['password']);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$localConfig['database']}`");
            $successMessages[] = "Local database '{$localConfig['database']}' created successfully!";

            // Create remote database if hibernate is enabled
            if ($hibernateEnabled && session('remote_config')) {
                $remoteConfig = session('remote_config');
                $remoteDsn = "mysql:host={$remoteConfig['host']};port={$remoteConfig['port']}";
                $remotePdo = new PDO($remoteDsn, $remoteConfig['username'], $remoteConfig['password']);
                $remotePdo->exec("CREATE DATABASE IF NOT EXISTS `{$remoteConfig['database']}`");
                $successMessages[] = "Remote database '{$remoteConfig['database']}' created successfully!";
            }

            return redirect()->route('installation.migrate')
                ->with('success', implode(' ', $successMessages));
        } catch (Exception $e) {
            return back()->with('error', 'Error creating database(s): ' . $e->getMessage());
        }
    }

    /**
     * Show migration page
     */
    public function migrate()
    {
        $hibernateEnabled = session('hibernate_enabled', false);
        return view('installation.migrate', compact('hibernateEnabled'));
    }

    /**
     * Execute migrations
     */
    public function executeMigrate()
    {
        try {
            $hibernateEnabled = session('hibernate_enabled', false);
            $migrationResults = [];
            $allSuccess = true;

            // Run migrations on local database
            Artisan::call('migrate', ['--force' => true]);
            $localOutput = Artisan::output();

            if (strpos($localOutput, 'error') === false) {
                $migrationResults[] = "✅ Local database migrations completed successfully!";
            } else {
                $migrationResults[] = "❌ Local database migration failed: " . $localOutput;
                $allSuccess = false;
            }

            // Run migrations on remote database if hibernate is enabled
            if ($hibernateEnabled && session('remote_config')) {
                $remoteConfig = session('remote_config');

                // Temporarily switch database configuration
                $originalEnv = File::get(base_path('.env'));
                $tempEnv = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $remoteConfig['host'], $originalEnv);
                $tempEnv = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $remoteConfig['port'], $tempEnv);
                $tempEnv = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $remoteConfig['database'], $tempEnv);
                $tempEnv = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $remoteConfig['username'], $tempEnv);
                $tempEnv = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $remoteConfig['password'], $tempEnv);

                File::put(base_path('.env'), $tempEnv);
                Artisan::call('config:clear');
                Artisan::call('migrate', ['--force' => true]);
                $remoteOutput = Artisan::output();

                // Restore original configuration
                File::put(base_path('.env'), $originalEnv);
                Artisan::call('config:clear');

                if (strpos($remoteOutput, 'error') === false) {
                    $migrationResults[] = "✅ Remote database migrations completed successfully!";
                } else {
                    $migrationResults[] = "❌ Remote database migration failed: " . $remoteOutput;
                    $allSuccess = false;
                }
            }

            if ($allSuccess) {
                $nextRoute = $hibernateEnabled ? 'installation.seed-databases' : 'installation.generate-key';
                return redirect()->route($nextRoute)
                    ->with('success', implode(' ', $migrationResults));
            }

            return back()->with('error', implode(' ', $migrationResults));
        } catch (Exception $e) {
            return back()->with('error', 'Migration error: ' . $e->getMessage());
        }
    }

    /**
     * Show seed databases page
     */
    public function seedDatabases()
    {
        return view('installation.seed-databases');
    }

    /**
     * Execute database seeding
     */
    public function executeSeedDatabases()
    {
        try {
            $seedResults = [];

            // Seed local database
            Artisan::call('db:seed', ['--force' => true]);
            $localOutput = Artisan::output();
            $seedResults[] = "✅ Local database seeded successfully!";

            // Seed remote database if hibernate is enabled
            if (session('hibernate_enabled') && session('remote_config')) {
                $remoteConfig = session('remote_config');

                // Temporarily switch to remote database
                $originalEnv = File::get(base_path('.env'));
                $tempEnv = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $remoteConfig['host'], $originalEnv);
                $tempEnv = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $remoteConfig['port'], $tempEnv);
                $tempEnv = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $remoteConfig['database'], $tempEnv);
                $tempEnv = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $remoteConfig['username'], $tempEnv);
                $tempEnv = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . $remoteConfig['password'], $tempEnv);

                File::put(base_path('.env'), $tempEnv);
                Artisan::call('config:clear');
                Artisan::call('db:seed', ['--force' => true]);

                // Restore original configuration
                File::put(base_path('.env'), $originalEnv);
                Artisan::call('config:clear');

                $seedResults[] = "✅ Remote database seeded successfully!";
            }

            return redirect()->route('installation.generate-key')
                ->with('success', 'Database seeding completed: ' . implode(' ', $seedResults));
        } catch (Exception $e) {
            return redirect()->route('installation.generate-key')
                ->with('warning', 'Seeding completed with warnings: ' . $e->getMessage());
        }
    }

    /**
     * Show generate key page
     */
    public function generateKey()
    {
        return view('installation.generate-key');
    }

    /**
     * Execute key generation
     */
    public function executeGenerateKey()
    {
        try {
            Artisan::call('key:generate', ['--force' => true]);

            return redirect()->route('installation.storage-link')
                ->with('success', 'Application key generated successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Error generating key: ' . $e->getMessage());
        }
    }

    /**
     * Show storage link page
     */
    public function storageLink()
    {
        return view('installation.storage-link');
    }

    /**
     * Execute storage link creation
     */
    public function executeStorageLink()
    {
        try {
            Artisan::call('storage:link');

            return redirect()->route('installation.complete')
                ->with('success', 'Storage link created successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Error creating storage link: ' . $e->getMessage());
        }
    }

    /**
     * Show completion page
     */
    public function complete()
    {
        $isServerRunning = $this->isServerRunning();

        // If setup is complete and server is running, redirect to home page
        if ($this->envExists() && $isServerRunning) {
            return redirect('/')->with('success', 'Welcome! Your Laravel application is ready.');
        }

        return view('installation.complete', compact('isServerRunning'));
    }

    /**
     * Reset setup
     */
    public function resetSetup()
    {
        if (File::exists(base_path('.env'))) {
            File::delete(base_path('.env'));
        }

        session()->flush();

        return redirect()->route('installation.system-check')
            ->with('info', 'Setup has been reset! All configuration has been cleared.');
    }

    // Helper Methods

    private function checkPHPRequirements()
    {
        $requirements = [
            'php_version' => ['required' => '8.1.0', 'current' => PHP_VERSION, 'status' => version_compare(PHP_VERSION, '8.1.0', '>=')],
            'openssl' => ['required' => 'enabled', 'current' => extension_loaded('openssl') ? 'enabled' : 'disabled', 'status' => extension_loaded('openssl')],
            'pdo' => ['required' => 'enabled', 'current' => extension_loaded('pdo') ? 'enabled' : 'disabled', 'status' => extension_loaded('pdo')],
            'mbstring' => ['required' => 'enabled', 'current' => extension_loaded('mbstring') ? 'enabled' : 'disabled', 'status' => extension_loaded('mbstring')],
            'tokenizer' => ['required' => 'enabled', 'current' => extension_loaded('tokenizer') ? 'enabled' : 'disabled', 'status' => extension_loaded('tokenizer')],
            'xml' => ['required' => 'enabled', 'current' => extension_loaded('xml') ? 'enabled' : 'disabled', 'status' => extension_loaded('xml')],
            'ctype' => ['required' => 'enabled', 'current' => extension_loaded('ctype') ? 'enabled' : 'disabled', 'status' => extension_loaded('ctype')],
            'json' => ['required' => 'enabled', 'current' => extension_loaded('json') ? 'enabled' : 'disabled', 'status' => extension_loaded('json')],
            'bcmath' => ['required' => 'enabled', 'current' => extension_loaded('bcmath') ? 'enabled' : 'disabled', 'status' => extension_loaded('bcmath')],
        ];

        $allPassed = true;
        foreach ($requirements as $req) {
            if (!$req['status']) {
                $allPassed = false;
                break;
            }
        }

        return ['passed' => $allPassed, 'details' => $requirements];
    }

    private function checkComposer()
    {
        $result = $this->execCommand('composer --version');
        if ($result['success']) {
            preg_match('/Composer version ([0-9.]+)/', $result['output'], $matches);
            $version = $matches[1] ?? 'unknown';
            return ['installed' => true, 'version' => $version, 'output' => $result['output']];
        }
        return ['installed' => false, 'version' => null, 'output' => $result['output']];
    }

    private function checkNodeJS()
    {
        $nodeResult = $this->execCommand('node --version');
        $npmResult = $this->execCommand('npm --version');

        $nodeVersion = $nodeResult['success'] ? trim(str_replace('v', '', $nodeResult['output'])) : null;
        $npmVersion = $npmResult['success'] ? trim($npmResult['output']) : null;
        $nodeVersionOk = $nodeVersion ? version_compare($nodeVersion, '16.0.0', '>=') : false;

        return [
            'node_installed' => $nodeResult['success'],
            'npm_installed' => $npmResult['success'],
            'node_version' => $nodeVersion,
            'npm_version' => $npmVersion,
            'node_version_ok' => $nodeVersionOk,
        ];
    }

    private function checkLaravelProject()
    {
        $projectRoot = base_path();
        return [
            'composer_json' => File::exists($projectRoot . '/composer.json'),
            'artisan' => File::exists($projectRoot . '/artisan'),
            'env_example' => File::exists($projectRoot . '/.env.example'),
            'package_json' => File::exists($projectRoot . '/package.json'),
            'app_directory' => is_dir($projectRoot . '/app'),
            'vendor_exists' => is_dir($projectRoot . '/vendor'),
            'writable_storage' => is_writable($projectRoot . '/storage'),
            'writable_bootstrap' => is_writable($projectRoot . '/bootstrap/cache')
        ];
    }

    private function getSystemInfo()
    {
        return [
            'os' => PHP_OS_FAMILY,
            'php_sapi' => php_sapi_name(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];
    }

    private function envExists()
    {
        return File::exists(base_path('.env'));
    }

    private function testDatabaseConnection($host = null, $port = null, $database = null, $username = null, $password = null)
    {
        try {
            if ($host === null) {
                $host = env('DB_HOST', 'localhost');
                $port = env('DB_PORT', 3306);
                $database = env('DB_DATABASE', '');
                $username = env('DB_USERNAME', '');
                $password = env('DB_PASSWORD', '');
            }

            $dsn = "mysql:host={$host};port={$port}";
            $pdo = new PDO($dsn, $username, $password);

            $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
            $dbExists = $stmt->rowCount() > 0;

            return ['success' => true, 'db_exists' => $dbExists, 'database' => $database, 'host' => $host];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'host' => $host];
        }
    }

    private function isServerRunning()
    {
        $output = shell_exec('netstat -ano | findstr :8000 | findstr LISTENING');
        return !empty(trim($output));
    }

    private function execCommand($command, $timeout = 300)
    {
        $output = [];
        $returnVar = 0;

        try {
            exec($command . ' 2>&1', $output, $returnVar);

            return [
                'output' => implode("\n", $output),
                'success' => $returnVar === 0,
                'timeout' => false
            ];
        } catch (Exception $e) {
            return [
                'output' => 'Command execution failed: ' . $e->getMessage(),
                'success' => false,
                'timeout' => strpos($e->getMessage(), 'time') !== false
            ];
        }
    }
}
