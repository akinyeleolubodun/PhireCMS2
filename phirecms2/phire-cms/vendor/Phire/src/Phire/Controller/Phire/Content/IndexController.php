<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Content;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends AbstractController
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
        $this->prepareView($this->viewPath . '/index.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', $this->view->i18n->__('Content'));

        $content = new Model\Content(array('acl' => $this->project->getService('acl')));

        if ((null !== $this->request->getPath(1)) && is_numeric($this->request->getPath(1))) {
            $content->getAll($this->request->getPath(1), $this->request->getQuery('sort'), $this->request->getQuery('page'));
            $this->view->set('typeId', $this->request->getPath(1))
                       ->set('table', $content->table)
                       ->set('searchTitle', $content->searchTitle)
                       ->set('sitesSearch', $content->sitesSearch)
                       ->set('title', $this->view->title . ' '. $this->view->separator . ' '. $content->title)
                       ->set('type', $content->type)
                       ->set('typeUri', $content->typeUri);
        } else {
            $this->view->set('typeId', null)
                       ->set('types', $content->getContentTypes());
        }

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
            $this->prepareView($this->viewPath . '/add.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            // Select content type
            if (null === $this->request->getPath(1)) {
                $this->view->set('title', $this->view->i18n->__('Content') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Select Type'));
                $form = new Form\Content(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    0, 0, $this->project->module('Phire')->asArray(),
                    $this->project->getService('acl')
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
                        $this->view->set('form', $form);
                        $this->send();
                    }
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, add content
            } else {
                $type = Table\ContentTypes::findById($this->request->getPath(1));

                // If content type is valid
                if (isset($type->id)) {
                    $this->view->set('typeId', $type->id)
                               ->set('typeUri', $type->uri)
                               ->set('title', $this->view->i18n->__('Content') . ' ' . $this->view->separator . ' ' . $type->name . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Add'));

                    $form = new Form\Content(
                        $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                        $type->id, 0, $this->project->module('Phire')->asArray()
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
                                $content = new Model\Content();
                                $content->save($form);
                                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '2')) {
                                    Response::redirect($this->request->getBasePath() . '/edit/' . $content->id . '?saved=' . time() . '&preview=' . time() . '&base_path=' . urlencode(BASE_PATH));
                                } else if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
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
                                $this->view->set('form', $form);
                                $this->send();
                            }
                        }
                    // Else, render form
                    } else {
                        $this->view->set('form', $form);
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
            $this->prepareView($this->viewPath . '/edit.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $content = new Model\Content();
            $content->getById($this->request->getPath(1));

            // If content object is found and valid
            if (isset($content->id) && (!in_array($content->site_id, $content->user->site_ids) || ((!$this->view->acl->isAuth('Phire\Controller\Phire\Content\IndexController', 'edit')) && (!$this->view->acl->isAuth('Phire\Controller\Phire\Content\IndexController', 'edit_' . $content->type_id))))) {
                Response::redirect($this->request->getBasePath() . '/index/' . $content->type_id);
            } else if (isset($content->id)) {
                $type = Table\ContentTypes::findById($content->type_id);
                $this->view->set('title', $this->view->i18n->__('Content') . ' ' . $this->view->separator . ' ' . $content->type_name . ' ' . $this->view->separator . ' ' . $content->content_title)
                           ->set('typeUri', $type->uri)
                           ->set('typeId', $content->type_id)
                           ->set('updated', $content->updated);

                $form = new Form\Content(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $content->type_id, $content->id, $this->project->module('Phire')->asArray()
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
                            $content->update($form);
                            if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '2')) {
                                Response::redirect($this->request->getBasePath() . '/edit/' . $content->id . '?saved=' . time() . '&preview=' . time() . '&base_path=' . urlencode(BASE_PATH));
                            } else if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
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
                            $this->view->set('form', $form);
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $form->setFieldValues(
                        $content->getData(null, false),
                        array('htmlentities'),
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
                $content->copy();
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
                $this->prepareView($this->viewPath . '/batch.phtml', array(
                    'assets'   => $this->project->getAssets(),
                    'acl'      => $this->project->getService('acl'),
                    'phireNav' => $this->project->getService('phireNav'),
                    'typeId'   => $type->id,
                    'typeUri'  => $type->uri
                ));

                $this->view->set('title', $this->view->i18n->__('Content') . ' ' . $this->view->separator . ' ' . $type->name . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Batch'));

                $form = new Form\Batch($this->request->getBasePath() . $this->request->getRequestUri(), 'post', $this->request->getPath(1));

                if ($this->request->isPost()) {
                    $content = new Model\Content();
                    $content->batch();
                    if (count($content->batchErrors) > 0) {
                        $this->view->set('form', $form);
                        $this->send();
                    } else {
                        Response::redirect($this->request->getBasePath() . '/index/' . $type->id);
                    }
                } else {
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
     * Content remove method
     *
     * @return void
     */
    public function process()
    {
        $typeId = (null !== $this->request->getPath(1)) ? '/index/' . $this->request->getPath(1) : null;
        if ($this->request->isPost()) {
            $content = new Model\Content();
            $content->process($this->request->getPost());
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

        $this->prepareView($this->viewPath . '/error.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $title = (null !== $msg) ? $this->view->i18n->__('System Error') : $this->view->i18n->__('404 Error') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Page Not Found');
        $this->view->set('title', $title);
        $this->view->set('msg', ((null !== $msg) ? $msg : $this->view->error_message));
        $this->send($code);
    }

}

