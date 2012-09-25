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

class SystemController extends C
{
    
    /**
     * Session property
     */
    protected $sess = null;
    
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
            $request = new Request(null, BASE_URI . SYSTEM_URI);
        }
                
        if (($request->getRequestUri() == '/install') || (PhireProject::isInstalled())) {
            parent::__construct($request, $response, $project, $viewPath);
            $this->sess = Session::getInstance();
        }
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        if (!$this->isAuth()) {
            Response::redirect($this->request->getBasePath() . '/login');
        } else {
            $this->view = View::factory($this->viewPath . '/index.phtml');
            $this->send();
        }
    }

    /**
     * Login method
     *
     * @return void
     */
    public function login()
    {
        if ($this->isAuth()) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->view = View::factory($this->viewPath . '/login.phtml');
            $this->send();
        }
    }
    
    /**
     * Logout method
     *
     * @return void
     */
    public function logout()
    {
        if (isset($this->sess)) {
            $this->sess->kill();
            unset($this->sess);
        }
        Response::redirect($this->request->getBasePath() . '/login');
    }

    /**
     * Forgot method
     *
     * @return void
     */
    public function forgot()
    {
        $this->view = View::factory($this->viewPath . '/forgot.phtml');
        $this->send();
    }
    
    /**
     * Install method
     *
     * @return void
     */
    public function install()
    {
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            throw new \Exception('The system is already installed.');
        } else {
            if ((null != $this->request->getPath(1)) && ($this->request->getPath(1) == 'user')) {
                echo 'Install initial user.';
            } else {
                $config = new SysConfig();
                $form = new Install($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');
                if ($this->request->isPost()) {
                    $form->setFieldValues($this->request->getPost(), array('html', 'stripTags'));
                    if ($form->isValid()) {
                        $config->install($form);
                        $config->set('form', '    <p>We are good!</p>');
                        $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                        $this->send();
                    } else {
                        $config->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                        $this->send();
                    }
                } else {
                    $config->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                    $this->send();
                }
            }
        }
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

    /**
     * Auth method
     *
     * @param  mixed  $role
     * @return boolean
     */
    protected function isAuth($role = null)
    {
        return (isset($this->sess->user_id));
    }

}

