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

class TypesController extends C
{

    /**
     * Constructor method to instantiate the content types controller object
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
                    $viewPath = $cfg['view']['*'];
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'];
                }
            }
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Content types index method
     *
     * @return void
     */
    public function index()
    {
        $type = new Model\ContentType(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Content Types'
        ));

        $type->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/types.phtml', $type);
        $this->send();
    }

    /**
     * Content types add method
     *
     * @return void
     */
    public function add()
    {
        $type = new Model\ContentType(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav')
        ));

        $type->set('title', 'Content Types ' . $type->config()->separator . ' Add');
        $form = new Form\ContentType(
            $this->request->getBasePath() . $this->request->getRequestUri() . (isset($_GET['redirect']) ? '?redirect=1' : null),
            'post', 0, $this->project->isLoaded('Fields')
        );

        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('strip_tags', 'htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            if ($form->isValid()) {
                $type->save($form, $this->project->isLoaded('Fields'));
                $url = ($form->redirect) ? BASE_PATH . APP_URI . '/content/add' : $this->request->getBasePath();
                Response::redirect($url);
            } else {
                $type->set('form', $form);
                $this->view = View::factory($this->viewPath . '/types.phtml', $type);
                $this->send();
            }
        } else {
            $type->set('form', $form);
            $this->view = View::factory($this->viewPath . '/types.phtml', $type);
            $this->send();
        }
    }

    /**
     * Content types edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $type = new Model\ContentType(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));

            $type->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If field is found and valid
            if (isset($type->id)) {
                $type->set('title', 'Content Types ' . $type->config()->separator . ' ' . $type->name);
                $form = new Form\ContentType(
                    $this->request->getBasePath() . $this->request->getRequestUri(),
                    'post', $type->id, $this->project->isLoaded('Fields')
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
                        $type->update($form, $this->project->isLoaded('Fields'));
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
     * Content types remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the fields
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_types'])) {
                foreach ($post['remove_types'] as $id) {
                    $type = Table\ContentTypes::findById($id);
                    if (isset($type->id)) {
                        if (!$type->uri) {
                            $content = Table\Content::findBy(array('type_id' => $type->id));
                            foreach ($content->rows as $c) {
                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media/' . $content->uri)) {
                                    Model\Content::removeMedia($content->uri);
                                }
                            }
                        }
                        $type->delete();
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
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $type = new Model\ContentType(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav')
        ));

        $type->set('title', '404 Error ' . $type->config()->separator . ' Page Not Found');
        $this->view = View::factory($this->viewPath . '/error.phtml', $type);
        $this->send(404);
    }

}

