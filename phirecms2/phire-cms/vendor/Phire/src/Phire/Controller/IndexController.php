<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Filter\String;
use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Pop\Web\Mobile;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends AbstractController
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
        if ($this->project->getService('acl')->isAuth()) {
            $this->prepareView(null, array(
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));
        } else {
            $this->prepareView();
        }

        // Set up navigations
        $nav = new Model\Navigation(array('acl' => $this->project->getService('acl')));
        $this->view->merge($nav->getContentNav());
        $this->view->set('category_nav', $nav->getCategoryNav());

        $content = new Model\Content(array('acl' => $this->project->getService('acl')));
        $content->getByUri($this->request->getRequestUri());

        // Set breadcrumb and Phire model object
        $this->view->set('breadcrumb', $content->getBreadcrumb())
                   ->set('phire', new Model\Phire());

        // If site is live
        if ($this->isLive()) {
            // If page found, but requires SSL
            if (isset($content->id) && (($_SERVER['SERVER_PORT'] != '443') && ($content->force_ssl))) {
                Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            // Else, if page found and allowed
            } else if (isset($content->id) && ($content->allowed)) {
                $template = $this->getTemplate($content->template, 'index');
                if (strpos($template, '[{categor') !== false) {
                    $this->view->merge(Model\Template::parseCategories($template));
                }
                if (strpos($template, '[{recent') !== false) {
                    $this->view->merge(Model\Template::parseRecent($template));
                }
                $this->view->set('breadcrumb_title', strip_tags($content->getBreadcrumb()));
                $this->view->merge($content->getData());
                $this->view->setTemplate($template);
                $this->send();
            // Else, check for date-based URI
            } else {
                $uri = $this->request->getRequestUri();
                if (substr($uri, 0, 1) == '/') {
                    $uri = substr($uri, 1);
                }
                $date = $this->isDate($uri);
                if (null !== $date) {
                    $content->getByDate($date);
                    if (isset($content->id) && ($content->allowed)) {
                        $template = $this->getTemplate($content->template, 'index');
                        $this->view->setTemplate($template);
                        $this->view->set('breadcrumb', $content->getBreadcrumb())
                                   ->set('breadcrumb_title', strip_tags($content->getBreadcrumb()));
                        $this->view->merge($content->getData());
                        $this->send();
                    } else if (isset($content->results[0])) {
                        $content->set('title', $date['match']);
                        $template = $this->getTemplate($content->template, 'date');
                        $this->view->setTemplate($template);
                        $this->view->merge($content->getData());
                        $this->send();
                    } else {
                        $this->error();
                    }
                // Else, error page
                } else {
                    $this->error();
                }
            }
        } else {
            $this->error();
        }
    }

    /**
     * Category method
     *
     * @return void
     */
    public function category()
    {
        if ($this->project->getService('acl')->isAuth()) {
            $this->prepareView(null, array(
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));
        } else {
            $this->prepareView();
        }

        // Set up navigation
        $nav = new Model\Navigation(array('acl' => $this->project->getService('acl')));
        $this->view->merge($nav->getContentNav());
        $this->view->set('category_nav', $nav->getCategoryNav());

        $category = new Model\Category();
        $category->getByUri(substr($this->request->getRequestUri(), 9));

        // Set up breadcrumb
        $this->view->set('breadcrumb', $category->getBreadcrumb())
                   ->set('breadcrumb_title', strip_tags($category->getBreadcrumb()));

        // If site is live
        if ($this->isLive()) {
            if (isset($category->id)) {
                $tmpl = Table\Templates::findBy(array('name' => 'Category'));
                $template = (isset($tmpl->id)) ? $this->getTemplate($tmpl->id, 'category') : $this->getTemplate('category.phtml', 'category');
                if (strpos($template, '[{categor') !== false) {
                    $this->view->merge(Model\Template::parseCategories($template));
                }
                $this->view->setTemplate($template);
                $this->view->merge($category->getData());
                $this->view->set('phire', new Model\Phire());
                $this->send();
            } else {
                $this->error();
            }
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
        if ($this->project->getService('acl')->isAuth()) {
            $this->prepareView(null, array(
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));
        } else {
            $this->prepareView();
        }

        // If site is live
        if ($this->isLive()) {
            // Set up navigation
            $nav = new Model\Navigation(array('acl' => $this->project->getService('acl')));
            $this->view->merge($nav->getContentNav());
            $this->view->set('category_nav', $nav->getCategoryNav())
                       ->set('title', 'Search');

            $content = new Model\Content();
            $content->search($this->request);
            $this->view->set('phire', new Model\Phire());

            if (count($content->keys) == 0) {
                $this->view->set('error', $this->view->i18n->__('No search keywords were passed. Please try again.'));
            }

            $contentData = $content->getData();

            $tmpl = Table\Templates::findBy(array('name' => 'Search'));
            if (isset($tmpl->id)) {
                $template = $this->getTemplate($tmpl->id, 'search');

                foreach ($contentData['results'] as $key => $result) {
                    $contentData['results'][$key]['published'] = date($this->view->datetime_format, strtotime($result['published']));
                    foreach ($result as $k => $v) {
                        $matches = array();
                        preg_match_all('/\[\{' . $k . '_\d+\}\]/', $template, $matches);
                        if (isset($matches[0]) && isset($matches[0][0])) {
                            $count = substr($matches[0][0], (strpos($matches[0][0], '_') + 1));
                            $count = substr($count, 0, strpos($count, '}]'));
                            $contentData['results'][$key][$k . '_' . $count] = substr(strip_tags($result[$k]), 0, $count);
                        }
                    }
                }
            } else {
                $template = $this->getTemplate('search.phtml', 'search');
            }

            if (strpos($template, '[{categor') !== false) {
                $this->view->merge(Model\Template::parseCategories($template));
            }

            $this->view->setTemplate($template);
            $this->view->merge($contentData);
            $this->send();
        } else {
            $this->error();
        }
    }

    /**
     * Feed method
     *
     * @return void
     */
    public function feed()
    {
        if ($this->project->getService('acl')->isAuth()) {
            $this->prepareView(null, array(
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));
        } else {
            $this->prepareView();
        }

        // If site is live
        if ($this->isLive()) {
            $this->view->set('title', 'Feed');

            $lang = $this->view->default_language;
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

            $content = new Model\Content();
            $feed = new \Pop\Feed\Writer(
                $headers, $content->getFeed((int)$content->config('feed_limit')),
                (int)$content->config('feed_type')
            );

            echo $feed->render(true);
        } else {
            $this->error();
        }
    }

    /**
     * CAPTCHA method
     *
     * @return void
     */
    public function captcha()
    {
        $config = $this->project->module('Phire')->captcha;
        $sess = Session::getInstance();
        $expire = (null !== $config->expire) ? (int)$config->expire : 300;
        $i18n = Table\Config::getI18n();
        $captchaImage = '<br /><img id="captcha-image" src="' . BASE_PATH . '/captcha" /><br /><a class="reload-link" href="#" onclick="document.getElementById(\'captcha-image\').src = \'' . BASE_PATH . '/captcha?reload=1\';return false;">' . $i18n->__('Reload') . '</a>';

        // If token does not exist, create one
        if ((null !== $this->request->getQuery('reload')) || !isset($sess->pop_captcha)) {
            $token = array(
                'captcha' => $captchaImage,
                'value'   => String::random($config->length, String::ALPHANUM, String::UPPER),
                'expire'  => (int)$expire,
                'start'   => time()
            );
            $sess->pop_captcha = serialize($token);
        // Else, retrieve existing token
        } else {
            $token = unserialize($sess->pop_captcha);
            if ($token['value'] == '') {
                $token = array(
                    'captcha' => $captchaImage,
                    'value'   => String::random($config->length, String::ALPHANUM, String::UPPER),
                    'expire'  => (int)$expire,
                    'start'   => time()
                );
                $sess->pop_captcha = serialize($token);
            // Check to see if the token has expired
            } else  if ($token['expire'] > 0) {
                if (($token['expire'] + $token['start']) < time()) {
                    $token = array(
                        'captcha' => $captchaImage,
                        'value'   => String::random($config->length, String::ALPHANUM, String::UPPER),
                        'expire'  => (int)$expire,
                        'start'   => time()
                    );
                    $sess->pop_captcha = serialize($token);
                }
            }
        }

        $spacing   = $config->lineSpacing;
        $lineColor = $config->lineColor->asArray();
        $textColor = $config->textColor->asArray();

        $image = new \Pop\Image\Gd('captcha.gif', $config->width, $config->height);
        $image->setStrokeColor(new \Pop\Color\Space\Rgb($lineColor[0], $lineColor[1], $lineColor[2]));

        // Draw background grid
        for ($y = $spacing; $y <= $config->height; $y += $spacing) {
            $image->drawLine(0, $y, $config->width, $y);
        }

        for ($x = $spacing; $x <= $config->width; $x += $spacing) {
            $image->drawLine($x, 0, $x, $config->height);
        }

        $image->setStrokeColor(new \Pop\Color\Space\Rgb($textColor[0], $textColor[1], $textColor[2]))
              ->border(0.5);

        // If no font, use system font
        if (null === $config->font) {
            $textX = round(($config->width - ($config->length * 10)) / 2);
            $textY = round(($config->height - 16) / 2);
            $image->text($token['value'], 5, $textX, $textY);
        // Else, use TTF font
        } else {
            $textX = round(($config->width - ($config->length * ($config->size / 1.5))) / 2);
            $textY = round($config->height - (($config->height - $config->size) / 2) + ((int)$config->rotate / 2));
            $image->text($token['value'], $config->size, $textX, $textY, $config->font, (int)$config->rotate);
        }

        $image->output();
    }

    /**
     * Error method
     *
     * @param  string $msg
     * @return void
     */
    public function error($msg = null)
    {
        if ($this->project->getService('acl')->isAuth()) {
            $this->prepareView(null, array(
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));
        } else {
            $this->prepareView();
        }

        // Set up navigations
        $nav = new Model\Navigation(array('acl' => $this->project->getService('acl')));
        $this->view->merge($nav->getContentNav());
        $this->view->set('category_nav', $nav->getCategoryNav());

        $content = new Model\Content(array('acl' => $this->project->getService('acl')));

        $title = (null !== $msg) ? $this->view->i18n->__('System Error') : $this->view->i18n->__('404 Error') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Page Not Found');
        $code = (null !== $msg) ? 200 : 404;

        $this->view->set('title', $title)
                   ->set('msg', ((null !== $msg) ? $msg : $this->view->error_message) . PHP_EOL)
                   ->set('breadcrumb', $content->getBreadcrumb())
                   ->set('breadcrumb_title', strip_tags($content->getBreadcrumb()))
                   ->set('phire', new Model\Phire());

        $tmpl = Table\Templates::findBy(array('name' => 'Error'));
        $template = (isset($tmpl->id)) ? $this->getTemplate($tmpl->id, 'error') : $this->getTemplate('error.phtml', 'error');
        if (strpos($template, '[{categor') !== false) {
            $this->view->merge(Model\Template::parseCategories($template));
        }

        $this->view->setTemplate($template);
        $this->view->merge($content->getData());
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
        $site = Table\Sites::getSite();
        $theme = Table\Extensions::findBy(array('type' => 0, 'active' => 1), null, 1);

        if (isset($theme->id)) {
            $this->viewPath = $site->document_root . $site->base_path . CONTENT_PATH . '/extensions/themes/' . $theme->name;
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
                        // If there is a template object, fall back on the desktop template
                        } else if (isset($tmpl['desktop'])) {
                            $isFile = false;
                            $t =  $tmpl['desktop']['template'];
                            $this->response->setHeader('Content-Type', $tmpl['desktop']['content_type']);
                        }
                    }

                    $t = Model\Template::parse($t, $template);
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
        if (($isFile) && file_exists($t)) {
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

