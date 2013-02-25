<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\Model;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;

abstract class AbstractController extends C
{

    /**
     * Session property
     */
    protected $sess = null;

    /**
     * Called controller class
     */
    protected $calledClass = null;

    /**
     * Constructor method to instantiate the controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return \Phire\Controller\AbstractController
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        // Determine controller-specific settings
        $this->calledClass = get_called_class();

        switch ($this->calledClass) {
            case 'Phire\Controller\IndexController':
                $view = null;
                $basePath = BASE_PATH;
                break;

            default:
                if ($this->calledClass != 'Phire\Controller\Phire\IndexController') {
                    $uri = str_replace('Phire\Controller\Phire\\', '', $this->calledClass);
                    $uri = '/' . strtolower(str_replace('Controller', '', $uri));
                } else {
                    $uri = null;
                }

                $basePath = BASE_PATH . APP_URI . $uri;
                $view = '/phire' . $uri;
        }

        // Set the view path
        if (null === $viewPath) {
            $viewPath = __DIR__ . '/../../../view' . $view;
        }

        // Create a request
        if (null === $request) {
            $request = new Request(null, $basePath);
        }

        if (\Phire\Project::isInstalled()) {
            parent::__construct($request, $response, $project, $viewPath);
            $this->sess = Session::getInstance();
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $this->view = View::factory($this->viewPath . '/error.phtml');
        $this->send(404);
    }

    /**
     * Auth method
     *
     * @param  string $id
     * @param  mixed  $role
     * @return boolean
     */
    protected function isAuth($id, $role = null)
    {
        return (isset($this->sess->$id));
    }

}

