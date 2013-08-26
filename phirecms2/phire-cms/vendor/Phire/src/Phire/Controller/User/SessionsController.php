<?php
/**
 * @namespace
 */
namespace Phire\Controller\User;

use Pop\Http\Response;
use Pop\Mvc\View;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class SessionsController extends AbstractController
{

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

}

