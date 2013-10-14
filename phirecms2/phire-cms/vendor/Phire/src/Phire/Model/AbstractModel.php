<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Web\Session;
use Phire\Table;

abstract class AbstractModel
{

    /**
     * Model data
     *
     * @var array
     */
    protected $data = array();

    /**
     * System config
     *
     * @var array
     */
    protected $config = array();

    /**
     * Instantiate the model object.
     *
     * @param  array $data
     * @return self
     */
    public function __construct(array $data = null)
    {
        if (null !== $data) {
            $this->data = $data;
        }

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

        $jsVars = null;

        // Set config object and system/site default data
        $this->config = \Phire\Table\Config::getSystemConfig();
        if (isset($this->data['assets'])) {
            $jsVars = '?_lang=' . $this->config->default_language;
        }

        if (isset($sess->user)) {
            if (isset($this->data['assets'])) {
                // Set the timeout warning, giving a 30 second buffer to act
                if (isset($this->data['acl']) && ($this->data['acl']->getType()->session_expiration > 0) && ($this->data['acl']->getType()->timeout_warning)) {
                    $exp = ($this->data['acl']->getType()->session_expiration * 60) - 30;
                    $jsVars .= '&_exp=' . $exp . '&_base=' . urlencode(BASE_PATH . APP_URI);
                }

                $this->data['assets'] = str_replace('jax.3.0.0.min.js', 'jax.3.0.0.min.js' . $jsVars, $this->data['assets']);
            }


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

                $tree = $this->data['phireNav']->getTree();

                // If the sub-children haven't been added yet
                if (isset($tree[0]) && isset($tree[0]['children']) && isset($tree[0]['children'][0]) && !isset($tree[0]['children'][0]['children'])) {
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
                }

                $this->data['phireNav']->rebuild();
                $this->data['phireNav']->nav()->setIndent('    ');
            }
        } else {
            if (isset($this->data['assets'])) {
                $this->data['assets'] = str_replace('jax.3.0.0.min.js', 'jax.3.0.0.min.js' . $jsVars, $this->data['assets']);
            }
        }

        // Set config object and system/site default data
        $this->data['system_title']       = $this->config->system_title;
        $this->data['site_title']         = $this->config->site_title;
        $this->data['separator']          = $this->config->separator;
        $this->data['default_language']   = $this->config->default_language;
        $this->data['error_message']      = $this->config->error_message;
        $this->data['datetime_format']    = $this->config->datetime_format;
        $this->data['incontent_editing']  = $this->config->incontent_editing;
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

    /**
     * Set model data
     *
     * @param  string $name,
     * @param  mixed $value
     * @return self
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Get method to return the value of data[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return (isset($this->data[$name])) ? $this->data[$name] : null;
    }

    /**
     * Get model data
     *
     * @param  string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (null !== $key) {
            return (isset($this->data[$key])) ? $this->data[$key] : null;
        } else {
            return $this->data;
        }
    }

    /**
     * Get method to return the value of data[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set method to set the property to the value of data[$name].
     *
     * @param  string $name
     * @param  mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Return the isset value of data[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Unset data[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

}
