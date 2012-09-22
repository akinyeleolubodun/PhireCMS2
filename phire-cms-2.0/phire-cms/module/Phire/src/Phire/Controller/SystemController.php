<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\Controller as C,
    Pop\Mvc\Model,
    Pop\Mvc\View,
    Pop\Project\Project;

class SystemController extends C
{

    /**
     * Constructer method to instantiate the controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return void
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        if (null === $viewPath) {
            $viewPath = __DIR__ . '/../../../view/system';
        }

        if (null === $request) {
            $request = new Request(null, BASE_URI . SYSTEM_URI);
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        if ($this->isInstalled()) {
            $this->view = View::factory($this->viewPath . '/index.phtml');
            $this->send();
        }
    }

    /**
     * Install method
     *
     * @return void
     */
    public function install()
    {
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            throw new \Exception('The system is already installed.');
        } else {
            $this->view = View::factory($this->viewPath . '/install.phtml');
            $this->send();
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        if ($this->isInstalled()) {
            $this->isError = true;
            $this->view = View::factory($this->viewPath . '/error.phtml');
            $this->send();
        }
    }

    /**
     * Method to check if the system is installed
     *
     * @throws Exception
     * @return boolean
     */
    public function isInstalled()
    {
        if ((DB_INTERFACE == '') || (DB_NAME == '')) {
            throw new \Exception('The config file is not properly configured. Please check the config file or install the system.');
            exit(0);
        }

        return true;
    }

}

