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
use Phire\Model\Content;

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
        if (\Phire\Project::isInstalled(true)) {
            parent::__construct($request, $response, $project, $viewPath);
        } else {
            Response::redirect(BASE_PATH . APP_URI . '/install');
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $content = new Content();
        $content->getByUri($this->request->getRequestUri());
        $this->view = View::factory($content->template, $content);
        $this->send($content->code);
    }

}

