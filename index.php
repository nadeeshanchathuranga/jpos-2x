<?php
/**
 * Root index.php for deployment checks
 */

$root = __DIR__;

// Required paths
$vendorDir = $root . '/vendor';
$envFile   = $root . '/.env';
$nodeDir   = $root . '/node_modules';
$npmBuild  = $root . '/public/js/app.js'; // adjust if your build generates different file

// Redirect function
function redirect($path) {
    header("Location: $path");
    exit;
}

// Check Laravel essentials
if (!is_dir($vendorDir) || !file_exists($envFile)) {
    redirect('install.php');
}

// Check .env database settings
$dbKeys = ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
$envContents = file_get_contents($envFile);
foreach ($dbKeys as $key) {
    if (!preg_match("/^{$key}=.+/m", $envContents)) {
        redirect('install.php');
    }
}

// Optional: check front-end build (node + npm)
if (!is_dir($nodeDir) || !file_exists($npmBuild)) {
    redirect('install.php');
}

// All good → redirect to public/
redirect('public/');
