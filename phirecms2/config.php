<?php
/**
 * Phire CMS 2.0 Configuration File
 */

/**
 * Path and URI Configuration Settings
 */

// Calculate and define the base path
$basePath = str_replace(array(realpath($_SERVER['DOCUMENT_ROOT']), '\\'), array('', '/'), realpath(__DIR__));
define('BASE_PATH', (!empty($basePath) ? $basePath : ''));

// Define the application path
define('APP_PATH', '/phire-cms');

// Define the media path
define('CONTENT_PATH', '/phire-content');

// Define the application URI
define('APP_URI', '/phire');

/**
 * Database Configuration Settings
 */

// Define the database interface
// 'Mysqli', 'Pgsql', 'Sqlite' or 'Pdo'
define('DB_INTERFACE', 'Mysqli');

// Define the database DSN type (for Pdo only)
// 'mysql', 'pgsql' or 'sqlite'
define('DB_TYPE', '');

// Define the database name
define('DB_NAME', 'phirecms');

// Define the database user
define('DB_USER', 'phire');

// Define the database password
define('DB_PASS', '12cms34');

// Define the database host
define('DB_HOST', 'localhost');

// Define the database prefix
define('DB_PREFIX', 'ph_');

