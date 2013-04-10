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

class UsersController extends IndexController
{

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
        if (null === $viewPath) {
            $viewPath = __DIR__ . '/../../../../view/user/users';
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Users index method
     *
     * @return void
     */
    public function index()
    {
        if (!$this->isAuth('users', 'read')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $user = new Model\User(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'Users'
            ));
            $user->getAll();
            $this->view = View::factory($this->viewPath . '/index.phtml', $user);
            $this->send();
        }
    }

    /**
     * User add method
     *
     * @return void
     */
    public function add()
    {
        if (!$this->isAuth('users', 'add')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            // Select user type
            if (null === $this->request->getPath(1)) {
                $user = new Model\User(array(
                    'acl'   => $this->project->getService('acl'),
                    'title' => 'User &gt; Add'
                ));
                $form = new Form\User($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, redirect to the second part of the form
                    if ($form->isValid()) {
                        Response::redirect($this->request->getBasePath() . $this->request->getRequestUri() . '/' . $form->type_id);
                    // Else, re-render the form with errors
                    } else {
                        $user->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                        $this->send();
                    }
                // Else, render the form
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                    $this->send();
                }
            // Else, add user
            } else {
                $type = Table\UserTypes::findById($this->request->getPath(1));

                // If user type is valid
                if (isset($type->id)) {
                    $user = new Model\User(array(
                        'acl'   => $this->project->getService('acl'),
                        'title' => 'User &gt; Add ' . $type->type
                    ));
                    $form = new Form\User($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ', $type->id);

                    // If form is submitted
                    if ($this->request->isPost()) {
                        $form->setFieldValues(
                            $this->request->getPost(),
                            array('strip_tags', 'htmlentities'),
                            array(null, array(ENT_QUOTES, 'UTF-8'))
                        );

                        // If form is valid, save new user
                        if ($form->isValid()) {
                            $user->save($form);
                            Response::redirect(BASE_PATH . APP_URI . '/users');
                        // Else, re-render form with errors
                        } else {
                            $user->set('form', $form);
                            $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                            $this->send();
                        }
                    // Else, render form
                    } else {
                        $user->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                        $this->send();
                    }
                // Else, redirect
                } else {
                    Response::redirect($this->request->getBasePath() . '/add');
                }
            }
        }
    }

    /**
     * User edit method
     *
     * @return void
     */
    public function edit()
    {
        if (!$this->isAuth('users', 'edit')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $user = new Model\User(array(
                'acl' => $this->project->getService('acl')
            ));
            $user->getById($this->request->getPath(1));

            // If user is found and valid
            if (null !== $user->id) {
                $user->set('title', 'User &gt; Edit &gt; ' . $user->username);
                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(),
                    'post',
                    null,
                    '    ',
                    $user->type_id
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
                        $user->update($form);
                        Response::redirect(BASE_PATH . APP_URI . '/users');
                    // Else, re-render form with errors
                    } else {
                        $user->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $user);
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
                    $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                    $this->send();
                }
            // Else redirect
            } else {
                Response::redirect(BASE_PATH . APP_URI . '/users');
            }
        }
    }

    /**
     * User edit type method
     *
     * @return void
     */
    public function type()
    {
        if (!$this->isAuth('users', 'edit')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $user = new Model\User(array(
                'acl' => $this->project->getService('acl')
            ));
            $user->getById($this->request->getPath(1));

            // If user is found and valid
            if (null !== $user->id) {
                $user->set('title', 'User &gt; Type &gt; ' . $user->username);
                $form = new Form\User($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

                // If the form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(array('type_id' => $this->request->getPost('type_id')));

                    // If the form is valid, save user type
                    if ($form->isValid()) {
                        $user->updateType($form);
                        Response::redirect(BASE_PATH . APP_URI . '/users');
                    // Else, re-render the form with errors
                    } else {
                        $user->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                        $this->send();
                    }
                // Else, render the form
                } else {
                    $form->setFieldValues(array('type_id' => $user->type_id));
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $user);
                    $this->send();
                }
            // Else redirect
            } else {
                Response::redirect(BASE_PATH . APP_URI . '/users');
            }
        }
    }

    /**
     * User remove method
     *
     * @return void
     */
    public function remove()
    {
        if (!$this->isAuth('users', 'remove')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            // Loop through the users to delete
            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                if (isset($post['remove_users'])) {
                    foreach ($post['remove_users'] as $id) {
                        $user = Table\Users::findById($id);
                        if (isset($user->id)) {
                            $user->delete();
                        }
                    }
                }
            }

            Response::redirect($this->request->getBasePath());
        }
    }

}

