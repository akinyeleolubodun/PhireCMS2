<?php
/**
 * @namespace
 */
namespace Phire;

use Pop\Project\Project as P;

class Project extends P
{

    /**
     * Add any project specific code to this method for run-time use here.
     *
     * @return void
     */
    public function run()
    {
        // Set the ACL service
        $this->setService('acl', '\Pop\Auth\Acl::factory');

        // Add main user routes
        $this->router->addControllers(array(
            APP_URI  => array(
                '/'         => 'Phire\Controller\User\IndexController',
                '/roles'    => 'Phire\Controller\User\RolesController',
                '/sessions' => 'Phire\Controller\User\SessionsController',
                '/types'    => 'Phire\Controller\User\TypesController',
                '/users'    => 'Phire\Controller\User\UsersController'
            )
        ));

        // Get any other user types and declare their URI / Controller mapping
        $types = \Phire\Table\UserTypes::findAll();

        foreach ($types->rows as $type) {
            if (($type->type != 'User')) {
                $this->router->addControllers(array(
                    '/' . strtolower($type->type) => 'Phire\Controller\\' . $type->type . '\IndexController'
                ));
            }
        }

        parent::run();
    }

    /**
     * Method to check if the system is installed
     *
     * @throws \Exception
     * @return boolean
     */
    public static function isInstalled()
    {
        if (($_SERVER['REQUEST_URI'] != BASE_PATH . APP_URI . '/install') &&
            ($_SERVER['REQUEST_URI'] != BASE_PATH . APP_URI . '/install/user') &&
            ((DB_INTERFACE == '') || (DB_NAME == ''))) {
            throw new \Exception('Error: The config file is not properly configured. Please check the config file or install the application.');
        }

        return true;
    }

    /**
     * Determine whether or not the necessary system directories are writable or not.
     *
     * @param  string $contentDir
     * @param  boolean $msgs
     * @return boolean|array
     */
    public static function checkDirs($contentDir, $msgs = false)
    {
        $dir = new \Pop\File\Dir($contentDir, true, true);
        $files = $dir->getFiles();
        $errorMsgs = array();

        // Check if the necessary directories are writable for Windows.
        if (stripos(PHP_OS, 'win') !== false) {
            touch($contentDir . '/writetest.txt');
            clearstatcache();
            if (!file_exists($contentDir . '/writetest.txt')) {
                $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $contentDir) . " is not writable.";
            } else {
                unlink($contentDir . '/writetest.txt');
            }
            foreach ($files as $value) {
                if (is_dir($value)) {
                    touch($value . '/writetest.txt');
                    clearstatcache();
                    if (!file_exists($value . '/writetest.txt')) {
                        $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $value) . " is not writable.";
                    } else {
                        unlink($value . '/writetest.txt');
                    }
                }
            }
            // Check if the necessary directories are writable for Unix/Linux.
        } else {
            clearstatcache();
            if (!is_writable($contentDir)) {
                $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $contentDir) . " is not writable.";
            }
            foreach ($files as $value) {
                if (is_dir($value)) {
                    clearstatcache();
                    if (!is_writable($value)) {
                        $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $value) . " is not writable.";
                    }
                }
            }
        }

        // If the messaging flag was passed, return any
        // error messages, else return true/false.
        if ($msgs) {
            return $errorMsgs;
        } else {
            return (count($errorMsgs) == 0) ? true : false;
        }

    }

}

