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
                '/install'  => 'Phire\Controller\User\InstallController',
                '/roles'    => 'Phire\Controller\User\RolesController',
                '/sessions' => 'Phire\Controller\User\SessionsController',
                '/types'    => 'Phire\Controller\User\TypesController',
                '/users'    => 'Phire\Controller\User\UsersController'
            )
        ));

        // Get any other user types and declare their URI / Controller mapping
        if ((DB_INTERFACE != '') || (DB_NAME != '')) {
            $types = \PopUser\Table\Types::findAll();

            foreach ($types->rows as $type) {
                if (($type->type != 'user')) {
                    // If the user type has a defined controller
                    if ($type->controller != '') {
                        // If the user type has defined sub-controllers
                        if ($type->sub_controllers != '') {
                            $controller = array('/' => $type->controller);
                            $namespace = substr($type->controller, 0, (strrpos($type->controller, '\\') + 1));
                            $subs = explode(',', $type->sub_controllers);
                            foreach ($subs as $sub) {
                                $sub = trim($sub);
                                $controller['/' . $sub] = $namespace . ucfirst($sub) . 'Controller';
                            }
                        } else {
                            $controller = $type->controller;
                        }
                    // Else, just map to the base User controller
                    } else {
                        $controller = 'Phire\Controller\User\IndexController';
                    }

                    $this->router->addControllers(array(
                        '/' . $type->type => $controller
                    ));
                }
            }
        }

        parent::run();
    }

    /**
     * Method to check if the system is installed
     *
     * @param  boolean $suppress
     * @throws \Exception
     * @return boolean
     */
    public static function isInstalled($suppress = false)
    {
        if ((strpos($_SERVER['REQUEST_URI'], BASE_PATH . APP_URI . '/install') === false) &&
            ((DB_INTERFACE == '') || (DB_NAME == ''))) {
                if (!$suppress) {
                    throw new \Exception('Error: The config file is not properly configured. Please check the config file or install the application.');
                } else {
                    return false;
                }
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

