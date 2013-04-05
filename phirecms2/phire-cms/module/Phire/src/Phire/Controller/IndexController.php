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

class IndexController extends C
{

    /**
     * Constructor method to instantiate the default controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return self
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        if (null === $viewPath) {
            $viewPath = __DIR__ . '/../../../view/';
        }

        if (null === $request) {
            $request = new Request(null, BASE_PATH);
        }

        if (\Phire\Project::isInstalled(true)) {
            parent::__construct($request, $response, $project, $viewPath);
        } else {
            Response::redirect(BASE_PATH . APP_URI . '/install');
        }
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->view = View::factory($this->viewPath . '/index.phtml', new Model(array('title' => 'Home')));
        $this->send();
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $this->view = View::factory($this->viewPath . '/error.phtml', new Model(array('title' => '404 Error')));
        $this->send(404);
    }

}

