<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Install;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;

class IndexController extends C
{

    /**
     * Session object
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Constructor method to instantiate the default controller object
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
            $viewPath = __DIR__ . '/../../../../../view/phire/install';

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
        $this->sess = Session::getInstance();
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            Response::redirect(BASE_PATH . APP_URI);
        } else {
            $install = new Model\Install(array(
                'title' => 'Phire CMS 2.0 Installation'
            ));

            $form = new Form\Install($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

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
     * Config method
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
            $install = new Model\Install(array(
                'title'  => 'Phire CMS 2.0 Configuration',
                'config' => unserialize($this->sess->config),
                'uri'    => BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/user'
            ));
            $this->view = View::factory($this->viewPath . '/config.phtml', $install);
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
            $user = new Model\User(array(
                'title' => 'Phire CMS 2.0 Initial User Setup'
            ));
            $form = new Form\User($this->request->getBasePath() . $this->request->getRequestUri(), 'post', 2001, true);
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                if ($form->isValid()) {
                    $user->save($form);
                    $user->set('form', '    <p>Thank you. The system has successfully been installed. You can now log in <a href="' . BASE_PATH . APP_URI . '/login">here</a>.</p>');
                    Model\Install::send($form);
                    unset($this->sess->config);
                    unset($this->sess->app_uri);
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
     * CSS method
     *
     * @return void
     */
    public function css()
    {
        $css = file_get_contents(__DIR__ . '/../../../../../data/assets/css/phire.css');
        $response = new Response();
        $response->setHeader('Content-Type', 'text/css')
                 ->setBody($css);
        $response->send();
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $install = new Model\Install(array(
            'title' => '404 Error &gt; Page Not Found'
        ));

        $this->view = View::factory($this->viewPath . '/error.phtml', $install);
        $this->send(404);
    }

}

