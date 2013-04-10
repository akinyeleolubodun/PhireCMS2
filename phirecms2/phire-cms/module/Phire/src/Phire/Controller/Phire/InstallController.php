<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire;

use Pop\Auth;
use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form\Install;
use Phire\Form\User;
use Phire\Model;
use Phire\Table;

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
            $viewPath = __DIR__ . '/../../../../view/phire/install';
        }

        if (\Phire\Project::isInstalled()) {
            parent::__construct($request, $response, $project, $viewPath);
        }
    }

    /**
     * Index method to install initial system config
     *
     * @throws \Exception
     * @return void
     */
    public function index()
    {
        // If the system is installed
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            Response::redirect(BASE_PATH . APP_URI);
        // Else, if the install process has begin but is not complete
        } else if (isset($this->sess->config) && isset($this->sess->app_uri) && (DB_INTERFACE == '') && (DB_NAME == '')) {
            Response::redirect(BASE_PATH . APP_URI . '/install/config');
        // Else, begin the install process
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
                    $url = ($install->configWritable) ?
                        BASE_PATH . $form->app_uri . '/install/user' :
                        BASE_PATH . APP_URI . '/install/config';
                    Response::redirect($url);
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
     * Manual install config method, if config file is not writable
     *
     * @return void
     */
    public function config()
    {
        // If the config was already written, redirect to the initial user screen
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/user');
        // Else, if the initial install screen isn't complete
        } else if (!isset($this->sess->config)) {
            Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install');
        // Else, display config to be copied and pasted
        } else {
            $config = new Model\Install(array(
                'title'  => 'Install Config',
                'config' => unserialize($this->sess->config),
                'uri'    => BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/user'
            ));
            $this->view = View::factory($this->viewPath . '/config.phtml', $config);
            $this->send();
        }
    }

    /**
     * Install initial user method
     *
     * @return void
     */
    public function user()
    {
        // If the system is installed
        if ((DB_INTERFACE != '') && (DB_NAME != '') && !isset($this->sess->config)) {
            Response::redirect(BASE_PATH . APP_URI);
        // Else, if the initial install screen or config isn't complete
        } else if ((DB_INTERFACE == '') && (DB_NAME == '')) {
            if (isset($this->sess->config)) {
                Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/config');
            } else {
                Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install');
            }
        // Else, install the first system user
        } else {
            $user = new Model\User(array('title' => 'Install User'));
            $form = new User($this->request->getFullUri(), 'post', null, null, 2001);
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );
                if ($form->isValid()) {
                    $user->save($form);

                    // Link first initial system user to the initial site
                    $site = new Table\SiteObjects(array(
                        'id'      => $user->id,
                        'site_id' => 6001,
                        'object'  => 'user'
                    ));
                    $site->save();

                    // Clear the session
                    $this->sess->kill();

                    $user->set('form', '    <p>Thank you. The system has successfully been installed. You can now log in <a href="' . BASE_PATH . APP_URI . '/login">here</a>.</p>');
                    $this->view = View::factory($this->viewPath . '/user.phtml', $user);
                    $this->send();
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/user.phtml', $user);
                    $this->send();
                }
            } else {
                $user->set('form', $form);
                $this->view = View::factory($this->viewPath . '/user.phtml', $user);
                $this->send();
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
        $this->view = View::factory($this->viewPath . '/error.phtml', new Model\Install(array('title' => '404 Error')));
        $this->send(404);
    }

}
