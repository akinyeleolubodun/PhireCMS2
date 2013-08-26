<?php
/**
 * @namespace
 */
namespace Phire\Controller\Content;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class TemplatesController extends C
{

    /**
     * Constructor method to instantiate the templates controller object
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
            $viewPath = __DIR__ . '/../../../../view/content';

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
     * Templates index method
     *
     * @return void
     */
    public function index()
    {
        $template = new Model\Template(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Templates'
        ));

        $template->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view = View::factory($this->viewPath . '/templates.phtml', $template);
        $this->send();
    }

    /**
     * Templates add method
     *
     * @return void
     */
    public function add()
    {
        $template = new Model\Template(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Templates &gt; Add'
        ));

        $form = new Form\Template(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
            0, $this->project->isLoaded('Phields')
        );

        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('htmlentities'),
                array(null, array(ENT_QUOTES, 'UTF-8'))
            );

            if ($form->isValid()) {
                $template->save($form, $this->project->isLoaded('Phields'));
                Response::redirect($this->request->getBasePath());
            } else {
                $template->set('form', $form);
                $this->view = View::factory($this->viewPath . '/templates.phtml', $template);
                $this->send();
            }
        } else {
            $template->set('form', $form);
            $this->view = View::factory($this->viewPath . '/templates.phtml', $template);
            $this->send();
        }
    }

    /**
     * Templates edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $template = new Model\Template(array(
                'assets' => $this->project->getAssets(),
                'acl'    => $this->project->getService('acl'),
                'nav'    => $this->project->getService('nav')
            ));

            $template->getById($this->request->getPath(1), $this->project->isLoaded('Phields'));

            // If field is found and valid
            if (isset($template->id)) {
                $template->set('title', 'Templates &gt; ' . $template->name);
                $form = new Form\Template(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $template->id, $this->project->isLoaded('Phields')
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
                        $template->update($form, $this->project->isLoaded('Phields'));
                        Response::redirect($this->request->getBasePath());
                    // Else, re-render the form with errors
                    } else {
                        $template->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/templates.phtml', $template);
                        $this->send();
                    }
                // Else, render form
                } else {
                    $templateValues = $template->asArray();
                    unset($templateValues['acl']);
                    $form->setFieldValues(
                        $templateValues,
                        array('htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    $template->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/templates.phtml', $template);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * Templates remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the fields
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (isset($post['remove_templates'])) {
                foreach ($post['remove_templates'] as $id) {
                    $template = Table\Templates::findById($id);
                    if (isset($template->id)) {
                        $template->delete();
                    }

                    // If the Phields module is installed, and if there are fields for this form/model
                    if ($this->project->isLoaded('Phields')) {
                        $fields = new \Phields\Table\FieldValues();
                        $fields->delete(array('model_id' => $id));
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
        $template = new Model\Template(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => '404 Error &gt; Page Not Found'
        ));
        $this->view = View::factory($this->viewPath . '/error.phtml', $template);
        $this->send(404);
    }

}

