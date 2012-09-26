<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\View;

class UserController extends AbstractController
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

    /**
     * Test method
     *
     * @return void
     */
    public function test()
    {
        echo 'User test<br />' . PHP_EOL;
    }

}

