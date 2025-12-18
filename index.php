<?php

define('BASE_PATH', __DIR__);
define('LOCK_FILE', BASE_PATH . '/storage/installed.lock');

if (file_exists($lockFile = LOCK_FILE)) {
    header('Location: public/');
    exit;
}

header('Location: install.php');
exit;
