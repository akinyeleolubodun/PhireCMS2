<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class ConfigController extends C
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
            $viewPath = __DIR__ . '/../../../../view/phire';

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
        $config = new Model\Config(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Configuration'
        ));

        if ($this->request->isPost()) {
            $config->update($this->request->getPost());
            Response::redirect($this->request->getBasePath());
        } else {
            $config->getAll();
            $this->view = View::factory($this->viewPath . '/config.phtml', $config);
            $this->send();
        }
    }
    /**
     * Method to get example
     *
     * @return void
     */
    public function json()
    {
        if (null !== $this->request->getPath(1)) {
            $format = str_replace('\\', '/', urldecode($this->request->getPath(1)));

            // Build the response and send it
            $response = new Response();
            $response->setHeader('Content-Type', 'application/json')
                     ->setBody(json_encode(array('format' => date($format))));
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

