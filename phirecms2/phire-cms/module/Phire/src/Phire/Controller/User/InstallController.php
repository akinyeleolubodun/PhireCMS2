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
use Phire\Form\Install;
use Phire\Model;

class InstallController extends C
{

    /**
     * Session property
     * @var \Pop\Web\Session
     */
    protected $sess = null;

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
        // Create the session object and type property
        $this->sess = Session::getInstance();

        if (null === $viewPath) {
            $viewPath = __DIR__ . '/../../../../view/user/install';
        }

        if (null === $request) {
            $request = new Request(null, BASE_PATH . APP_URI . '/install');
        }

        if (\Phire\Project::isInstalled()) {
            parent::__construct($request, $response, $project, $viewPath);
        }
    }

    /**
     * Index method
     *
     * @throws \Exception
     * @return void
     */
    public function index()
    {
        if ((DB_INTERFACE != '') && (DB_NAME != '') && (!isset($this->sess->install))) {
            throw new \Exception('The system is already installed.');
        } else {
            $install = new Model\Install(array('title' => 'Install'));
            $form = new Install($this->request->getFullUri(), 'post');
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );
                if ($form->isValid()) {
                    $install->config($form);
                    echo 'Installed!';
                } else {
                    $install->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $install);
                    $this->send();
                }
            } else {
                $install->set('form', $form);
                $this->view = View::factory($this->viewPath . '/index.phtml', $install);
                $this->send();
            }
        }
    }

    /**
     * User method
     *
     * @return void
     */
    public function user()
    {
        $this->view = View::factory($this->viewPath . '/user.phtml', new Model(array('title' => 'Install User')));
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
