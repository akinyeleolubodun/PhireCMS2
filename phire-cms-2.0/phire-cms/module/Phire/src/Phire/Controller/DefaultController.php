<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\View;

class DefaultController extends AbstractController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->view = View::factory($this->viewPath . '/index.phtml');
        $this->send();
    }

}

