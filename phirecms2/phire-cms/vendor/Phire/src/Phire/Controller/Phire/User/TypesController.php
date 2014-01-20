<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\User;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class TypesController extends AbstractController
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
        $this->prepareView($this->viewPath . '/types.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'title'    => 'User Types'
        ));

        $type = new Model\UserType(array('acl' => $this->project->getService('acl')));
        $type->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view->set('table', $type->table);
        $this->send();
    }

    /**
     * Type add method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView($this->viewPath . '/types.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', 'User Types ' . $this->view->separator . ' Add');

        $form = new Form\UserType(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post', 0
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
                $type = new Model\UserType();
                $type->save($form);
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
                    $this->view->set('form', $form);
                    $this->send();
                }
            }
        // Else, render the form
        } else {
            $this->view->set('form', $form);
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
            $this->prepareView($this->viewPath . '/types.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $type = new Model\UserType();
            $type->getById($this->request->getPath(1));

            // If type is found and valid
            if (null !== $type->type) {
                $this->view->set('title', 'User Types ' . $this->view->separator . ' ' . ucwords(str_replace('-', ' ', $type->type)));
                $form = new Form\UserType(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post', $this->request->getPath(1)
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
                        $type->update($form, $this->project->module('Phire'));
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
                            $this->view->set('form', $form);
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $form->setFieldValues(
                        $type->getData(null, false),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $this->view->set('form', $form);
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
            $type->remove($this->request->getPost());
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
        $this->prepareView($this->viewPath . '/error.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', '404 Error ' . $this->view->separator . ' Page Not Found')
                   ->set('msg', $this->view->error_message);
        $this->send(404);
    }

}

