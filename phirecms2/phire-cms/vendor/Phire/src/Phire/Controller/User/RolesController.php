<?php
/**
 * @namespace
 */
namespace Phire\Controller\User;

use Pop\Http\Response;
use Pop\Mvc\View;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class RolesController extends AbstractController
{

    /**
     * Role index method
     *
     * @return void
     */
    public function index()
    {
        $role = new Model\UserRole(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'User Roles'
        ));

        $role->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/roles.phtml', $role);
        $this->send();
    }

    /**
     * Role add method
     *
     * @return void
     */
    public function add()
    {
        $role = new Model\UserRole(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'User Roles &gt; Add'
        ));

        $form = new Form\UserRole(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
            0, $this->project->module('Phire'), $this->project->isLoaded('Fields')
        );

        // If form is submitted
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('strip_tags', 'htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            // If form is valid, save new role
            if ($form->isValid()) {
                $role->save($form, $this->project->isLoaded('Fields'));
                Response::redirect(BASE_PATH . APP_URI . '/users/roles');
            // Else, re-render the form with errors
            } else {
                $role->set('form', $form);
                $this->view = View::factory($this->viewPath . '/roles.phtml', $role);
                $this->send();
            }
        // Else, render the form
        } else {
            $role->set('form', $form);
            $this->view = View::factory($this->viewPath . '/roles.phtml', $role);
            $this->send();
        }
    }

    /**
     * Role edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $role = new Model\UserRole(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));
            $role->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If role is found and valid
            if (null !== $role->name) {
                $role->set('title', 'User Roles &gt; ' . $role->name);
                $form = new Form\UserRole(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $role->id, $this->project->module('Phire'), $this->project->isLoaded('Fields')
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save role
                    if ($form->isValid()) {
                        $role->update($form, $this->project->isLoaded('Fields'));
                        Response::redirect(BASE_PATH . APP_URI . '/users/roles');
                    // Else, re-render the form with errors
                    } else {
                        $role->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/roles.phtml', $role);
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
                    $this->view = View::factory($this->viewPath . '/roles.phtml', $role);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect(BASE_PATH . APP_URI . '/users/roles');
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
        // Loop through and delete the roles
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_roles'])) {
                foreach ($post['remove_roles'] as $id) {
                    $role = Table\UserRoles::findById($id);
                    if (isset($role->id)) {
                        $role->delete();
                    }

                    $sql = Table\UserTypes::getSql();

                    if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
                        $sql->update(array(
                            'default_role_id' => null
                        ))->where()->equalTo('default_role_id', $role->id);
                        Table\UserTypes::execute($sql->render(true));
                    }

                    // If the Fields module is installed, and if there are fields for this form/model
                    if ($this->project->isLoaded('Fields')) {
                        $fields = new \Fields\Table\FieldValues();
                        $fields->delete(array('model_id' => $id));
                    }
                }
            }
        }

        Response::redirect($this->request->getBasePath());
    }

    /**
     * Method to get other resource permissions via JS
     *
     * @return void
     */
    public function json()
    {
        if (null !== $this->request->getPath(1)) {
            $resources = \Phire\Model\UserRole::getResources($this->project->module('Phire'));
            $class = urldecode($this->request->getPath(1));
            $actions = array();

            foreach ($resources as $key => $resource) {
                if ($key == $class) {
                    $actions = $resource['actions'];
                }
            }

            $body = array('actions' => $actions);

            // Build the response and send it
            $response = new Response();
            $response->setHeader('Content-Type', 'application/json')
                     ->setBody(json_encode($body));
            $response->send();
        }
    }
}

