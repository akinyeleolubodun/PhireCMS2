<?php
/**
 * Path and URI Configuration Settings
 */

// Define the base path, the folder in which the application is located
define('BASE_PATH', str_replace(array(realpath($_SERVER['DOCUMENT_ROOT']), '\\'), array('', '/'), realpath(__DIR__)));

// Define the content path, where the application assets are stored
define('CONTENT_PATH', '/phire-content');

// Define the application directory, where the application files are located
define('APP_PATH', '/phire-cms');

// Define the application URI, how you access the application
define('APP_URI', '/phire');

/**
 * Database Configuration Settings
 */

// Define the database interface
// 'Mysql', 'Mysqli', 'Sqlsrv', 'Pgsql', 'Sqlite' or 'Pdo'
define('DB_INTERFACE', '');

// Define the database type (for Pdo only)
// 'mysql', 'sqlsrv', 'pgsql' or 'sqlite'
define('DB_TYPE', '');

// Define the database name
define('DB_NAME', '');

// Define the database user
define('DB_USER', '');

// Define the database password
define('DB_PASS', '');

// Define the database host
define('DB_HOST', '');

// Define the database prefix
define('DB_PREFIX', '');

/**
 * Language Settings
 */

// Define the default language and locale
define('POP_LANG', 'en_US');
