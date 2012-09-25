<?php
/**
 * Pop PHP Framework Bootstrap File
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * IMPORTANT!
 *
 * If you move this 'bootstrap.php' file, make sure you adjust
 * the path to the Autoloader class file in the 'require'
 * statement below accordingly.
 *
 */

// Require the config file
require_once 'config.php';

// Check the path and URI constants
if (!defined('BASE_URI') || !defined('SYSTEM_URI') || !defined('SYSTEM_DIR') ||
    !defined('CONTENT_DIR') || !defined('DB_INTERFACE') || !defined('DB_NAME')) {
    echo 'The config file is not properly configured. Please check the config file or install the system.';
    exit(0);
}

// Require the Autoloader class file
require_once __DIR__ . SYSTEM_DIR . '/vendor/PopPHPFramework/src/Pop/Loader/Autoloader.php';

// Instantiate the autoloader object
$autoloader = new Pop\Loader\Autoloader();
$autoloader->splAutoloadRegister();

/*
 * Unless you move this 'bootstrap.php' file and need to alter the path to the
 * Autoloader class file in the 'require_once' statement above, then it is best NOT
 * to edit this file above this doc block.
 *
 * However, you can add any optional custom code or loader features below this
 * doc block, such as, registering a third-party library or loading a class
 * map file. Some examples are commented out below.
 */

// $autoloader->register('YourLib', __DIR__ . '/../vendor/YourLib/src');
// $autoloader->loadClassMap('../vendor/YourLib/classmap.php');

$autoloader->register('Phire', __DIR__ . SYSTEM_DIR . '/module/Phire/src');

// Create a project object
$project = Phire\Project::factory(
    include __DIR__ . SYSTEM_DIR . '/config/project.config.php',
    include __DIR__ . SYSTEM_DIR . '/module/Phire/config/module.config.php',
    new Pop\Mvc\Router(array(
        'default'                       => 'Phire\Controller\DefaultController',
        substr(SYSTEM_URI, 1)           => 'Phire\Controller\SystemController',
        substr(SYSTEM_URI, 1) . '/user' => 'Phire\Controller\UserController'
    ))
);
