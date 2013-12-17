<?php
/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Mvc\View;

class AbstractController extends \Pop\Mvc\Controller
{

    /**
     * Prepare view method
     *
     * @param  string $template
     * @param  array  $data
     * @return void
     */
    public function prepareView($template = null, array $data = array())
    {
        $sess = \Pop\Web\Session::getInstance();
        $config = \Phire\Table\Config::getSystemConfig();
        $jsVars = null;

        $this->view = View::factory($template, $data);
        $this->view->set('base_path', BASE_PATH)
                   ->set('content_path', CONTENT_PATH);

        // Check for an override Phire theme for the header/footer
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/header.phtml') &&
            file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/footer.phtml')) {
            $this->view->set('phireHeader', $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/header.phtml')
                       ->set('phireFooter', $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/footer.phtml');
        // Else, just use the default header/footer
        } else {
            $this->view->set('phireHeader', $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor/Phire/view/phire/header.phtml')
                       ->set('phireFooter', $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor/Phire/view/phire/footer.phtml');
        }

        if (isset($this->view->assets)) {
            $jsVars = '?lang=' . $config->default_language;
        }

        if (isset($sess->user)) {
            // Set the timeout warning, giving a 30 second buffer to act
            if (isset($this->view->assets)) {
                if (isset($this->view->acl) && ($this->view->acl->getType()->session_expiration > 0) && ($this->view->acl->getType()->timeout_warning)) {
                    $exp = ($this->view->acl->getType()->session_expiration * 60) - 30;
                    $jsVars .= '&_exp=' . $exp . '&_base=' . urlencode(BASE_PATH . APP_URI);
                }
            }

            $this->view->set('user', $sess->user)
                       ->set('role', \Phire\Table\UserRoles::getRole($sess->user->role_id))
                       ->set('globalAccess', $sess->user->global_access);

            if (isset($this->view->phireNav) && isset($this->view->acl) && ($this->view->acl->hasRole($this->view->role->getName()))) {
                $this->view->phireNav->setConfig(array(
                    'top' => array(
                        'node'  => 'ul',
                        'id'    => 'phire-nav'
                    ),
                ));
                $this->view->phireNav->setAcl($this->view->acl);
                $this->view->phireNav->setRole($this->view->role);

                $tree = $this->view->phireNav->getTree();

                // If the sub-children haven't been added yet
                if (isset($tree[0])) {
                    // And any content types to the main nav
                    $contentTypes = \Phire\Table\ContentTypes::findAll('order ASC');
                    if (isset($contentTypes->rows)) {
                        foreach ($contentTypes->rows as $type) {
                            $perm = 'index_' . $type->id;
                            if ($this->view->acl->isAuth('Phire\Controller\Phire\Content\IndexController', 'index') &&
                                $this->view->acl->isAuth('Phire\Controller\Phire\Content\IndexController', 'index_' . $type->id)) {
                                $perm = 'index';
                            }

                            $this->view->phireNav->addLeaf('Content', array(
                                'name'     => $type->name,
                                'href'     => 'index/' . $type->id,
                                'acl' => array(
                                    'resource'   => 'Phire\Controller\Phire\Content\IndexController',
                                    'permission' => $perm
                                )
                            ), 1);
                        }
                    }

                    // And any user types to the main nav
                    $userTypes = \Phire\Table\UserTypes::findAll('id ASC');
                    if (isset($userTypes->rows)) {
                        foreach ($userTypes->rows as $type) {
                            $perm = 'index_' . $type->id;
                            if ($this->view->acl->isAuth('Phire\Controller\Phire\User\IndexController', 'index') &&
                                $this->view->acl->isAuth('Phire\Controller\Phire\User\IndexController', 'index_' . $type->id)) {
                                $perm = 'index';
                            }

                            $this->view->phireNav->addLeaf('Users', array(
                                'name'     => ucwords(str_replace('-', ' ', $type->type)),
                                'href'     => 'index/' . $type->id,
                                'acl' => array(
                                    'resource'   => 'Phire\Controller\Phire\User\IndexController',
                                    'permission' => $perm
                                )
                            ), 1);
                        }
                    }
                }

                $this->view->phireNav->rebuild();
                $this->view->phireNav->nav()->setIndent('    ');
            }
        }

        if (isset($this->view->assets)) {
            $this->view->assets = str_replace('jax.3.1.0.min.js', 'jax.3.1.0.min.js' . $jsVars, $this->view->assets);
        }

        // Set config object and system/site default data
        $this->view->set('system_title', $config->system_title)
                   ->set('site_email', $config->site_email)
                   ->set('site_title', $config->site_title)
                   ->set('separator', $config->separator)
                   ->set('default_language', $config->default_language)
                   ->set('error_message', $config->error_message)
                   ->set('datetime_format', $config->datetime_format)
                   ->set('incontent_editing', $config->incontent_editing);

    }

}

