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

class TypesController extends C
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
                    $viewPath = $cfg['view']['*'] . '/user';
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'] . '/user';
                }
            }
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
        $type = new Model\UserType(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'title'    => 'User Types'
        ));

        $type->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/types.phtml', $type->getData());
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
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $type->set('title', 'User Types ' . $type->config()->separator . ' Add');

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
                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                    Response::redirect($this->request->getBasePath() . '/edit/' . $type->id . '?saved=' . time());
                } else if (null !== $this->request->getQuery('update')) {
                    $this->sendJson(array(
                        'redirect' => $this->request->getBasePath() . '/edit/' . $type->id . '?saved=' . time(),
                        'updated'  => '',
                        'form'     => 'user-type-form'
                    ));
                } else {
                    Response::redirect($this->request->getBasePath());
                }
            // Else, re-render the form with errors
            } else {
                if (null !== $this->request->getQuery('update')) {
                    $this->sendJson($form->getErrors());
                } else {
                    $type->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/types.phtml', $type->getData());
                    $this->send();
                }
            }
        // Else, render the form
        } else {
            $type->set('form', $form);
            $this->view = View::factory($this->viewPath . '/types.phtml', $type->getData());
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
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));
            $type->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If type is found and valid
            if (null !== $type->type) {
                $type->set('title', 'User Types ' . $type->config()->separator . ' ' . ucwords(str_replace('-', ' ', $type->type)));
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
                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $type->id . '?saved=' . time());
                        } else if (null !== $this->request->getQuery('update')) {
                            $this->sendJson(array(
                                'updated' => '',
                                'form'    => 'user-type-form'
                            ));
                        } else {
                            Response::redirect($this->request->getBasePath());
                        }
                    // Else, re-render the form with errors
                    } else {
                        if (null !== $this->request->getQuery('update')) {
                            $this->sendJson($form->getErrors());
                        } else {
                            $type->set('form', $form);
                            $this->view = View::factory($this->viewPath . '/types.phtml', $type->getData());
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $typeValues = $type->getData();
                    unset($typeValues['acl']);
                    $form->setFieldValues(
                        $typeValues,
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $type->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/types.phtml', $type->getData());
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
        if ($this->request->isPost()) {
            $type = new Model\UserType();
            $type->remove($this->request->getPost(), $this->project->isLoaded('Fields'));
        }

        Response::redirect($this->request->getBasePath());
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $type = new Model\UserType(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $type->set('title', '404 Error ' . $type->config()->separator . ' Page Not Found');
        $this->view = View::factory($this->viewPath . '/error.phtml', $type->getData());
        $this->send(404);
    }

}

