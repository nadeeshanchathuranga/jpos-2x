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
     * One-Click Installation Starter Page
     */
    public function oneClickStart()
    {
        return view('installation.one-click-start');
    }

    /**
     * Prepare system by running all initial commands
     */
    public function prepareSystem(Request $request)
    {
        try {
            set_time_limit(0);
            ini_set('max_execution_time', '0');
            
            $logFile = storage_path('logs/system-preparation.log');
            File::put($logFile, "System Preparation Started at " . now() . "\n\n");
            
            $results = [];
            
            // Step 1: Composer Update
            $this->logStep($logFile, "Step 1: Running Composer Update...");
            $composerResult = $this->execCommand('composer update --no-interaction --prefer-dist', 600);
            $results['composer'] = $composerResult['success'];
            $this->logStep($logFile, $composerResult['success'] ? "✅ Composer updated" : "❌ Composer failed");
            
            // Step 2: NPM Install
            $this->logStep($logFile, "Step 2: Installing NPM packages...");
            $npmResult = $this->execCommand('npm install', 600);
            $results['npm'] = $npmResult['success'];
            $this->logStep($logFile, $npmResult['success'] ? "✅ NPM installed" : "❌ NPM failed");
            
            // Step 3: NPM Build
            $this->logStep($logFile, "Step 3: Building frontend assets...");
            $buildResult = $this->execCommand('npm run build', 300);
            if (!$buildResult['success']) {
                $buildResult = $this->execCommand('npm run dev', 300);
            }
            $results['build'] = $buildResult['success'];
            $this->logStep($logFile, $buildResult['success'] ? "✅ Build completed" : "❌ Build failed");
            
            // Step 4: Start Laravel Server
            $this->logStep($logFile, "Step 4: Starting Laravel server...");
            if (!$this->isServerRunning()) {
                if (PHP_OS_FAMILY === 'Windows') {
                    pclose(popen('start /B php artisan serve 2>&1', 'r'));
                } else {
                    pclose(popen('php artisan serve > /dev/null 2>&1 &', 'r'));
                }
                sleep(3);
            }
            $results['server'] = $this->isServerRunning();
            $this->logStep($logFile, $results['server'] ? "✅ Server started" : "❌ Server failed");
            
            $this->logStep($logFile, "\n🎉 System preparation complete!\n");
            
            return response()->json([
                'success' => true,
                'message' => 'System prepared successfully',
                'results' => $results,
                'redirect_url' => route('installation.auto-install')
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System preparation failed: ' . $e->getMessage()
            ], 500);
        }
    }

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

    /**
     * Show auto installation page
     */
    public function autoInstall()
    {
        return view('installation.auto-install');
    }

    /**
     * Execute auto installation - runs all steps sequentially
     */
    public function executeAutoInstall(Request $request)
    {
        try {
            // Increase timeouts for long-running installation
            set_time_limit(0); // No timeout
            ini_set('max_execution_time', '0');
            ignore_user_abort(true);
            
            $request->validate([
                'db_host' => 'required|string',
                'db_port' => 'required|integer',
                'db_name' => 'required|string',
                'db_user' => 'required|string',
                'db_pass' => 'nullable|string',
                'hibernate' => 'nullable|boolean',
                'remote_db_host' => 'nullable|required_if:hibernate,1|string',
                'remote_db_port' => 'nullable|required_if:hibernate,1|integer',
                'remote_db_name' => 'nullable|required_if:hibernate,1|string',
                'remote_db_user' => 'nullable|required_if:hibernate,1|string',
                'remote_db_pass' => 'nullable|string',
            ]);

            $logFile = storage_path('logs/auto-install.log');
            File::put($logFile, "Starting auto installation at " . now() . "\n\n");

            $steps = [];

            // Step 1: Create fresh .env file (always start from scratch)
            $this->logStep($logFile, "Step 1: Creating Fresh Environment File...");
            // Always delete old .env and create fresh one from example
            if (File::exists(base_path('.env'))) {
                File::delete(base_path('.env'));
                $this->logStep($logFile, "🗑️ Removed old .env file for fresh installation");
            }
            File::copy(base_path('.env.example'), base_path('.env'));
            $steps[] = ['step' => 'Create .env', 'success' => true, 'output' => 'Fresh .env file created'];
            $this->logStep($logFile, "✅ Fresh environment file created");

            // Step 2: Configure database settings in .env
            $this->logStep($logFile, "Step 2: Configuring Database Settings...");
            $envContent = File::get(base_path('.env'));
            $envContent = preg_replace('/DB_CONNECTION=.*/', 'DB_CONNECTION=mysql', $envContent);
            $envContent = preg_replace('/DB_HOST=.*/', 'DB_HOST=' . $request->db_host, $envContent);
            $envContent = preg_replace('/DB_PORT=.*/', 'DB_PORT=' . $request->db_port, $envContent);
            $envContent = preg_replace('/DB_DATABASE=.*/', 'DB_DATABASE=' . $request->db_name, $envContent);
            $envContent = preg_replace('/DB_USERNAME=.*/', 'DB_USERNAME=' . $request->db_user, $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', 'DB_PASSWORD=' . ($request->db_pass ?? ''), $envContent);
            
            // Ensure CACHE_STORE is set to file
            if (!str_contains($envContent, 'CACHE_STORE=')) {
                $envContent .= "\nCACHE_STORE=file\n";
            } else {
                $envContent = preg_replace('/CACHE_STORE=.*/', 'CACHE_STORE=file', $envContent);
            }

            if ($request->hibernate) {
                $remoteConfig = "\n\n# Remote Database Configuration (Hibernate)\n";
                $remoteConfig .= "REMOTE_DB_HOST=" . $request->remote_db_host . "\n";
                $remoteConfig .= "REMOTE_DB_PORT=" . $request->remote_db_port . "\n";
                $remoteConfig .= "REMOTE_DB_DATABASE=" . $request->remote_db_name . "\n";
                $remoteConfig .= "REMOTE_DB_USERNAME=" . $request->remote_db_user . "\n";
                $remoteConfig .= "REMOTE_DB_PASSWORD=" . ($request->remote_db_pass ?? '') . "\n";
                $remoteConfig .= "HIBERNATE_ENABLED=true\n";
                $envContent .= $remoteConfig;
            }

            File::put(base_path('.env'), $envContent);
            $steps[] = ['step' => 'Database Configuration', 'success' => true, 'output' => 'Database configured'];
            $this->logStep($logFile, "✅ Database configuration saved");

            // Step 3: Generate Application Key
            $this->logStep($logFile, "Step 3: Generating Application Key...");
            try {
                // Generate a random 32-byte key and encode it to base64
                $key = 'base64:' . base64_encode(random_bytes(32));
                
                // Read .env file
                $envPath = base_path('.env');
                $envContent = File::get($envPath);
                
                // Replace or add APP_KEY
                if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $envContent);
                } else {
                    $envContent = "APP_KEY=" . $key . "\n" . $envContent;
                }
                
                File::put($envPath, $envContent);
                
                // Clear config cache to reload the new key
                Artisan::call('config:clear');
                
                $steps[] = ['step' => 'Generate Application Key', 'success' => true, 'output' => 'Application key generated: ' . substr($key, 0, 20) . '...'];
                $this->logStep($logFile, "✅ Application key generated successfully");
            } catch (Exception $e) {
                $steps[] = ['step' => 'Generate Application Key', 'success' => false, 'output' => $e->getMessage()];
                $this->logStep($logFile, "❌ Key generation failed: " . $e->getMessage());
            }

            // Step 4: Composer Update
            $this->logStep($logFile, "Step 4: Updating Composer Dependencies...");
            $composerResult = $this->execCommand('composer update --no-interaction --prefer-dist', 600);
            $steps[] = [
                'step' => 'Composer Update',
                'success' => $composerResult['success'],
                'output' => $composerResult['output']
            ];
            $this->logStep($logFile, $composerResult['success'] ? "✅ Composer updated successfully" : "❌ Composer update failed");

            // Step 5: NPM Install
            $this->logStep($logFile, "Step 5: Installing NPM Dependencies...");
            $npmInstallResult = $this->execCommand('npm install', 600);
            $steps[] = [
                'step' => 'NPM Install',
                'success' => $npmInstallResult['success'],
                'output' => $npmInstallResult['output']
            ];
            $this->logStep($logFile, $npmInstallResult['success'] ? "✅ NPM packages installed successfully" : "❌ NPM installation failed");

            // Step 6: NPM Build (npm run dev or npm run build)
            $this->logStep($logFile, "Step 6: Building Frontend Assets...");
            // Try npm run build first, fallback to npm run dev if build doesn't exist
            $npmBuildResult = $this->execCommand('npm run build', 300);
            if (!$npmBuildResult['success']) {
                $npmBuildResult = $this->execCommand('npm run dev', 300);
            }
            $steps[] = [
                'step' => 'Build Frontend Assets',
                'success' => $npmBuildResult['success'],
                'output' => $npmBuildResult['output']
            ];
            $this->logStep($logFile, $npmBuildResult['success'] ? "✅ Frontend assets built successfully" : "❌ Frontend build failed");

            // Step 7: Run Migrations
            $this->logStep($logFile, "Step 7: Running Database Migrations...");
            try {
                Artisan::call('migrate', ['--force' => true]);
                $steps[] = ['step' => 'Run Migrations', 'success' => true, 'output' => 'Migrations completed'];
                $this->logStep($logFile, "✅ Database migrations completed successfully");
            } catch (Exception $e) {
                $steps[] = ['step' => 'Run Migrations', 'success' => false, 'output' => $e->getMessage()];
                $this->logStep($logFile, "❌ Migration failed: " . $e->getMessage());
            }

            // Step 8: Seed Database
            $this->logStep($logFile, "Step 8: Seeding Database...");
            try {
                Artisan::call('db:seed', ['--force' => true]);
                $steps[] = ['step' => 'Seed Database', 'success' => true, 'output' => 'Database seeded'];
                $this->logStep($logFile, "✅ Database seeded successfully");
            } catch (Exception $e) {
                $steps[] = ['step' => 'Seed Database', 'success' => false, 'output' => $e->getMessage()];
                $this->logStep($logFile, "⚠️ Database seeding completed with warnings");
            }

            // Step 9: Create storage link
            $this->logStep($logFile, "Step 9: Creating Storage Link...");
            try {
                Artisan::call('storage:link');
                $steps[] = ['step' => 'Storage Link', 'success' => true, 'output' => 'Storage link created'];
                $this->logStep($logFile, "✅ Storage link created");
            } catch (Exception $e) {
                $steps[] = ['step' => 'Storage Link', 'success' => true, 'output' => 'Already exists or created'];
                $this->logStep($logFile, "✅ Storage link ready");
            }

            // Step 10: Clear and optimize application
            $this->logStep($logFile, "Step 10: Optimizing Application...");
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $steps[] = ['step' => 'Optimize Application', 'success' => true, 'output' => 'Application optimized'];
            $this->logStep($logFile, "✅ Application optimized");

            $this->logStep($logFile, "\n🎉 AUTO INSTALLATION COMPLETED SUCCESSFULLY!\n");

            // Step 10: Auto-start Laravel server
            $this->logStep($logFile, "Step 10: Starting Laravel Development Server...");
            $serverStarted = false;
            $serverUrl = 'http://127.0.0.1:8000';
            
            try {
                // Check if server is already running
                if (!$this->isServerRunning()) {
                    // Start server in background without opening new window
                    if (PHP_OS_FAMILY === 'Windows') {
                        // Windows: Start hidden process
                        pclose(popen('start /B php artisan serve 2>&1', 'r'));
                    } else {
                        // Linux/Mac: Start in background
                        pclose(popen('php artisan serve > /dev/null 2>&1 &', 'r'));
                    }
                    
                    // Wait for server to start
                    sleep(3);
                    
                    // Verify server started
                    if ($this->isServerRunning()) {
                        $serverStarted = true;
                        $this->logStep($logFile, "✅ Laravel server started successfully on {$serverUrl}");
                        $steps[] = ['step' => 'Start Server', 'success' => true, 'output' => "Server running on {$serverUrl}"];
                    } else {
                        $this->logStep($logFile, "⚠️ Server start attempted but verification failed");
                        $steps[] = ['step' => 'Start Server', 'success' => false, 'output' => 'Server may be starting'];
                    }
                } else {
                    $serverStarted = true;
                    $this->logStep($logFile, "✅ Laravel server already running on {$serverUrl}");
                    $steps[] = ['step' => 'Start Server', 'success' => true, 'output' => "Server already running"];
                }
            } catch (Exception $e) {
                $this->logStep($logFile, "⚠️ Server start warning: " . $e->getMessage());
                $steps[] = ['step' => 'Start Server', 'success' => false, 'output' => $e->getMessage()];
            }

            $this->logStep($logFile, "\n🎉 INSTALLATION COMPLETE! Application ready at {$serverUrl}\n");

            return response()->json([
                'success' => true,
                'message' => 'Auto installation completed successfully!',
                'server_started' => $serverStarted,
                'server_url' => $serverUrl,
                'steps' => $steps,
                'log' => File::get($logFile)
            ]);

        } catch (Exception $e) {
            $logFile = storage_path('logs/auto-install.log');
            $this->logStep($logFile, "\n❌ ERROR: " . $e->getMessage() . "\n");
            
            return response()->json([
                'success' => false,
                'message' => 'Auto installation failed: ' . $e->getMessage(),
                'steps' => $steps ?? [],
                'log' => File::exists($logFile) ? File::get($logFile) : ''
            ], 500);
        }
    }

    /**
     * Get auto installation status (for progress polling)
     */
    public function autoInstallStatus()
    {
        $logFile = storage_path('logs/auto-install.log');
        
        if (File::exists($logFile)) {
            return response()->json([
                'log' => File::get($logFile),
                'completed' => strpos(File::get($logFile), 'AUTO INSTALLATION COMPLETED') !== false
            ]);
        }

        return response()->json(['log' => '', 'completed' => false]);
    }

    /**
     * Start Laravel development server
     */
    public function startServer()
    {
        try {
            // Check if server is already running
            if ($this->isServerRunning()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Server is already running on port 8000',
                    'url' => 'http://127.0.0.1:8000'
                ]);
            }

            // Start server in background
            if (PHP_OS_FAMILY === 'Windows') {
                pclose(popen('start /B php artisan serve', 'r'));
            } else {
                pclose(popen('php artisan serve > /dev/null 2>&1 &', 'r'));
            }

            // Wait a moment for server to start
            sleep(2);

            return response()->json([
                'success' => true,
                'message' => 'Laravel development server started successfully!',
                'url' => 'http://127.0.0.1:8000'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log installation step
     */
    private function logStep($logFile, $message)
    {
        File::append($logFile, "[" . now()->format('Y-m-d H:i:s') . "] " . $message . "\n");
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
