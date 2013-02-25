<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Mvc\View;

class IndexController extends AbstractController
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

