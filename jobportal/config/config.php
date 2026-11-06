<?php
// Core configuration for WAMP (/wamp64/www/jobportal/)
define('BASE_URL', '/jobportal');
define('ROOT_PATH', dirname(__DIR__));

define('DB_HOST', 'localhost');
define('DB_NAME', 'jobportal');
define('DB_USER', 'root');
define('DB_PASS', '');          // WAMP default is an empty password
define('CURRENCY', 'LKR');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

spl_autoload_register(function (string $class): void {
    $file = ROOT_PATH . '/app/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require ROOT_PATH . '/app/helpers.php';
