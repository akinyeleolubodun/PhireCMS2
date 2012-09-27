<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Phire\Form\Install,
    Phire\Model\SysConfig,
    Pop\Http\Response,
    Pop\Http\Request,
    Pop\Mvc\View;

class PhireController extends AbstractController
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
        if ((DB_INTERFACE != '') && (DB_NAME != '') && (!isset($this->sess->config))) {
            throw new \Exception('The system is already installed.');
        } else {
            $config = new SysConfig();
            if ((null != $this->request->getPath(1)) && ($this->request->getPath(1) == 'user')) {
                if ((DB_INTERFACE == '') || (DB_NAME == '')) {
                    $config->set('configWritable', false);
                    $config->set('config', unserialize($this->sess->config));
                    $this->view = View::factory($this->viewPath . '/install.phtml', $config);
                    $this->send();
                } else {
                    //unset($this->sess->config);
                    echo 'Set up initial user.';
                }
            } else {
                $form = new Install($this->request->getBasePath() . $this->request->getRequestUri(), 'post', null, '    ');
                if ($this->request->isPost()) {
                    $form->setFieldValues($this->request->getPost(), array('html', 'stripTags'));
                    if ($form->isValid()) {
                        $config->install($form);
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
        }
    }

}

