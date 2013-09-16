<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Web\Session;
use Phire\Table;

abstract class AbstractModel extends \Pop\Mvc\Model
{

    /**
     * System config
     *
     * @var array
     */
    protected $config = array();

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return self
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);

        $sess = \Pop\Web\Session::getInstance();

        $this->data['base_path'] = BASE_PATH;
        $this->data['content_path'] = CONTENT_PATH;

        if (isset($sess->errors)) {
            $this->data['errors'] = $sess->errors;
        }

        // Check for an override Phire theme for the header/footer
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/header.phtml') &&
            file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/footer.phtml')) {
            $this->data['phireHeader'] = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/header.phtml';
            $this->data['phireFooter'] = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/footer.phtml';
        // Else, just use the default header/footer
        } else {
            $this->data['phireHeader'] = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor/Phire/view/phire/header.phtml';
            $this->data['phireFooter'] = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor/Phire/view/phire/footer.phtml';
        }

        if (isset($sess->user)) {
            $this->data['user'] = $sess->user;
            $this->data['role'] = \Phire\Table\UserRoles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
            if (isset($this->data['phireNav']) && isset($this->data['acl']) && ($this->data['acl']->hasRole($this->data['role']->getName()))) {
                $this->data['phireNav']->setConfig(array(
                    'parent' => array(
                        'node'  => 'ul',
                        'id'    => 'phire-nav'
                    ),
                ));
                $this->data['phireNav']->setAcl($this->data['acl']);
                $this->data['phireNav']->setRole($this->data['role']);

                // And any content types to the main nav
                $contentTypes = Table\ContentTypes::findAll('order ASC');
                if (isset($contentTypes->rows)) {
                    foreach ($contentTypes->rows as $type) {
                        $this->data['phireNav']->addLeaf('Content', array(
                            'name'     => $type->name,
                            'href'     => 'index/' . $type->id
                        ), 1);
                    }
                }

                // And any user types to the main nav
                $userTypes = Table\UserTypes::findAll('id ASC');
                if (isset($userTypes->rows)) {
                    foreach ($userTypes->rows as $type) {
                        $this->data['phireNav']->addLeaf('Users', array(
                            'name'     => ucwords(str_replace('-', ' ', $type->type)),
                            'href'     => 'index/' . $type->id
                        ), 1);
                    }
                }

                $this->data['phireNav']->rebuild();
                $this->data['phireNav']->nav()->setIndent('    ');
            }
        }

        // Set config object and system/site default data
        $this->config = \Phire\Table\Config::getSystemConfig();
        $this->data['site_title']       = $this->config->site_title;
        $this->data['separator']        = $this->config->separator;
        $this->data['default_language'] = $this->config->default_language;
        $this->data['error_message']    = $this->config->error_message;
        $this->data['datetime_format']  = $this->config->datetime_format;
    }

    /**
     * Create a model object
     *
     * @param  mixed $data
     * @param  string $name
     * @return static
     */
    public static function factory($data = null, $name = null)
    {
        return new static($data, $name);
    }

    /**
     * Get system config
     *
     * @param  string $name
     * @return mixed
     */
    public function config($name = null)
    {
        $result = null;

        if (null === $name) {
            $result = $this->config;
        } else if (isset($this->config[$name])) {
            $result = $this->config[$name];
        }

        return $result;
    }

    /**
     * Get sort order
     *
     * @param  string $sort
     * @param  string $page
     * @return array
     */
    public function getSortOrder($sort = null, $page = null)
    {
        $sess = Session::getInstance();
        $order = array(
            'field' => 'id',
            'order' => 'ASC'
        );

        if (null !== $sort) {
            if ($page != $sess->lastPage) {
                if ($sort != $sess->lastSortField) {
                    $order['field'] = $sort;
                    $order['order'] = 'ASC';
                } else {
                    $order['field'] = $sess->lastSortField;
                    $order['order'] = $sess->lastSortOrder;
                }
            } else {
                $order['field'] = $sort;
                if (isset($sess->lastSortOrder)) {
                    $order['order'] = ($sess->lastSortOrder == 'ASC') ? 'DESC' : 'ASC';
                } else {
                    $order['order'] = 'ASC';
                }
            }
        }

        $sess->lastSortField = $order['field'];
        $sess->lastSortOrder = $order['order'];
        $sess->lastPage = $page;

        return $order;
    }

}
