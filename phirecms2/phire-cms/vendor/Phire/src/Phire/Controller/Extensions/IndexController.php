<?php
/**
 * @namespace
 */
namespace Phire\Controller\Extensions;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Model;

class IndexController extends C
{

    /**
     * Session object
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Constructor method to instantiate the default controller object
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
            $viewPath = __DIR__ . '/../../../../view/extensions';

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
        $this->sess = Session::getInstance();
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $ext = new Model\Extension(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Extensions'
        ));

        $this->view = View::factory($this->viewPath . '/index.phtml', $ext);
        $this->send();
    }

    /**
     * Themes method
     *
     * @return void
     */
    public function themes()
    {
        $ext = new Model\Extension(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Extensions &gt; Themes'
        ));

        $ext->getThemes();
        $this->view = View::factory($this->viewPath . '/themes.phtml', $ext);
        $this->send();
    }

    /**
     * Modules method
     *
     * @return void
     */
    public function modules()
    {
        $ext = new Model\Extension(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => 'Extensions &gt; Modules'
        ));

        $ext->getThemes();
        $this->view = View::factory($this->viewPath . '/modules.phtml', $ext);
        $this->send();
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $ext = new Model\Extension(array(
            'assets' => $this->project->getAssets(),
            'acl'    => $this->project->getService('acl'),
            'nav'    => $this->project->getService('nav'),
            'title'  => '404 Error &gt; Page Not Found'
        ));

        $this->view = View::factory($this->viewPath . '/error.phtml', $ext);
        $this->send();
    }

}
