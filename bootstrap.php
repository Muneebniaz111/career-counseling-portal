<?php
/**
 * Bootstrap File - Application Initialization
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

if (!defined('PUBLIC_DIR')) {
    define('PUBLIC_DIR', APP_ROOT . '/public');
}

if (!defined('APP_DIR')) {
    define('APP_DIR', APP_ROOT . '/app');
}

if (!defined('DATABASE_DIR')) {
    define('DATABASE_DIR', APP_ROOT . '/database');
}

// Set include path
set_include_path(get_include_path() . PATH_SEPARATOR . APP_ROOT);

// Include database configuration
require_once APP_DIR . '/config/db_connection.php';

// Include common helper functions
if (file_exists(APP_DIR . '/helpers/functions.php')) {
    require_once APP_DIR . '/helpers/functions.php';
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

