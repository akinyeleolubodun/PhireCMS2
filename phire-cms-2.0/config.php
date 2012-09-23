<?php
/**
 * URI and Path Configuration Settings
 */

// Define the base URI
define('BASE_URI', str_replace('\\', '/', str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__))));

// Define the system URI
define('SYSTEM_URI', '/phire');

// Define the system directory
define('SYSTEM_DIR', '/phire-cms');

// Define the content directory
define('CONTENT_DIR', '/phire-content');

/**
 * Database Configuration Settings
 */

// Define the database interface type
define('DB_INTERFACE', '');

// Define the database name
define('DB_NAME', '');

// Define the database user
define('DB_USER', '');

// Define the database password
define('DB_PASSWORD', '');

// Define the database host
define('DB_HOST', '');

// Define the database prefix
define('DB_PREFIX', '');
