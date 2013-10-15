<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Content;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class NavigationController extends C
{

    /**
     * Constructor method to instantiate the navigation controller object
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
            $viewPath = __DIR__ . '/../../../../../view/phire/content';

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'] . '/content';
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'] . '/content';
                }
            }
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Navigation index method
     *
     * @return void
     */
    public function index()
    {
        $navigation = new Model\Navigation(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'title'    => 'Navigation'
        ));

        $navigation->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/navigation.phtml', $navigation->getData());
        $this->send();
    }

    /**
     * Navigation add method
     *
     * @return void
     */
    public function add()
    {
        $navigation = new Model\Navigation(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $navigation->set('title', 'Navigation ' . $navigation->config()->separator . ' Add');
        $form = new Form\Navigation(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
            0, $this->project->isLoaded('Fields')
        );

        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('strip_tags', 'htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            if ($form->isValid()) {
                $navigation->save($form, $this->project->isLoaded('Fields'));
                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                    Response::redirect($this->request->getBasePath() . '/edit/' . $navigation->id . '?saved=' . time());
                } else if (null !== $this->request->getQuery('update')) {
                    $this->sendJson(array(
                        'redirect' => $this->request->getBasePath() . '/edit/' . $navigation->id . '?saved=' . time(),
                        'updated'  => '',
                        'form'     => 'navigation-form'
                    ));
                } else {
                    Response::redirect($this->request->getBasePath());
                }
            } else {
                if (null !== $this->request->getQuery('update')) {
                    $this->sendJson($form->getErrors());
                } else {
                    $navigation->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/navigation.phtml', $navigation->getData());
                    $this->send();
                }
            }
        } else {
            $navigation->set('form', $form);
            $this->view = View::factory($this->viewPath . '/navigation.phtml', $navigation->getData());
            $this->send();
        }
    }

    /**
     * Navigation edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $navigation = new Model\Navigation(array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $navigation->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If field is found and valid
            if (isset($navigation->id)) {
                $navigation->set('title', 'Navigation ' . $navigation->config()->separator . ' ' . $navigation->navigation);
                $form = new Form\Navigation(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $navigation->id, $this->project->isLoaded('Fields')
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save field
                    if ($form->isValid()) {
                        $navigation->update($form, $this->project->isLoaded('Fields'));
                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $navigation->id . '?saved=' . time());
                        } else if (null !== $this->request->getQuery('update')) {
                            $this->sendJson(array(
                                'updated' => '',
                                'form'    => 'navigation-form'
                            ));
                        } else {
                            Response::redirect($this->request->getBasePath());
                        }
                    // Else, re-render the form with errors
                    } else {
                        if (null !== $this->request->getQuery('update')) {
                            $this->sendJson($form->getErrors());
                        } else {
                            $navigation->set('form', $form);
                            $this->view = View::factory($this->viewPath . '/navigation.phtml', $navigation->getData());
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $navigationValues = $navigation->getData();
                    unset($navigationValues['acl']);
                    $form->setFieldValues(
                        $navigationValues,
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $navigation->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/navigation.phtml', $navigation->getData());
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * Navigation remove method
     *
     * @return void
     */
    public function remove()
    {
        if ($this->request->isPost()) {
            $navigation = new Model\Navigation();
            $navigation->remove($this->request->getPost(), $this->project->isLoaded('Fields'));
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
        $navigation = new Model\Navigation(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $navigation->set('title', '404 Error ' . $navigation->config()->separator . ' Page Not Found');
        $this->view = View::factory($this->viewPath . '/error.phtml', $navigation->getData());
        $this->send(404);
    }

}

