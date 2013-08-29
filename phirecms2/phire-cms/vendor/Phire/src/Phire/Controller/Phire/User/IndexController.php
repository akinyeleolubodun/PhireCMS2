<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\User;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends C
{

    /**
     * Constructor method to instantiate the categories controller object
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
            $viewPath = __DIR__ . '/../../../../../view/phire/user';

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
    }

    /**
     * Users index method
     *
     * @return void
     */
    public function index()
    {
        $user = new Model\User(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Users'
        ));

        // If type id is set, get users by type
        if ((null !== $this->request->getPath(1)) && is_numeric($this->request->getPath(1))) {
            $user->getAll($this->request->getPath(1), $this->request->getQuery('sort'), $this->request->getQuery('page'));
            $user->set('typeId', $this->request->getPath(1));
        // Else, list user types to choose from
        } else {
            $user->getUserTypes();
            $user->set('typeId', null);
        }

        $this->view = View::factory($this->viewPath . '/index.phtml', $user);
        $this->send();
    }

    /**
     * User add method
     *
     * @return void
     */
    public function add()
    {
        // Select user type
        if (null === $this->request->getPath(1)) {
            $user = new Model\User(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav'),
                'title'  => 'User &gt; Add'
            ));

            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                '0', false, 0, $this->project->isLoaded('Fields')
            );

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
                    'assets' => $this->project->getAssets(),
                    'acl'    => $this->project->getService('acl'),
                    'nav'    => $this->project->getService('nav'),
                    'title'  => 'User &gt; Add ' . $type->type
                ));

                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $type->id, false, 0, $this->project->isLoaded('Fields')
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save new user
                    if ($form->isValid()) {
                        $user->save($form, $this->project->isLoaded('Fields'));
                        Response::redirect(BASE_PATH . APP_URI . '/users/index/' . $this->request->getPath(1));
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

    /**
     * User edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $user = new Model\User(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));
            $user->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If user is found and valid
            if (null !== $user->id) {
                $user->set('title', 'User &gt; Edit &gt; ' . $user->type_name . $user->username);
                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $user->type_id, false, $user->id, $this->project->isLoaded('Fields')
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
                        $user->update($form, $this->project->isLoaded('Fields'));
                        Response::redirect(BASE_PATH . APP_URI . '/users/index/' . $form->type_id);
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
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $user = new Model\User(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));
            $user->getById($this->request->getPath(1));

            // If user is found and valid
            if (null !== $user->id) {
                $user->set('title', 'User &gt; Type &gt; ' . $user->username);
                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    0, false, 0, $this->project->isLoaded('Fields')
                );

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
     * User logins method
     *
     * @return void
     */
    public function logins()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            if ($this->request->isPost()) {
                $user = Table\Users::findById($this->request->getPath(1));
                if (isset($user->id)) {
                    $user->logins = null;
                    $user->update();
                }
                Response::redirect(BASE_PATH . APP_URI . '/users');
            } else {
                $user = new Model\User(array(
                    'assets' => $this->project->getAssets(),
                    'acl'    => $this->project->getService('acl'),
                    'nav'    => $this->project->getService('nav')
                ));

                $user->getLoginsById($this->request->getPath(1), $this->project->isLoaded('Fields'));
                $this->view = View::factory($this->viewPath . '/logins.phtml', $user);
                $this->send();
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
        $typeId = null;

        // Loop through the users to delete
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_users'])) {
                foreach ($post['remove_users'] as $id) {
                    $user = Table\Users::findById($id);
                    if (isset($user->id)) {
                        $typeId = '/index/' . $user->type_id;
                        $user->delete();
                    }

                    // If the Fields module is installed, and if there are fields for this form/model
                    if ($this->project->isLoaded('Fields')) {
                        $fields = new \Fields\Table\FieldValues();
                        $fields->delete(array('model_id' => $id));
                    }
                }
            }
        }

        Response::redirect($this->request->getBasePath() . $typeId);
    }

}
