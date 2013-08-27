<?php
/**
 * @namespace
 */
namespace Phire\Controller\User;

use Pop\Http\Response;
use Pop\Mvc\View;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class TypesController extends AbstractController
{

    /**
     * Types index method
     *
     * @return void
     */
    public function index()
    {
        $type = new Model\UserType(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'User Types'
        ));

        $type->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/types.phtml', $type);
        $this->send();
    }

    /**
     * Type add method
     *
     * @return void
     */
    public function add()
    {
        $type = new Model\UserType(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'User Types &gt; Add'
        ));

        $form = new Form\UserType(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
            0, $this->project->isLoaded('Fields')
        );

        // If form is submitted
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('strip_tags', 'htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            // If form is valid, save new type
            if ($form->isValid()) {
                $type->save($form, $this->project->isLoaded('Fields'));
                Response::redirect(BASE_PATH . APP_URI . '/users/types');
            // Else, re-render the form with errors
            } else {
                $type->set('form', $form);
                $this->view = View::factory($this->viewPath . '/types.phtml', $type);
                $this->send();
            }
        // Else, render the form
        } else {
            $type->set('form', $form);
            $this->view = View::factory($this->viewPath . '/types.phtml', $type);
            $this->send();
        }
    }

    /**
     * Type edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $type = new Model\UserType(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));
            $type->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If type is found and valid
            if (null !== $type->type) {
                $type->set('title', 'User Types &gt; ' . $type->type);
                $form = new Form\UserType(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $this->request->getPath(1), $this->project->isLoaded('Fields')
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save type
                    if ($form->isValid()) {
                        $type->update($form, $this->project->isLoaded('Fields'));
                        Response::redirect(BASE_PATH . APP_URI . '/users/types');
                    // Else, re-render the form with errors
                    } else {
                        $type->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/types.phtml', $type);
                        $this->send();
                    }
                // Else, render form
                } else {
                    $typeValues = $type->asArray();
                    unset($typeValues['acl']);
                    $form->setFieldValues(
                        $typeValues,
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $type->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/types.phtml', $type);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect(BASE_PATH . APP_URI . '/users/types');
            }
        }
    }

    /**
     * Type remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the roles
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_types'])) {
                foreach ($post['remove_types'] as $id) {
                    $type = Table\UserTypes::findById($id);
                    if (isset($type->id)) {
                        $type->delete();
                    }

                    // If the Fields module is installed, and if there are fields for this model type
                    if ($this->project->isLoaded('Fields')) {
                        $fields = new \Fields\Table\FieldsToModels();
                        $fields->delete(array('type_id' => $id));

                        $fields = new \Fields\Table\FieldValues();
                        $fields->delete(array('model_id' => $id));
                    }
                }
            }
        }

        Response::redirect($this->request->getBasePath());
    }

}

