<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Phire\Project as PhireProject,
    Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\Controller as C,
    Pop\Mvc\Model,
    Pop\Mvc\View,
    Pop\Project\Project,
    Pop\Web\Session;

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
     * @return void
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        // Determine controller-specific settings
        $this->calledClass = get_called_class();

        switch ($this->calledClass) {
            case 'Phire\Controller\IndexController':
                $view = null;
                $basePath = BASE_URI;
                break;

            default:
                $basePath = BASE_URI . SYSTEM_URI;

                if ($this->calledClass != 'Phire\Controller\Phire\IndexController') {
                    $uri = str_replace('Phire\\Controller\\Phire\\', '', $this->calledClass);
                    $uri = '/' . strtolower(str_replace('Controller', '', $uri));
                } else {
                    $uri = null;
                }

                $basePath = BASE_URI . SYSTEM_URI . $uri;
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

        if (PhireProject::isInstalled()) {
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
        $this->isError = true;
        $this->view = View::factory($this->viewPath . '/error.phtml');
        $this->send();
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

