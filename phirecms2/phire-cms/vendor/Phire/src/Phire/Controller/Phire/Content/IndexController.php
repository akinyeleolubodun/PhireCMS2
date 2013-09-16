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

class IndexController extends C
{

    /**
     * Constructor method to instantiate the content controller object
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
     * Content index method
     *
     * @return void
     */
    public function index()
    {
        $content = new Model\Content(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'title'    => 'Content'
        ));

        if ((null !== $this->request->getPath(1)) && is_numeric($this->request->getPath(1))) {
            $content->getAll($this->request->getPath(1), $this->request->getQuery('sort'), $this->request->getQuery('page'));
            $content->set('typeId', $this->request->getPath(1));
        } else {
            $content->getContentTypes();
            $content->set('typeId', null);
        }

        $this->view = View::factory($this->viewPath . '/index.phtml', $content);
        $this->send();
    }

    /**
     * Content add method
     *
     * @return void
     */
    public function add()
    {
        if (count(Table\ContentTypes::findAll()->rows) == 0) {
            Response::redirect($this->request->getBasePath() . '/types/add?redirect=1');
        } else {
            // Select content type
            if (null === $this->request->getPath(1)) {
                $content = new Model\Content(array(
                    'assets'   => $this->project->getAssets(),
                    'acl'      => $this->project->getService('acl'),
                    'phireNav' => $this->project->getService('phireNav')
                ));

                $content->set('title', 'Content ' . $content->config()->separator . ' Select Type');
                $form = new Form\Content(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    0, 0, $this->project->isLoaded('Fields'), $this->project->module('Phire')->asArray()
                );

                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    if ($form->isValid()) {
                        Response::redirect($this->request->getBasePath() . $this->request->getRequestUri() . '/' . $form->type_id);
                    } else {
                        $content->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $content);
                        $this->send();
                    }
                } else {
                    $content->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $content);
                    $this->send();
                }
            // Else, add content
            } else {
                $type = Table\ContentTypes::findById($this->request->getPath(1));

                // If content type is valid
                if (isset($type->id)) {
                    $content = new Model\Content(array(
                        'assets'   => $this->project->getAssets(),
                        'acl'      => $this->project->getService('acl'),
                        'phireNav' => $this->project->getService('phireNav'),
                        'typeId'   => $type->id,
                        'typeUri'  => $type->uri
                    ));

                    $content->set('title', 'Content ' . $content->config()->separator . ' ' . $type->name . ' ' . $content->config()->separator . ' Add');
                    $form = new Form\Content(
                        $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                        $type->id, 0, $this->project->isLoaded('Fields'), $this->project->module('Phire')->asArray()
                    );

                    // If form is submitted
                    if ($this->request->isPost()) {
                        $form->setFieldValues(
                            $this->request->getPost(),
                            array('htmlentities'),
                            array(null, array(ENT_QUOTES, 'UTF-8'))
                        );

                        // If form is valid, save new content
                        if ($form->isValid()) {
                            try {
                                $content->save($form, $this->project->isLoaded('Fields'));
                                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                                    Response::redirect($this->request->getBasePath() . '/edit/' . $content->id . '?saved=' . time());
                                } else if (null !== $this->request->getQuery('update')) {
                                    $this->sendJson(array(
                                        'redirect' => $this->request->getBasePath() . '/edit/' . $content->id . '?saved=' . time(),
                                        'updated'  => '<strong>Updated:</strong> ' . date($content->config()->datetime_format, time()) . ' by <strong>' . $content->user->username . '</strong>',
                                        'form'     => 'content-form'
                                    ));
                                } else {
                                    Response::redirect($this->request->getBasePath() . '/index/' . $this->request->getPath(1));
                                }
                            } catch (\Exception $e) {
                                $this->error($e->getMessage());
                            }
                        // Else, re-render form with errors
                        } else {
                            if (null !== $this->request->getQuery('update')) {
                                $this->sendJson($form->getErrors());
                            } else {
                                $content->set('form', $form);
                                $this->view = View::factory($this->viewPath . '/index.phtml', $content);
                                $this->send();
                            }
                        }
                    // Else, render form
                    } else {
                        $content->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/index.phtml', $content);
                        $this->send();
                    }
                // Else, redirect
                } else {
                    Response::redirect($this->request->getBasePath() . '/add');
                }
            }
        }
    }

    /**
     * Content edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $content = new Model\Content(array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $content->getById($this->request->getPath(1), $this->project->isLoaded('Fields'));

            // If content object is found and valid
            if (isset($content->id)) {
                $content->set('title', 'Content ' . $content->config()->separator . ' ' . $content->type_name . ' ' . $content->config()->separator . ' ' . $content->content_title);
                $content->set('typeId', $content->type_id);
                $form = new Form\Content(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $content->type_id, $content->id, $this->project->isLoaded('Fields'), $this->project->module('Phire')->asArray()
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save field
                    if ($form->isValid()) {
                        try {
                            $content->update($form, $this->project->isLoaded('Fields'));
                            if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                                Response::redirect($this->request->getBasePath() . '/edit/' . $content->id . '?saved=' . time());
                            } else if (null !== $this->request->getQuery('update')) {
                                $this->sendJson(array(
                                    'updated' => '<strong>Updated:</strong> ' . date($content->config()->datetime_format, time()) . ' by <strong>' . $content->user->username . '</strong>',
                                    'form'    => 'content-form'
                                ));
                            } else if ((null !== $this->request->getPost('live')) && ($this->request->getPost('live') == 1)) {
                                Response::redirect(BASE_PATH . $content->uri);
                            } else {
                                Response::redirect($this->request->getBasePath() . '/index/' . $form->type_id);
                            }
                        } catch (\Exception $e) {
                            $this->error($e->getMessage());
                        }
                    // Else, re-render the form with errors
                    } else {
                        if (null !== $this->request->getQuery('update')) {
                            $this->sendJson($form->getErrors());
                        } else {
                            $content->set('form', $form);
                            $this->view = View::factory($this->viewPath . '/index.phtml', $content);
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $contentValues = $content->asArray();
                    unset($contentValues['acl']);
                    $form->setFieldValues(
                        $contentValues,
                        array('htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $content->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $content);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * Content copy method
     *
     * @return void
     */
    public function copy()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $content = new Model\Content();
            $content->getById($this->request->getPath(1));
            if (isset($content->id)) {
                $content->copy($this->project->isLoaded('Fields'));
                Response::redirect($this->request->getBasePath() . '/index/' . $content->type_id);
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * Content batch add method
     *
     * @return void
     */
    public function batch()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $type = Table\ContentTypes::findById($this->request->getPath(1));

            // If content type is valid
            if (isset($type->id)) {
                $content = new Model\Content(array(
                    'assets'   => $this->project->getAssets(),
                    'acl'      => $this->project->getService('acl'),
                    'phireNav' => $this->project->getService('phireNav'),
                    'typeId'   => $type->id,
                    'typeUri'  => $type->uri
                ));

                $content->set('title', 'Content ' . $content->config()->separator . ' ' . $type->name . ' ' . $content->config()->separator . ' Batch');
                $form = new Form\Batch($this->request->getBasePath() . $this->request->getRequestUri(), 'post', $this->request->getPath(1));

                if ($this->request->isPost()) {
                    $content->batch();
                    if (count($content->batchErrors) > 0) {
                        $content->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/batch.phtml', $content);
                        $this->send();
                    } else {
                        Response::redirect($this->request->getBasePath() . '/index/' . $type->id);
                    }
                } else {
                    $content->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/batch.phtml', $content);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath() . '/add');
            }
        }
    }

    /**
     * Content remove method
     *
     * @return void
     */
    public function remove()
    {
        $typeId = null;

        // Loop through and delete the fields
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_content'])) {
                $model = new Model\Content();
                $open = $model->config('open_authoring');
                foreach ($post['remove_content'] as $id) {
                    $content = Table\Content::findById($id);
                    $createdBy = null;
                    if (isset($content->id)) {
                        $typeId = '/index/' . $content->type_id;
                        $createdBy = $content->created_by;
                        if (!((!$open) && ($content->created_by != $model->user->id))) {
                            $type = Table\ContentTypes::findById($content->type_id);
                            if (isset($type->id) && (!$type->uri)) {
                                Model\Content::removeMedia($content->uri);
                            }
                            $content->delete();
                        }
                    }

                    // If the Fields module is installed, and if there are fields for this form/model
                    if ($this->project->isLoaded('Fields') && !((!$open) && ($createdBy != $model->user->id))) {
                        \Fields\Model\FieldValue::remove($id);
                    }
                }
            }
        }

        Response::redirect($this->request->getBasePath() . $typeId);
    }

    /**
     * Method to get other parent content objects via JS
     *
     * @return void
     */
    public function json()
    {
        if (null !== $this->request->getPath(1)) {
            $uri = '';
            $content = Table\Content::findById($this->request->getPath(1));

            // Construct the full parent URI
            if (isset($content->id)) {
                $uri = $content->slug;
                while ($content->parent_id != 0) {
                    $content = Table\Content::findById($content->parent_id);
                    if (isset($content->id)) {
                        $uri = $content->slug . '/' . $uri;
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
     * @param  string $msg
     * @return void
     */
    public function error($msg = null)
    {
        $code = (null !== $msg) ? 200 : 404;
        $content = new Model\Content(array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),

        ));

        $title = (null !== $msg) ? 'System Error' : '404 Error ' . $content->config()->separator . ' Page Not Found';
        $content->set('title', $title);
        $content->set('msg', ((null !== $msg) ? $msg : $content->config()->error_message) . PHP_EOL);
        $this->view = View::factory($this->viewPath . '/error.phtml', $content);
        $this->send($code);
    }

}

