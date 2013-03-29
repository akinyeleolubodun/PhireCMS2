<?php
/**
 * Phire CMS 2.0 Configuration File
 */

/**
 * Path and URI Configuration Settings
 */

// Calculate and define the base path
define('BASE_PATH', str_replace(array(realpath($_SERVER['DOCUMENT_ROOT']), '\\'), array('', '/'), realpath(__DIR__)));

// Define the content path, where the application assets are stored
define('CONTENT_PATH', '/phire-content');

// Define the application path, where the application files are located
define('APP_PATH', '/phire-cms');

// Define the application URI, how you access the application
define('APP_URI', '/phire');

/**
 * Database Configuration Settings
 */

// Define the database interface
// 'Mysql', 'Mysqli', 'Pgsql', 'Sqlite' or 'Pdo'
define('DB_INTERFACE', '');

// Define the database DSN type (used with 'Pdo' interface only)
// 'mysql', 'pgsql' or 'sqlite'
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

// Define the default language
define('POP_LANG', 'en_US');
