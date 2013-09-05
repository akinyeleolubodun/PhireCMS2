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

class CategoriesController extends C
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
     * Categories index method
     *
     * @return void
     */
    public function index()
    {
        $category = new Model\Category(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Categories'
        ));

        $category->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/categories.phtml', $category);
        $this->send();
    }

    /**
     * Categories add method
     *
     * @return void
     */
    public function add()
    {
        $category = new Model\Category(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav')
        ));

        $category->set('title', 'Categories ' . $category->config()->separator . ' Add');
        $form = new Form\Category(
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
                $category->save($form, $this->project->isLoaded('Fields'));
                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                    Response::redirect($this->request->getBasePath() . '/edit/' . $category->id . '?saved=' . time());
                } else if (null !== $this->request->getQuery('update')) {
                    $this->sendJson(array(
                        'redirect' => $this->request->getBasePath() . '/edit/' . $category->id . '?saved=' . time(),
                        'updated'  => '',
                        'form'     => 'category-form'
                    ));
                } else {
                    Response::redirect($this->request->getBasePath());
                }
            } else {
                if (null !== $this->request->getQuery('update')) {
                    $this->sendJson($form->getErrors());
                } else {
                    $category->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/categories.phtml', $category);
                    $this->send();
                }
            }
        } else {
            $category->set('form', $form);
            $this->view = View::factory($this->viewPath . '/categories.phtml', $category);
            $this->send();
        }
    }

    /**
     * Categories edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $category = new Model\Category(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));

            $category->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If field is found and valid
            if (isset($category->id)) {
                $category->set('title', 'Categories ' . $category->config()->separator . ' ' . $category->category);
                $form = new Form\Category(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $category->id, $this->project->isLoaded('Fields')
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
                        $category->update($form, $this->project->isLoaded('Fields'));
                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $category->id . '?saved=' . time());
                        } else if (null !== $this->request->getQuery('update')) {
                            $this->sendJson(array(
                                'updated' => '',
                                'form'    => 'category-form'
                            ));
                        } else {
                            Response::redirect($this->request->getBasePath());
                        }
                    // Else, re-render the form with errors
                    } else {
                        if (null !== $this->request->getQuery('update')) {
                            $this->sendJson($form->getErrors());
                        } else {
                            $category->set('form', $form);
                            $this->view = View::factory($this->viewPath . '/categories.phtml', $category);
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $categoryValues = $category->asArray();
                    unset($categoryValues['acl']);
                    $form->setFieldValues(
                        $categoryValues,
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $category->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/categories.phtml', $category);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * Categories remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the fields
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_categories'])) {
                foreach ($post['remove_categories'] as $id) {
                    $category = Table\Categories::findById($id);
                    if (isset($category->id)) {
                        $category->delete();
                    }

                    // If the Fields module is installed, and if there are fields for this form/model
                    if ($this->project->isLoaded('Fields')) {
                        \Fields\Model\FieldValue::remove($id);
                    }
                }
            }
        }

        Response::redirect($this->request->getBasePath());
    }

    /**
     * Method to get other parent category objects via JS
     *
     * @return void
     */
    public function json()
    {
        if (null !== $this->request->getPath(1)) {
            $uri = '/';
            $category = Table\Categories::findById($this->request->getPath(1));

            // Construct the full parent URI
            if (isset($category->id)) {
                $uri = $category->slug;
                while ($category->parent_id != 0) {
                    $category = Table\Categories::findById($category->parent_id);
                    if (isset($category->id)) {
                        $uri = $category->slug . '/' . $uri;
                    }
                }
            }

            $body = array('uri' => $uri . '/');

            // Build the response and send it
            $response = new Response();
            $response->setHeader('Content-Type', 'application/json')
                     ->setBody(json_encode($body));
            $response->send();
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $category = new Model\Category(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav')
        ));

        $category->set('title', '404 Error ' . $category->config()->separator . ' Page Not Found');
        $this->view = View::factory($this->viewPath . '/error.phtml', $category);
        $this->send(404);
    }

}

