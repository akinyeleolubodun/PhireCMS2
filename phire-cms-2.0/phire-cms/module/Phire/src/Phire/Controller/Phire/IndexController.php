<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire;

use Phire\Controller\AbstractController,
    Phire\Form\Install as InstallForm,
    Phire\Form\User,
    Phire\Model\Install,
    Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\View;

class IndexController extends AbstractController
{

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        if (!$this->isAuth('user_id')) {
            Response::redirect($this->request->getBasePath() . '/login');
        } else {
            $this->view = View::factory($this->viewPath . '/index.phtml');
            $this->send();
        }
    }

    /**
     * Login method
     *
     * @return void
     */
    public function login()
    {
        if ($this->isAuth('user_id')) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->view = View::factory($this->viewPath . '/login.phtml');
            $this->send();
        }
    }

    /**
     * Logout method
     *
     * @return void
     */
    public function logout()
    {
        if (isset($this->sess)) {
            $this->sess->kill();
            unset($this->sess);
        }
        Response::redirect($this->request->getBasePath() . '/login');
    }

    /**
     * Forgot method
     *
     * @return void
     */
    public function forgot()
    {
        $this->view = View::factory($this->viewPath . '/forgot.phtml');
        $this->send();
    }

    /**
     * Install method
     *
     * @return void
     */
    public function install()
    {
        if ((DB_INTERFACE != '') && (DB_NAME != '') && (!isset($this->sess->install))) {
            throw new \Exception('The system is already installed.');
        } else {
            $this->sess->install = true;
            $config = new Install();
            if ((null != $this->request->getPath(1)) && ($this->request->getPath(1) == 'user')) {
                if (!isset($this->sess->config)) {
                    Response::redirect(BASE_URI . SYSTEM_URI . '/install');
                } else if ((DB_INTERFACE == '') || (DB_NAME == '')) {
                    $config->set('configWritable', false);
                    $config->set('config', unserialize($this->sess->config));
                    $config->set('url', BASE_URI . $this->sess->system_uri . '/install/user');
                    $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                    $this->send();
                } else {
                    $form = new User($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');
                    if ($this->request->isPost()) {
                        $form->setFieldValues(
                            $this->request->getPost(),
                            array('strip_tags', 'htmlentities'),
                            array(null, array(ENT_QUOTES, 'UTF-8'))
                        );
                        if ($form->isValid()) {
                            unset($this->sess->install);
                            $config->installUser($form);
                            $config->set('form', '    <p>The initial user has been created. You can <a href="http://' . $_SERVER['HTTP_HOST'] . BASE_URI . SYSTEM_URI . '/login">login in here</a>.</p>' . PHP_EOL);
                            $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                            $this->send();
                        } else {
                            $config->set('form', $form);
                            $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                            $this->send();
                        }
                    } else {
                        $config->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                        $this->send();
                    }
                }
            } else {
                $form = new InstallForm($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('strip_tags', 'htmlentities'),
                        array(null, array(ENT_QUOTES, 'UTF-8'))
                    );
                    if ($form->isValid()) {
                        $config->install($form);
                        $url = ($config->configWritable) ? BASE_URI . $form->system_uri . '/install/user' : BASE_URI . SYSTEM_URI . '/install/user';
                        Response::redirect($url);
                    } else {
                        $config->set('form', $form);
                        $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                        $this->send();
                    }
                } else {
                    $config->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                    $this->send();
                }
            }
        }
    }

}

