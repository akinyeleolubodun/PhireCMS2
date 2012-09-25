<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Phire\Project as PhireProject,
    Phire\Form\Install,
    Phire\Model\SysConfig,
    Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\Controller as C,
    Pop\Mvc\Model,
    Pop\Mvc\View,
    Pop\Project\Project,
    Pop\Web\Session,
    Pop\Version;

class UserController extends C
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
            $request = new Request(null, BASE_URI . SYSTEM_URI . '/user');
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
        echo 'User index' . PHP_EOL;
    }

    /**
     * Test method
     *
     * @return void
     */
    public function test()
    {
        echo 'User test<br />' . PHP_EOL;
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

}

