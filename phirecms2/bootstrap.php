<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Loader
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2013 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

// Require the config file
require_once 'config.php';

// Check the path and URI constants
if (!defined('BASE_PATH') || !defined('APP_PATH') || !defined('APP_URI') ||
    !defined('DB_INTERFACE') || !defined('DB_NAME')) {
    throw new \Exception('Error: The config file is not properly configured. Please check the config file or install the system.');
}

/**
 * IMPORTANT!
 *
 * Require the Autoloader class file and instantiate the autoloader object.
 * If you change the relationship between this file and the framework,
 * adjust the path accordingly.
 */
require_once __DIR__ . APP_PATH . '/vendor/PopPHPFramework/src/Pop/Loader/Autoloader.php';

$autoloader = new \Pop\Loader\Autoloader();
$autoloader->splAutoloadRegister();

/**
 * Add any additional custom code or loader features below this doc block.
 * For example, you can register a third-party library or load a classmap file.
 * Some examples are:
 *
 *     $autoloader->register('YourLib', __DIR__ . '/../vendor/YourLib/src');
 *     $autoloader->loadClassMap('../vendor/YourLib/classmap.php');
 */

$autoloader->register('Phire', __DIR__ . APP_PATH . '/module/Phire/src');

// Create a project object
$project = Phire\Project::factory(
    include __DIR__ . APP_PATH . '/config/project.config.php',
    include __DIR__ . APP_PATH . '/module/Phire/config/module.config.php',
    new Pop\Mvc\Router(array(
        '/'        => 'Phire\Controller\IndexController',
        APP_URI => array(
            '/'     => 'Phire\Controller\Phire\IndexController',
            '/user' => 'Phire\Controller\Phire\UserController'
        )
    ))
);