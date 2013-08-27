<?php
/**
 * @namespace
 */
namespace Phire\Controller\User;

use Pop\Auth;
use Pop\Http\Response;
use Pop\Mvc\View;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends AbstractController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $user = new Model\User(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Home'
        ));

        $this->view = View::factory($this->viewPath . '/index.phtml', $user);
        $this->send();
    }

    /**
     * Login method
     *
     * @return void
     */
    public function login()
    {
        // If user type is not found, 404
        if (!isset($this->type->id)) {
            $this->error();
        // Else, render the form
        } else {
            $user = new Model\User(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav'),
                'title'  => 'Login'
            ));

            // Set up 'forgot,' 'register' and 'unsubscribe' links
            $uri = ($this->type->type == 'user') ? APP_URI : '/' . $this->type->type;
            $forgot = '<a href="' . BASE_PATH . $uri . '/forgot">Forgot</a>';
            $forgot .= (($this->type->registration) ? ' | <a href="' . BASE_PATH . $uri . '/register">Register</a>' : null);
            $forgot .= (!($this->type->unsubscribe_login) ? ' | <a href="' . BASE_PATH . $uri . '/unsubscribe">Unsubscribe</a>' : null);
            $user->set('forgot', $forgot);

            if (isset($this->sess->expired)) {
                $user->set('error', 'Your session has expired.');
            } else if (isset($this->sess->authError)) {
                $user->set('error', 'The user is not allowed in this area.');
            }

            $form = new Form\Login($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

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
                    $auth = $this->project->getService('auth')->config($this->type, $form->username);
                    $auth->authenticate($form->username, $form->password);

                    // Get the auth result
                    $result = $auth->getAuthResult($this->type, $form->username);

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

    /**
     * Register method
     *
     * @return void
     */
    public function register()
    {
        // If registration is not allowed
        if (!$this->type->registration) {
            Response::redirect($this->request->getBasePath());
        // Else render the registration form
        } else {
            $user = new Model\User(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav'),
                'title'  => 'Register'
            ));
            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post', $this->type->id, true, 0, $this->project->isLoaded('Fields')
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
        $user = new Model\User(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Profile'
        ));
        $user->getById($this->sess->user->id);

        // If user is found and valid
        if (null !== $user->id) {
            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post', $this->type->id, true, $user->id, $this->project->isLoaded('Fields')
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

    /**
     * Unsubscribe method
     *
     * @return void
     */
    public function unsubscribe()
    {
        $user = new Model\User(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Unsubscribe'
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
                $user->unsubscribe($form);
                if ($this->project->getService('acl')->isAuth()) {
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
            if ($this->project->getService('acl')->isAuth()) {
                $form->setFieldValues(array('email' => $this->sess->user->email));
            }
            $user->set('form', $form);
            $this->view = View::factory($this->viewPath . '/profile.phtml', $user);
            $this->send();
        }
    }

    /**
     * Forgot method
     *
     * @return void
     */
    public function forgot()
    {
        $user = new Model\User(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Forgot'
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
            if ($this->project->getService('acl')->isAuth()) {
                $form->setFieldValues(array('email' => $this->sess->user->email));
            }
            $user->set('form', $form);
            $this->view = View::factory($this->viewPath . '/forgot.phtml', $user);
            $this->send();
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
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav'),
                'title'  => 'Verify'
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
        $this->project->getService('acl')->logout($redirect);
    }

}
