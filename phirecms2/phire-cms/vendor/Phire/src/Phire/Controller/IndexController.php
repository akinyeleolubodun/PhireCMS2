<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Mobile;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends C
{

    /**
     * Session object
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Device
     * @var string
     */
    protected $device = 'desktop';

    /**
     * Mobile flag
     * @var boolean
     */
    protected $mobile = false;

    /**
     * Tablet flag
     * @var boolean
     */
    protected $tablet = false;

    /**
     * Error action
     * @var string
     */
    protected $errorAction = 'index';

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
            $viewPath = __DIR__ . '/../../../view';

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

        if (($_SERVER['SERVER_PORT'] != '443') && (Model\Content::factory()->config()->force_ssl)) {
            Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        } else {
            parent::__construct($request, $response, $project, $viewPath);
            $this->sess = Session::getInstance();
            $this->getDevice();
        }
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $content = new Model\Content(array(
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $content->getByUri($this->request->getRequestUri(), $this->project->isLoaded('Fields'));

        // If page found, but requires SSL
        if (isset($content->id) && (($_SERVER['SERVER_PORT'] != '443') && ($content->force_ssl))) {
            Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        // Else, if page found and allowed
        } else if (isset($content->id) && ($content->allowed)) {
            $template = $this->getTemplate($content->template, 'index');
            $this->view = View::factory($template, $content);
            $this->send();
        // Else, check for date-based URI
        } else {
            $uri = $this->request->getRequestUri();
            if (substr($uri, 0, 1) == '/') {
                $uri = substr($uri, 1);
            }
            $date = $this->isDate($uri);
            if (null !== $date) {
                $content->getByDate($date, $this->project->isLoaded('Fields'));
                if (isset($content->id) && ($content->allowed)) {
                    $template = $this->getTemplate($content->template, 'index');
                    $this->view = View::factory($template, $content);
                    $this->send();
                } else if (isset($content->rows[0])) {
                    $content->set('title', $date['match']);
                    $template = $this->getTemplate($content->template, 'date');
                    $this->view = View::factory($template, $content);
                    $this->send();
                } else {
                    $this->error();
                }
            // Else, error page
            } else {
                $this->error();
            }
        }
    }

    /**
     * Category method
     *
     * @return void
     */
    public function category()
    {
        $category = new Model\Category(array(
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));
        $category->getByUri(substr($this->request->getRequestUri(), 9), $this->project->isLoaded('Fields'));
        if (isset($category->id)) {
            $tmpl = Table\Templates::findBy(array('name' => 'Category'));
            $template = (isset($tmpl->id)) ? $this->getTemplate($tmpl->id, 'category') : $this->getTemplate('category.phtml', 'category');
            $this->view = View::factory($template, $category);
            $this->send();
        } else {
            $this->error();
        }
    }

    /**
     * Search method
     *
     * @return void
     */
    public function search()
    {
        $content = new Model\Content(array(
            'title'    => 'Search',
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));
        $content->search($this->request, $this->project->isLoaded('Fields'));
        if (count($content->keys) == 0) {
            $content->set('error', 'No search keywords were passed. Please try again.');
        }

        $tmpl = Table\Templates::findBy(array('name' => 'Search'));
        $template = (isset($tmpl->id)) ? $this->getTemplate($tmpl->id, 'search') : $this->getTemplate('search.phtml', 'search');

        $this->view = View::factory($template, $content);
        $this->send();
    }

    /**
     * Feed method
     *
     * @return void
     */
    public function feed()
    {
        $content = new Model\Content(array(
            'title'    => 'Feed',
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $lang = $content->config('default_language');
        if (strpos($lang, '_') !== false) {
            $lang = substr($lang, 0, strpos($lang, '_'));
        }

        $headers = array(
            'title'     => $_SERVER['HTTP_HOST'] . ' Feed',
            'subtitle'  => $_SERVER['HTTP_HOST'] . ' Feed',
            'link'      => 'http://' . $_SERVER['HTTP_HOST'] . '/',
            'language'  => $lang,
            'updated'   => date('Y-m-d H:i:s'),
            'generator' => 'http://' . $_SERVER['HTTP_HOST'] . '/',
            'author'    => 'Phire CMS Feed Generator'
        );

        $feed = new \Pop\Feed\Writer(
            $headers, $content->getFeed((int)$content->config('feed_limit')),
            (int)$content->config('feed_type')
        );
        echo $feed->render(true);
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
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $title = (null !== $msg) ? 'System Error' : '404 Error ' . $content->config()->separator . ' Page Not Found';

        $content->set('title', $title);
        $content->set('msg', ((null !== $msg) ? $msg : $content->config()->error_message) . PHP_EOL);

        $tmpl = Table\Templates::findBy(array('name' => 'Error'));
        $template = (isset($tmpl->id)) ? $this->getTemplate($tmpl->id, 'error') : $this->getTemplate('error.phtml', 'error');
        $this->view = View::factory($template, $content);
        $this->send($code);
    }

    /**
     * Method to determine the mobile device
     *
     * @return string
     */
    protected function getDevice()
    {
        if (null !== $this->request->getQuery('mobile')) {
            $force = $this->request->getQuery('mobile');
            if ($force == 'clear') {
                unset($this->sess->mobile);
            } else {
                $this->sess->mobile = $force;
            }
        }

        if (!isset($this->sess->mobile)) {
            $this->mobile = Mobile::isMobileDevice();
            $this->tablet = Mobile::isTabletDevice();
            $device = Mobile::getMobileDevice();

            if (null !== $device) {
                $this->device = strtolower($device);
                if (($this->device == 'android') || ($this->device == 'windows')) {
                    $this->device .= ($this->tablet) ? '-tablet' : '-phone';
                }
            }
        } else {
            $this->device = $this->sess->mobile;
        }
    }

    /**
     * Method to determine the correct template
     *
     * @param  mixed  $template
     * @param  string $default
     * @return string
     */
    protected function getTemplate($template, $default = 'index')
    {
        $isFile = true;
        $theme = Table\Extensions::findBy(array('type' => 0, 'active' => 1), null, 1);
        if (isset($theme->id)) {
            $this->viewPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/' . $theme->name;
        }
        $t = $this->viewPath . '/' . $default . '.phtml';

        if (null !== $template) {
            // If the template is in the database
            if (is_numeric($template)) {
                $tmpl = Table\Templates::getTemplate($template);
                if (count($tmpl) > 0) {
                    // If a specific mobile template is set
                    if (isset($tmpl[$this->device])) {
                        $isFile = false;
                        $t =  $tmpl[$this->device]['template'];
                        $this->response->setHeader('Content-Type', $tmpl[$this->device]['content_type']);
                    // Else, attempt to fall back on a generic mobile template
                    } else if ($this->device != 'desktop') {
                        $device = null;
                        if (isset($tmpl['tablet'])) {
                            $device = 'tablet';
                        } else if (isset($tmpl['phone'])) {
                            $device = 'phone';
                        } else if (isset($tmpl['mobile'])) {
                            $device = 'mobile';
                        }
                        if (null !== $device) {
                            $isFile = false;
                            $t =  $tmpl[$device]['template'];
                            $this->response->setHeader('Content-Type', $tmpl[$device]['content_type']);
                        }
                    }
                }
            // Else, if the template is a file
            } else {
                $t = $this->viewPath . '/' . $template;
                if ($this->device != 'desktop') {
                    $mobileDir = $this->viewPath . '/' . substr($template, 0, strrpos($template, '.'));
                    if (file_exists($mobileDir) && is_dir($mobileDir)) {
                        // If a specific mobile template file is available
                        if (file_exists($mobileDir . '/' . $this->device . '.phtml')) {
                            $t = $mobileDir . '/' . $this->device . '.phtml';
                        } else if (file_exists($mobileDir . '/' . $this->device . '.php')) {
                            $t = $mobileDir . '/' . $this->device . '.php';
                        } else if (file_exists($mobileDir . '/' . $this->device . '.php3')) {
                            $t = $mobileDir . '/' . $this->device . '.php3';
                        // Else, attempt to fall back on a generic mobile template file
                        } else {
                            $altDevices = array('tablet', 'phone', 'mobile');
                            foreach ($altDevices as $device) {
                                if (file_exists($mobileDir . '/' . $device . '.phtml')) {
                                    $t = $mobileDir . '/' . $device . '.phtml';
                                } else if (file_exists($mobileDir . '/' . $device . '.php')) {
                                    $t = $mobileDir . '/' . $device . '.php';
                                } else if (file_exists($mobileDir . '/' . $device . '.php3')) {
                                    $t = $mobileDir . '/' . $device . '.php3';
                                }
                            }
                        }
                    }
                }
            }
        }

        // Check is the template file has a Content-Type override
        if ($isFile) {
            $f = file_get_contents($t);
            if (strpos($f, 'Content-Type:') != false) {
                $contentType = substr($f, (strpos($f, 'Content-Type:') + 13));
                $contentType = trim(substr($contentType, 0, strpos($contentType, "\n")));
                if (in_array($contentType, Form\Template::getContentTypes())) {
                    $this->response->setHeader('Content-Type', $contentType);
                }
            }
        }

        return $t;
    }

    /**
     * Method to determine if the URI is a date
     *
     * @param  string $subject
     * @return mixed
     */
    protected function isDate($subject)
    {
        $regexes = array(
            10 => '/^(1[0-9]{3}|2[0-9]{3})\/(0[1-9]|1[0-2])\/(0[1-9]|1[0-9]|2[0-9]|3[0-1])$/', // YYYY/MM/DD
            7  => '/^(1[0-9]{3}|2[0-9]{3})\/(0[1-9]|1[0-2])$/',                                // YYYY/MM
            4  => '/^(1[0-9]{3}|2[0-9]{3})$/'                                                  // YYYY
        );

        $result = null;

        foreach ($regexes as $length => $regex) {
            $match = substr($subject, 0, $length);
            if (preg_match($regex, $match)) {
                $result = array(
                    'match' => $match,
                    'uri'   => substr($subject, $length)
                );
                break;
            }
        }

        return $result;
    }

}

