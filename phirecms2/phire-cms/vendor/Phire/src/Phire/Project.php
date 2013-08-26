<?php
/**
 * @namespace
 */
namespace Phire;

use Pop\File\Dir;
use Pop\Project\Project as P;

class Project extends P
{

    /**
     * Project assets
     */
    protected $assets = null;

    /**
     * Register and load any other modules
     *
     * @param  \Pop\Loader\Autoloader $autoloader
     * @throws \Exception
     * @return self
     */
    public function load($autoloader)
    {
        if (!self::checkDirs($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH)) {
            throw new \Exception('Error: The content folder(s) are not writable.');
        }


        $modulesDirs = array(
            __DIR__ . '/../../../',
            __DIR__ . '/../../../../module',
            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/',
            $_SERVER['DOCUMENT_ROOT'] . '/../module/'
        );

        // Register and load any other internal modules
        foreach ($modulesDirs as $directory) {
            if (file_exists($directory) && is_dir($directory)) {
                $dir = new Dir($directory);
                $dirs = $dir->getFiles();
                sort($dirs);
                foreach ($dirs as $d) {
                    $moduleCfg = null;
                    if (($d != 'PopPHPFramework') && ($d != 'config') && ($d != 'vendor') && (is_dir($directory . $d))) {
                        $this->loadAssets($directory . $d . '/data', $d);
                        if ($d != 'Phire') {
                            if (file_exists($directory . $d . '/src')) {
                                $autoloader->register($d, $directory . $d . '/src');
                            }
                            // Get module config
                            if (file_exists($directory . $d . '/config/module.config.php')) {
                                $moduleCfg = include $directory . $d . '/config/module.config.php';
                            }
                            // Check for any module config overrides
                            if (file_exists($directory . '/config/' . strtolower($d) . '.config.php')) {
                                $override = include $directory . '/config/' . strtolower($d) . '.config.php';
                                if (isset($override[$d])) {
                                    $moduleCfg[$d]->merge($override[$d]);
                                }
                            }
                            // Load module configs
                            if (null !== $moduleCfg) {
                                $this->loadModule($moduleCfg);
                            }
                        }
                    }
                }
            }
        }

        // Initiate the router object
        $this->loadRouter(new \Pop\Mvc\Router(array(), new \Pop\Http\Request(null, BASE_PATH)));

        return $this;
    }

    /**
     * Get project assets
     *
     * @return string
     */
    public function getAssets()
    {
        return $this->assets['js'] . $this->assets['css'] . PHP_EOL;
    }

    /**
     * Add any project specific code to this method for run-time use here.
     *
     * @return void
     */
    public function run()
    {
        // Set the services
        $this->setService('acl', 'Phire\Auth\Acl');
        $this->setService('auth', 'Phire\Auth\Auth');
        $this->setService('nav', 'Pop\Nav\Nav');

        // Get loaded modules and add their routes and nav
        $modules = $this->modules();
        $nav = $this->getService('nav');

        // Load module routes and nav
        foreach ($modules as $name => $config) {
            $cfg = $config->asArray();
            // Add nav
            if (isset($cfg['nav'])) {
                $nav->add($cfg['nav']);
            }

            // Add routes
            if (isset($cfg['routes'])) {
                $this->router->addControllers($cfg['routes']);
            }
        }

        // Load any user routes and initialize the ACL object
        $this->loadUserRoutes();
        $this->initAcl();

        // Set the auth method to trigger on 'dispatch.pre'
        $this->attachEvent('dispatch.pre', function($router) {
            $resource = $router->getControllerClass();
            $permission = $router->getAction();

            // Check for the resource and permission
            if ($resource != 'Phire\Controller\IndexController') {
                if (null === $router->project()->getService('acl')->getResource($resource)) {
                    $resource = null;
                    $permission = null;
                }

                // Get the user URI
                $uri = ($router->project()->getService('acl')->getType()->type == 'user') ?
                    APP_URI :
                    '/' . strtolower($router->project()->getService('acl')->getType()->type);

                // If not logged in for unsubscribe and required, redirect to the system login
                if (($_SERVER['REQUEST_URI'] == BASE_PATH . $uri . '/unsubscribe') &&
                    ($router->project()->getService('acl')->getType()->unsubscribe_login) &&
                    (!$router->project()->getService('acl')->isAuth($resource, $permission))) {
                    \Pop\Http\Response::redirect(BASE_PATH . $uri . '/login');
                    return \Pop\Event\Manager::KILL;
                // Else, if not logged in or allowed, redirect to the system login
                } else if (($_SERVER['REQUEST_URI'] != BASE_PATH . $uri . '/login') &&
                    ($_SERVER['REQUEST_URI'] != BASE_PATH . $uri . '/register') &&
                    ($_SERVER['REQUEST_URI'] != BASE_PATH . $uri . '/forgot') &&
                    ($_SERVER['REQUEST_URI'] != BASE_PATH . $uri . '/unsubscribe') &&
                    (strpos($_SERVER['REQUEST_URI'], BASE_PATH . $uri . '/verify') === false) &&
                    (!$router->project()->getService('acl')->isAuth($resource, $permission))) {
                    \Pop\Http\Response::redirect(BASE_PATH . $uri . '/login');
                    return \Pop\Event\Manager::KILL;
                // Else, if logged in and allowed, and a system access URI, redirect back to the system
                } else if ((($_SERVER['REQUEST_URI'] == BASE_PATH . $uri . '/login') ||
                    ($_SERVER['REQUEST_URI'] == BASE_PATH . $uri . '/register') ||
                    ($_SERVER['REQUEST_URI'] == BASE_PATH . $uri . '/forgot')) &&
                    ($router->project()->getService('acl')->isAuth($resource, $permission))) {
                    \Pop\Http\Response::redirect(BASE_PATH . $uri);
                    return \Pop\Event\Manager::KILL;
                }
            }
        });

        // If SSL is required for this user type, and not SSL,
        // redirect to SSL, else, just run
        if (($this->getService('acl')->getType()->force_ssl) && !($_SERVER['SERVER_PORT'] == '443')) {
            \Pop\Http\Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        } else {
            parent::run();
        }
    }

