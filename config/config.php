<?php
// Use SQLite (no MySQL server needed)
define('DB_TYPE', 'sqlite');

// MySQL config (not used with SQLite)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'drahmi_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('JWT_SECRET', 'drahmi_jwt_secret_key_2026');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRY', 86400);

define('BASE_URL', 'http://localhost/drahmi-api');

define('APP_NAME', 'Drahmi');
define('APP_VERSION', '1.0.0');

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);