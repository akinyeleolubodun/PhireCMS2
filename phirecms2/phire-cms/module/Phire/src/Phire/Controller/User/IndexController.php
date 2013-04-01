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
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends C
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
     * Auth property
     * @var \Pop\Auth\Auth
     */
    protected $auth = null;

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
        if (\Phire\Project::isInstalled()) {
            // Get the user type from the URI
            $type = str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']);

            // If the URI matches the system user URI
            if (substr($type, 0, strlen(APP_URI)) == APP_URI) {
                $type = 'user';
            // Else, set user type
            } else {
                $type = substr($type, 1);
                if (strpos($type, '/') !== false) {
                    $type = substr($type, 0, strpos($type, '/'));
                }
            }

            if (null === $viewPath) {
                $viewPath = __DIR__ . '/../../../../view/' . $type;
            }

            if (null === $request) {
                $basePath = ($type != 'user') ? BASE_PATH . '/' . strtolower($type) : BASE_PATH . APP_URI;
                $request = new Request(null, $basePath);
            }

            // Create the session object and type property
            $this->sess = Session::getInstance();
            $this->type = Table\UserTypes::findBy(array('type' => $type));

            // If the user type requires SSL, redirect
            if (($this->type->force_ssl) && (!$request->isSecure())) {
                Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $request->getFullUri());
            } else {
                parent::__construct($request, $response, $project, $viewPath);
            }

            // Set the roles for this user type in the Acl object
            $perms = Table\UserRoles::getAllRoles($this->type->id);
            if (count($perms['roles']) > 0) {
                foreach ($perms['roles'] as $role) {
                    $this->project->getService('acl')->addRole($role);
                }
            }
            if (count($perms['resources']) > 0) {
                foreach ($perms['resources'] as $role => $perm) {
                    if (count($perm) > 0) {
                        foreach ($perm as $resource => $p) {
                            $this->project->getService('acl')->addResource($resource);
                            $this->project->getService('acl')->allow($role, $resource, ((count($p) > 0) ? $p : null));
                        }
                    } else {
                        $this->project->getService('acl')->allow($role);
                    }
                }
            }
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
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Home Page'
            ));
            $this->view = View::factory($this->viewPath . '/index.phtml', $user);
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
            // If user type is not found, 404
            if (!isset($this->type->id)) {
                $this->error();
            // Else, render the form
            } else {
                $user = new Model\User(array(
                    'acl'   => $this->project->getService('acl'),
                    'title' => 'Login'
                ));
                $user->set('unsubscribe', !($this->type->unsubscribe_login));
                $user->set('register', $this->type->registration);
                if (isset($this->sess->expired)) {
                    $user->set('error', 'Your session has expired.');
                } else if (isset($this->sess->authError)) {
                    $user->set('error', 'The user is not allowed in this area.');
                }
                $form = new Form\Login($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    $user->set('form', $form);

                    // If form is valid, authenticate the user
                    if ($form->isValid()) {
                        // Create Auth object and attempt to authenticate
                        $this->createAuth($form->username);
                        $this->auth->authenticate($form->username, $form->password);

                        // Get the auth result
                        $result = $this->getAuthResult($form->username);

                        // If error, record failed attempt and display error
                        if (null !== $result) {
                            $user->login($form->username, $this->type, false);
                            $user->set('error', $result);
                            $this->view = View::factory($this->viewPath . '/login.phtml', $user);
                            $this->send();
                        // Else, login
                        } else {
                            $user->login($form->username, $this->type);
                            $url = (isset($this->sess->lastUrl)) ? $this->sess->lastUrl : $this->request->getBasePath();
                            unset($this->sess->expired);
                            unset($this->sess->authError);
                            unset($this->sess->lastUrl);
                            Response::redirect($url);
                        }
                    // Else, re-render the form
                    } else {
                        $this->view = View::factory($this->viewPath . '/login.phtml', $user);
                        $this->send();
                    }
                // Else, render the form
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/login.phtml', $user);
                    $this->send();
                }
            }
        }
    }

    /**
     * Register method
     *
     * @return void
     */
    public function register()
    {
        // If registration is not allowed
        if (($this->isAuth()) || (!$this->type->registration)) {
            Response::redirect($this->request->getBasePath());
        // Else render the registration form
        } else {
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Register'
            ));
            $form = new Form\Profile(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post',
                null,
                '    ',
                $this->type
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
                    $user->save($form);
                    $user->set('form', '    <p>Thank you for registering.</p>');
                    $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                    $this->send();
                // Else, re-render the form with errors
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                    $this->send();
                }
            // Else, render the form
            } else {
                $user->set('form', $form);
                $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                $this->send();
            }
        }
    }

    /**
     * Profile method
     *
     * @return void
     */
    public function profile()
    {
        if (!$this->isAuth('profile', 'edit')) {
            Response::redirect($this->request->getBasePath() . '/login');
        } else {
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Profile'
            ));
            $user->getById($this->sess->user->id);

            // If user is found and valid
            if (null !== $user->id) {
                $form = new Form\Profile(
                    $this->request->getBasePath() . $this->request->getRequestUri(),
                    'post',
                    null,
                    '    ',
                    $this->type
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
                        $user->update($form);
                        Response::redirect($this->request->getBasePath());
                    // Else, re-render the form with errors
                    } else {
                        $user->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                        $this->send();
                    }
                // Else, render the form
                } else {
                    $userValues = $user->asArray();
                    unset($userValues['acl']);
                    $form->setFieldValues(
                        $userValues,
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                    $this->send();
                }
            }
        }
    }

    /**
     * Unsubscribe method
     *
     * @return void
     */
    public function unsubscribe()
    {
        // If login is required for unsubscribe
        if ((!$this->isAuth()) && ($this->type->unsubscribe_login)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Unsubscribe'
            ));
            $form = new Form\Unsubscribe($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

            // If form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                // If form is valid, unsubscribe the user
                if ($form->isValid()) {
                    $user->unsubscribe($form);
                    if ($this->isAuth()) {
                        $this->logout(false);
                    }
                    $user = new Model\User(array(
                        'title' => 'Unsubscribe',
                        'form'  => '    <p>Thank you. You have been unsubscribed from this website.</p>'
                    ));
                    $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                    $this->send();
                // Else, re-render the form with errors
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                    $this->send();
                }
            // Else, render the form
            } else {
                if ($this->isAuth()) {
                    $form->setFieldValues(array('email' => $this->sess->user->email));
                }
                $user->set('form', $form);
                $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
                $this->send();
            }
        }
    }

    /**
     * Forgot method
     *
     * @return void
     */
    public function forgot()
    {
        if ($this->isAuth()) {
            Response::redirect($this->request->getBasePath());
        } else {
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Forgot'
            ));
            $form = new Form\Forgot($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

            // If form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                // If form is valid, send reminder
                if ($form->isValid()) {
                    $user->sendReminder($form);
                    $user->set('form', '    <p>Thank you. A password reminder has been sent.</p>');
                    $this->view = View::factory($this->viewPath . '/forgot.phtml', $user);
                    $this->send();
                // Else, re-render the form with errors
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/forgot.phtml', $user);
                    $this->send();
                }
            // Else, render the form
            } else {
                if ($this->isAuth()) {
                    $form->setFieldValues(array('email' => $this->sess->user->email));
                }
                $user->set('form', $form);
                $this->view = View::factory($this->viewPath . '/forgot.phtml', $user);
                $this->send();
            }
        }
    }

    /**
     * Verify method
     *
     * @return void
     */
    public function verify()
    {
        // If the required user ID and hash is submitted
        if ((null !== $this->request->getPath(1)) && (null !== $this->request->getPath(2))) {
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Verify'
            ));
            $user->getById($this->request->getPath(1));

            // If the user was found, verify and save
            if (isset($user->id) && (sha1($user->email) == $this->request->getPath(2))) {
                $user->verify();
                $message = 'Thank you. Your email has been verified.';
            // Else, render failure message
            } else {
                $message = 'Sorry. That email could not be verified.';
            }

            $user->set('message', $message);
            $this->view = View::factory($this->viewPath . '/verify.phtml', $user);
            $this->send();
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
        // Destroy the session database entry
        if (null !== $this->sess->user->sess_id) {
            $session = Table\UserSessions::findById($this->sess->user->sess_id);
            if (isset($session->id)) {
                $session->delete();
            }
        }

        // Destroy the session object.
        unset($this->sess->user);

        if ($redirect) {
            Response::redirect($this->request->getBasePath());
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $viewPath = (strpos(get_called_class(), 'IndexController') === false) ?
            $this->viewPath . '/../error.phtml' : $this->viewPath . '/error.phtml';


        $user = new Model\User(array(
            'acl'   => $this->project->getService('acl'),
            'title' => '404 Error &gt; Page Not Found'
        ));
        $this->view = View::factory($viewPath, $user);
        $this->send(404);
    }

    /**
     * Create Auth object method
     *
     * @param  string $username
     * @return void
     */
    protected function createAuth($username)
    {
        // Create Auth object
        $this->auth = new Auth\Auth(
            new Auth\Adapter\Table('Phire\Table\Users', 'username', 'password', 'role_id'),
            (int)$this->type->password_encryption,
            $this->type->password_salt
        );

        // Set attempt limit
        $this->auth->setAttemptLimit((int)$this->type->allowed_attempts);

        // Set allowed IPs
        if (!empty($this->type->ip_allowed)) {
            $allowed = explode(',', $this->type->ip_allowed);
            $this->auth->setAllowedIps($allowed);
            $this->auth->setAllowedSubnets($allowed);
        }

        // Set blocked IPs
        if (!empty($this->type->ip_blocked)) {
            $blocked = explode(',', $this->type->ip_blocked);
            $this->auth->setBlockedIps($blocked);
            $this->auth->setBlockedSubnets($blocked);
        }

        // Set failed attempts
        $user = Table\Users::findBy(array('username' => $username));
        if (isset($user->id)) {
            $this->auth->setAttempts((int)$user->failed_attempts);
        }
    }

    /**
     * Get Auth result
     *
     * @return string
     */
    protected function getAuthResult()
    {
        $result = null;

        if (!$this->auth->isValid()) {
            $result = $this->auth->getResultMessage();
        } else {
            $user = $this->auth->getUser();
            $session = Table\UserSessions::findBy(array('user_id' => $user['id']));
            if ((!$this->type->multiple_sessions) && (isset($session->id))) {
                $result = 'Multiple sessions are not allowed. Someone is already logged on from ' . $session->ip . '.';
            } else if ((!$this->type->mobile_access) && (\Pop\Web\Mobile::isMobileDevice())) {
                $result = 'Mobile access is not allowed.';
            } else if (!$user['verified']) {
                $result = 'The user is not verified.';
            }
        }

        return $result;
    }

    /**
     * Auth method
     *
     * @param  string $resource
     * @param  string $permission
     * @return boolean
     */
    protected function isAuth($resource = null, $permission = null)
    {
        $auth = false;

        // If tracking sessions is on
        if (($this->type->track_sessions) && ((isset($this->sess->user->sess_id) && null !== $this->sess->user->sess_id))) {
            $session = Table\UserSessions::findById($this->sess->user->sess_id);
            if (!isset($session->id) || (($this->type->session_expiration != 0) && $session->hasExpired($this->type->session_expiration))) {
                $this->sess->lastUrl = $this->request->getBasePath() . $this->request->getRequestUri();
                $this->sess->expired = true;
                $this->logout();
            } else if (isset($this->sess->user->id)) {
                // If the user is not the right type, check for global access
                if ($this->type->id != $this->sess->user->type_id) {
                    if ($this->sess->user->global_access) {
                        $session->last = date('Y-m-d H:i:s');
                        $session->save();
                        $auth = true;
                    } else {
                        $this->sess->authError = true;
                        $this->logout();
                        $auth = false;
                    }
                // Else, authorize the user role
                } else if ($this->sess->user->role_id != 0) {
                    $role = Table\UserRoles::getRole($this->sess->user->role_id);
                    if ((null !== $resource) && (!$this->project->getService('acl')->hasResource($resource))) {
                        $this->project->getService('acl')->addResource($resource);
                    }
                    if ($this->project->getService('acl')->isAllowed($role, $resource, $permission)) {
                        $session->last = date('Y-m-d H:i:s');
                        $session->save();
                        $auth = true;
                    } else {
                        $auth = false;
                    }
                // Else, validate the session and record the action
                } else {
                    $session->last = date('Y-m-d H:i:s');
                    $session->save();
                    $auth = true;
                }
            }
        // Else, just check for a regular session
        } else if (isset($this->sess->user->id)) {
            // If the user is not the right type, check for global access
            if ($this->type->id != $this->sess->user->type_id) {
                $auth = ($this->sess->user->global) ? true : false;
            // Else, authorize the user role
            } else if ($this->sess->user->role_id != 0) {
                $role = Table\UserRoles::getRole($this->sess->user->role_id);
                if ((null !== $resource) && (!$this->project->getService('acl')->hasResource($resource))) {
                    $this->project->getService('acl')->addResource($resource);
                }
                $auth = $this->project->getService('acl')->isAllowed($role, $resource, $permission);
            } else {
                $auth = true;
            }
        }

        return $auth;
    }

}
