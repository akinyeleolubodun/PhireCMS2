<?php
/**
 * @namespace
 */
namespace Phire\Controller\User;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class SessionsController extends IndexController
{

    /**
     * Constructor method to instantiate the sessions controller object
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
            $viewPath = __DIR__ . '/../../../../view/user/sessions';
        }

        if (null === $request) {
            $request = new Request(null, BASE_PATH . APP_URI . '/sessions');
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
        if (!$this->isAuth('sessions', 'read')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $session = new Model\Session(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'User Sessions'
            ));
            $session->getAll();
            $this->view = View::factory($this->viewPath . '/index.phtml', $session);
            $this->send();
        }
    }

    /**
     * Session remove method
     *
     * @return void
     */
    public function remove()
    {
        if (!$this->isAuth('sessions', 'remove')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            // Loop through and delete the sessions
            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                if (isset($post['remove_sessions'])) {
                    foreach ($post['remove_sessions'] as $id) {
                        $session = Table\Sessions::findById($id);
                        if (isset($session->id)) {
                            $session->delete();
                        }
                    }
                }
            }

            Response::redirect($this->request->getBasePath());
        }
    }

}

