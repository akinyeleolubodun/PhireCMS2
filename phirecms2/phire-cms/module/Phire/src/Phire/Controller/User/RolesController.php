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

class RolesController extends IndexController
{

    /**
     * Constructor method to instantiate the roles controller object
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
            $viewPath = __DIR__ . '/../../../../view/user/roles';
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Role index method
     *
     * @return void
     */
    public function index()
    {
        if (!$this->isAuth('roles', 'read')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $role = new Model\Role(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'User Roles'
            ));
            $role->getAll();
            $this->view = View::factory($this->viewPath . '/index.phtml', $role);
            $this->send();
        }
    }

    /**
     * Role add method
     *
     * @return void
     */
    public function add()
    {
        if (!$this->isAuth('roles', 'add')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $role = new Model\Role(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'User Roles &gt; Add'
            ));
            $form = new Form\Role($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

            // If form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                // If form is valid, save new role
                if ($form->isValid()) {
                    $role->save($form);
                    Response::redirect(BASE_PATH . APP_URI . '/roles');
                // Else, re-render the form with errors
                } else {
                    $role->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $role);
                    $this->send();
                }
            // Else, render the form
            } else {
                $role->set('form', $form);
                $this->view = View::factory($this->viewPath . '/index.phtml', $role);
                $this->send();
            }
        }
    }

    /**
     * Role edit method
     *
     * @return void
     */
    public function edit()
    {
        if (!$this->isAuth('roles', 'edit')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $role = new Model\Role(array(
                'acl' => $this->project->getService('acl')
            ));
            $role->getById($this->request->getPath(1));

            // If role is found and valid
            if (null !== $role->name) {
                $role->set('title', 'User Roles &gt; ' . $role->name);
                $form = new Form\Role($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ', $role->id);

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save role
                    if ($form->isValid()) {
                        $role->update($form);
                        Response::redirect(BASE_PATH . APP_URI . '/roles');
                    // Else, re-render the form with errors
                    } else {
                        $role->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $role);
                        $this->send();
                    }
                // Else, render form
                } else {
                    $roleValues = $role->asArray();
                    unset($roleValues['acl']);
                    $form->setFieldValues(
                        $roleValues,
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $role->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $role);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect(BASE_PATH . APP_URI . '/roles');
            }
        }
    }

    /**
     * Role remove method
     *
     * @return void
     */
    public function remove()
    {
        if (!$this->isAuth('roles', 'remove')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            // Loop through and delete the roles
            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                if (isset($post['remove_roles'])) {
                    foreach ($post['remove_roles'] as $id) {
                        $role = Table\UserRoles::findById($id);
                        if (isset($role->id)) {
                            $role->delete();
                        }
                    }
                }
            }

            Response::redirect($this->request->getBasePath());
        }
    }

}