    /**
     * Load other user types' routes
     *
     * @return void
     */
    protected function loadUserRoutes()
    {
        // Get any other user types and declare their URI / Controller mapping
        $types = \Phire\Table\UserTypes::findAll();

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

    /**
     * Initialize the ACL object, checking for user types and user roles
     *
     * @return void
     */
    protected function initAcl()
    {
        // Get the user type from the URI
        $type = str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);

        // If the URI matches the system user URI
        if (substr($type, 0, strlen(APP_URI)) == APP_URI) {
            $type = 'user';
            // Else, set user type
        } else {
            $type = substr($type, 1);
            if (strpos($type, '/') !== false) {
                $type = substr($type, 0, strpos($type, '/'));
            }
        }

        // Create the type object and pass it to the Acl object
        $typeObj = \Phire\Table\UserTypes::findBy(array('type' => $type));
        $this->getService('acl')->setType($typeObj);

        // Set the roles for this user type in the Acl object
        $perms = \Phire\Table\UserRoles::getAllRoles($typeObj->id);
        if (count($perms['roles']) > 0) {
            foreach ($perms['roles'] as $role) {
                $this->getService('acl')->addRole($role);
            }
        }

        // Set up the ACL object's resources and permissions
        if (count($perms['resources']) > 0) {
            foreach ($perms['resources'] as $role => $perm) {
                if (count($perm) > 0) {
                    foreach ($perm as $resource => $p) {
                        $this->getService('acl')->addResource($resource);
                        $this->getService('acl')->allow($role, $resource, ((count($p) > 0) ? $p : null));
                    }
                } else {
                    $this->getService('acl')->allow($role);
                }
            }
        }
    }

    /**
     * Load install any assets for the module
     *
     * @param  string $d
     * @param  string $moduleName
     * @return void
     */
    protected function loadAssets($d, $moduleName)
    {
        if (null === $this->assets) {
            $this->assets = array(
                'js'  => PHP_EOL . '    <script type="text/javascript" src="' . BASE_PATH . CONTENT_PATH . '/assets/js/jax.min.js"></script>' . PHP_EOL,
                'css' => PHP_EOL
            );
        }

        $newModuleDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName);
        if (!file_exists($newModuleDir)) {
            mkdir($newModuleDir);
            chmod($newModuleDir, 0777);
            copy($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/index.html', $newModuleDir . '/index.html');
            chmod($newModuleDir . '/index.html', 0777);
        }

        $assetDirs = array('js', 'css', 'css/fonts', 'img');

        // Check and install asset files
        foreach ($assetDirs as $assetDir) {
            if (file_exists($d . '/assets/' . $assetDir)) {
                $newDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName) . '/' . $assetDir;
                if (!file_exists($newDir)) {
                    mkdir($newDir);
                    chmod($newDir, 0777);
                    copy($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/index.html', $newDir . '/index.html');
                    chmod($newDir . '/index.html', 0777);
                }
                $asDir = new Dir($d . '/assets/' . $assetDir, true, false, false);
                $asFiles = $asDir->getObjects();
                foreach ($asFiles as $as) {
                    if ($as->getExt() != 'html') {
                        // If asset file doesn't exist, or has been modified, copy it over
                        if (!file_exists($newDir . '/' . $as->getBasename()) ||
                            (filemtime($newDir . '/' . $as->getBasename()) < filemtime($as->getFullPath()))) {
                            $as->copy($newDir . '/' . $as->getBasename(), true);
                            $as->setPermissions(0777);
                        }
                        if ($assetDir == 'js') {
                            $this->assets['js'] .= '    <script type="text/javascript" src="' . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName) . '/js/' . $as->getBasename() . '"></script>' . PHP_EOL;
                        } else if ($assetDir == 'css') {
                            $this->assets['css'] .= '    <link type="text/css" rel="stylesheet" href="' . BASE_PATH . CONTENT_PATH . '/assets/' . strtolower($moduleName) . '/css/' . $as->getBasename() . '" />' . PHP_EOL;
                        }
                    }
                }
            }
        }
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
        $dir = new Dir($contentDir, true, true);
        $files = $dir->getFiles();
        $errorMsgs = array();

        // Check if the necessary directories are writable for Windows.
        if (stripos(PHP_OS, 'win') !== false) {
            if ((@touch($contentDir . '/writetest.txt')) == false) {
                $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $contentDir) . " is not writable.";
            } else {
                unlink($contentDir . '/writetest.txt');
                clearstatcache();
            }
            foreach ($files as $value) {
                if ((strpos($value, 'data') === false) && (strpos($value, 'ckeditor') === false) && (strpos($value, 'tinymce') === false) && (is_dir($value))) {
                    if ((@touch($value . '/writetest.txt')) == false) {
                        $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $value) . " is not writable.";
                    } else {
                        unlink($value . '/writetest.txt');
                        clearstatcache();
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
                if ((strpos($value, 'data') === false) && (strpos($value, 'ckeditor') === false) && (strpos($value, 'tinymce') === false) && (is_dir($value))) {
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

