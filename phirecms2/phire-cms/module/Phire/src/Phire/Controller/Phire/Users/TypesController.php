<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Users;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class TypesController extends \Phire\Controller\Phire\IndexController
{

    /**
     * Constructor method to instantiate the types controller object
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
            $viewPath = __DIR__ . '/../../../../../view/users';
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Types index method
     *
     * @return void
     */
    public function index()
    {
        if (!$this->isAuth('types', 'read')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $type = new Model\Type(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'User Types'
            ));
            $type->getAll();
            $this->view = View::factory($this->viewPath . '/types.phtml', $type);
            $this->send();
        }
    }

    /**
     * Type add method
     *
     * @return void
     */
    public function add()
    {
        if (!$this->isAuth('types', 'add')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $type = new Model\Type(array(
                'acl'   => $this->project->getService('acl'),
                'title' => 'User Types &gt; Add'
            ));
            $form = new Form\Type($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

            // If form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('strip_tags', 'htmlentities'),
                    array(null, array(ENT_QUOTES, 'UTF-8'))
                );

                // If form is valid, save new type
                if ($form->isValid()) {
                    $type->save($form);
                    Response::redirect($this->request->getBasePath());
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
    }

    /**
     * Type edit method
     *
     * @return void
     */
    public function edit()
    {
        if (!$this->isAuth('types', 'edit')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $type = new Model\Type(array(
                'acl' => $this->project->getService('acl')
            ));
            $type->getById($this->request->getPath(1));

            // If type is found and valid
            if (null !== $type->type) {
                $type->set('title', 'User Types &gt; ' . $type->type);
                $form = new Form\Type($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save type
                    if ($form->isValid()) {
                        $type->update($form);
                        Response::redirect($this->request->getBasePath());
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
                Response::redirect($this->request->getBasePath());
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
        if (!$this->isAuth('types', 'remove')) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            // Loop through and delete the roles
            if ($this->request->isPost()) {
                $post = $this->request->getPost();
                if (isset($post['remove_types'])) {
                    foreach ($post['remove_types'] as $id) {
                        $type = Table\UserTypes::findById($id);
                        if (isset($type->id)) {
                            $type->delete();
                        }
                    }
                }
            }
            Response::redirect($this->request->getBasePath());
        }
    }

}

