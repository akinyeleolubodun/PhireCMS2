<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire;

use Pop\Auth;
use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends AbstractController
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
            $viewPath = __DIR__ . '/../../../../view/phire';

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

            // If it is not a user, or a user globally logged into another area
            if (((strtolower($this->type->type) != 'user') && (!$this->type->global_access)) ||
                (substr($_SERVER['REQUEST_URI'], 0, strlen(BASE_PATH . APP_URI)) != BASE_PATH . APP_URI)) {
                $typePath = $viewPath . '/../' . strtolower($this->type->type);
                if (file_exists($typePath)) {
                    $viewPath = $typePath;
                } else {
                    $viewPath = __DIR__ . '/../../../../view/member';
                }
            }
        }

        // Set the correct base path and user URI based on user type
        if (get_called_class() == 'Phire\Controller\Phire\IndexController') {
            $basePath = (strtolower($this->type->type) != 'user') ? BASE_PATH . '/' . strtolower($this->type->type) : BASE_PATH . APP_URI;
            $request = new Request(null, $basePath);
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView($this->viewPath . '/index.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'title'    => 'Dashboard',
            'subTitle' => 'Welcome to Phire CMS 2.0'
        ));

        $content = new Model\Content();
        $config = new Model\Config();

        $this->view->set('recent', $content->getRecent())
                   ->set('themes', $content->getThemes())
                   ->set('modules', $content->getModules())
                   ->set('overview', $config->getOverview());

        $this->send();
    }

    /**
     * Login method
     *
     * @param  string $redirect
     * @return void
     */
    public function login($redirect = null)
    {
        // If user type is not found, 404
        if (!isset($this->type->id)) {
            $this->error();
        // If login is not allowed
        } else if (!$this->type->login) {
            Response::redirect(BASE_PATH . '/');
        // Else, render the form
        } else {
            $this->prepareView($this->viewPath . '/login.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
                'phire'    => new Model\Phire(),
                'title'    => 'Login'
            ));

            // Set up 'forgot,' 'register' and 'unsubscribe' links
            $uri = (strtolower($this->type->type) == 'user') ? APP_URI : '/' . strtolower($this->type->type);
            $forgot = '<a href="' . BASE_PATH . $uri . '/forgot">Forgot</a>';
            $forgot .= (($this->type->registration) ? ' | <a href="' . BASE_PATH . $uri . '/register">Register</a>' : null);
            $forgot .= (!($this->type->unsubscribe_login) ? ' | <a href="' . BASE_PATH . $uri . '/unsubscribe">Unsubscribe</a>' : null);
            $this->view->set('forgot', $forgot);

            if (isset($this->sess->expired)) {
                $this->view->set('error', 'Your session has expired.');
            } else if (isset($this->sess->authError)) {
                $this->view->set('error', 'The user is not allowed in this area.');
            }

            $form = new Form\Login($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

            // If form is submitted
            if ($this->request->isPost()) {
                $user = new Model\User();
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8')),
                    $this->project->getService('auth')->config($this->type, $this->request->getPost('username')),
                    $this->type, $user
                );

                $this->view->set('form', $form);

                // If form is valid, authenticate the user
                if ($form->isValid()) {
                    $user->login($form->username, $this->type);
                    if (isset($this->sess->lastUrl)) {
                        $url = $this->sess->lastUrl;
                    } else {
                        $url = (null !== $redirect) ? $redirect : $this->request->getBasePath();
                    }
                    unset($this->sess->expired);
                    unset($this->sess->authError);
                    unset($this->sess->lastUrl);

                    if ($url == '') {
                        $url = '/';
                    }
                    Response::redirect($url);
                // Else, re-render the form
                } else {
                    $this->send();
                }
            // Else, render the form
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        }
    }

    /**
     * Register method
     *
     * @param  string $redirect
     * @return void
     */
    public function register($redirect = null)
    {
        // If registration is not allowed
        if (!$this->type->registration) {
            Response::redirect($this->request->getBasePath());
        // Else render the registration form
        } else {
            $this->prepareView($this->viewPath . '/register.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
                'phire'    => new Model\Phire(),
                'title'    => 'Register'
            ));

            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post', $this->type->id, true, 0
            );

            // If form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                // If form is valid, save the user
                if ($form->isValid()) {
                    $user = new Model\User();
                    $user->save($form, $this->project->module('Phire'));
                    if (null !== $redirect) {
                        Response::redirect($redirect);
                    } else {
                        $this->view->set('form', '        <h4>Thank you for registering.</h4>')
                                   ->set('typeUri', ((strtolower($this->type->type) != 'user') ? '/' . strtolower($this->type->type) : APP_URI));
                        if ($this->type->verification) {
                            $this->view->set('verify', true);
                        }
                        if ($this->type->approval) {
                            $this->view->set('approval', true);
                        }
                        $this->send();
                    }
                // Else, re-render the form with errors
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, render the form
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        }
    }

    /**
     * Profile method
     *
     * @param  string $redirect
     * @return void
     */
    public function profile($redirect = null)
    {
        $this->prepareView($this->viewPath . '/profile.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire(),
            'title'    => 'Profile'
        ));

        $user = new Model\User();
        $user->getById($this->sess->user->id);

        // If user is found and valid
        if (null !== $user->id) {
            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post', $this->type->id, true, $user->id
            );

            // If the form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                // If the form is valid
                if ($form->isValid()) {
                    $user->update($form, $this->project->module('Phire'));
                    $url = (null !== $redirect) ? $redirect : $this->request->getBasePath();
                    if ($url == '') {
                        $url = '/';
                    }
                    Response::redirect($url);
                // Else, re-render the form with errors
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, render the form
            } else {
                $form->setFieldValues(
                    $user->getData(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );
                $this->view->set('form', $form);
                $this->send();
            }
        }
    }

    /**
     * Unsubscribe method
     *
     * @param  string $redirect
     * @return void
     */
    public function unsubscribe($redirect = null)
    {
        $this->prepareView($this->viewPath . '/unsubscribe.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire(),
            'title'    => 'Unsubscribe'
        ));

        $form = new Form\Unsubscribe($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

        // If form is submitted
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('strip_tags', 'htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            // If form is valid, unsubscribe the user
            if ($form->isValid()) {
                $user = new Model\User();
                $user->unsubscribe($form);
                if ($this->project->getService('acl')->isAuth()) {
                    $this->logout(false);
                }
                if (null !== $redirect) {
                    Response::redirect($redirect);
                } else {
                    $this->prepareView($this->viewPath . '/unsubscribe.phtml', array(
                        'assets'   => $this->project->getAssets(),
                        'title'    => 'Unsubscribe'
                    ));
                    $this->view->set('form', '    <p>Thank you. You have been unsubscribed from this website.</p>');
                    $this->send();
                }
            // Else, re-render the form with errors
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        // Else, render the form
        } else {
            if ($this->project->getService('acl')->isAuth()) {
                $form->setFieldValues(array('email' => $this->sess->user->email));
            }
            $this->view->set('form', $form);
            $this->send();
        }
    }

    /**
     * Forgot method
     *
     * @param  string $redirect
     * @return void
     */
    public function forgot($redirect = null)
    {
        $this->prepareView($this->viewPath . '/forgot.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire(),
            'title'    => 'Forgot'
        ));

        $form = new Form\Forgot($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

        // If form is submitted
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('strip_tags', 'htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            // If form is valid, send reminder
            if ($form->isValid()) {
                $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
                $user = new Model\User();
                $user->sendReminder($form->email, $this->project->module('Phire'));
                if (null !== $redirect) {
                    Response::redirect($redirect);
                } else {
                    $this->view->set('form', '    <p>Thank you. A password reminder has been sent.</p>');
                    $this->send();
                }
            // Else, re-render the form with errors
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        // Else, render the form
        } else {
            if ($this->project->getService('acl')->isAuth()) {
                $form->setFieldValues(array('email' => $this->sess->user->email));
            }
            $this->view->set('form', $form);
            $this->send();
        }
    }

    /**
     * Verify method
     *
     * @param  string $redirect
     * @return void
     */
    public function verify($redirect = null)
    {
        // If the required user ID and hash is submitted
        if ((null !== $this->request->getPath(1)) && (null !== $this->request->getPath(2))) {
            $this->prepareView($this->viewPath . '/verify.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
                'phire'    => new Model\Phire(),
                'title'    => 'Verify'
            ));

            $user = new Model\User();
            $user->getById($this->request->getPath(1));

            // If the user was found, verify and save
            if (isset($user->id) && (sha1($user->email) == $this->request->getPath(2))) {
                $user->verify();
                $message = 'Thank you. Your email has been verified.';
            // Else, render failure message
            } else {
                $message = 'Sorry. That email could not be verified.';
            }
            if (null !== $redirect) {
                Response::redirect($redirect);
            } else {
                $this->view->set('message', $message);
                $this->send();
            }
        // Else, redirect
        } else {
            Response::redirect($this->request->getBasePath());
        }
    }

    /**
     * Logout method
     *
     * @param  boolean $redirect
     * @return void
     */
    public function logout($redirect = true)
    {
        $this->project->getService('acl')->logout($redirect);
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $this->prepareView($this->viewPath . '/error.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire(),
        ));

        $this->view->set('title', '404 Error ' . $this->view->separator . ' Page Not Found');
        $this->send(404);
    }

}
