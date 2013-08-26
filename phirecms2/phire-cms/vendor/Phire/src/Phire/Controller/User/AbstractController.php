<?php
/**
 * @namespace
 */
namespace Phire\Controller\User;

use Pop\Auth;
use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Model;
use Phire\Table;

abstract class AbstractController extends C
{

    /**
     * Session property
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Types property
     * @var \Phire\Table\UserTypes
     */
    protected $type = null;

    /**
     * Constructor method to instantiate the user controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return self
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        // Create the session object and get the user type
        $this->sess = Session::getInstance();
        $this->type = $project->getService('acl')->getType();

        if (null === $viewPath) {
            $cfg = $project->module('Phire')->asArray();
            $viewPath = __DIR__ . '/../../../../view/' . $this->type->type;

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'];
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'];
                }
            }
        }

        // Set the correct base path and user URI based on user type
        if (get_called_class() == 'Phire\Controller\User\IndexController') {
            $basePath = ($this->type->type != 'user') ? BASE_PATH . '/' . strtolower($this->type->type) : BASE_PATH . APP_URI;
            $request = new Request(null, $basePath);
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $user = new Model\User(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => '404 Error &gt; Page Not Found'
        ));

        $this->view = View::factory($this->viewPath . '/error.phtml', $user);
        $this->send(404);
    }

}
