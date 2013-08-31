<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\User;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class SessionsController extends C
{

    /**
     * Constructor method to instantiate the categories controller object
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
            $cfg = $project->module('Phire')->asArray();
            $viewPath = __DIR__ . '/../../../../../view/phire/user';

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

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Sessions index method
     *
     * @return void
     */
    public function index()
    {
        $session = new Model\UserSession(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'User Sessions'
        ));

        $session->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/sessions.phtml', $session);
        $this->send();
    }

    /**
     * Session remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the sessions
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_sessions'])) {
                foreach ($post['remove_sessions'] as $id) {
                    $session = Table\UserSessions::findById($id);
                    if (isset($session->id)) {
                        $session->delete();
                    }
                }
            }
        }

        Response::redirect($this->request->getBasePath());
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $session = new Model\UserSession(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav')
        ));

        $session->set('title', '404 Error ' . $session->config()->separator . ' Page Not Found');
        $this->view = View::factory($this->viewPath . '/error.phtml', $session);
        $this->send(404);
    }

}

