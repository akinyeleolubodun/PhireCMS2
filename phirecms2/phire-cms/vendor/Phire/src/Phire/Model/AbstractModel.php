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

        $this->config = \Phire\Table\Config::getSystemConfig();
        $sess = \Pop\Web\Session::getInstance();

        if (isset($sess->user)) {
            $this->data['user'] = $sess->user;
            $this->data['role'] = \Phire\Table\UserRoles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
        }
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
     * @param  string  $key
     * @param  boolean $user
     * @return mixed
     */
    public function getData($key = null, $user = true)
    {
        if (null !== $key) {
            return (isset($this->data[$key])) ? $this->data[$key] : null;
        } else {
            $data = $this->data;
            if (!$user) {
                unset($data['user']);
            }
            return $data;
        }
    }

    /**
     * Method to filter the content and replace any placeholders
     *
     * @param   array $data
     * @returns array
     */
    protected function filterContent(array $data = null)
    {
        $dataAry = (null === $data) ? $this->data : $data;

        if ((int)$dataAry['site_id'] > 0) {
            $site     = Table\Sites::findById((int)$dataAry['site_id']);
            $basePath = (isset($site->id)) ? $site->base_path : BASE_PATH;
        } else {
            $basePath = BASE_PATH;
        }

        $keys = array_keys($dataAry);

        foreach ($dataAry as $key => $value) {
            if (is_string($value)) {
                $value = str_replace(array('[{base_path}]', '[{content_path}]'), array($basePath, CONTENT_PATH), $value);
                foreach ($keys as $k) {
                    if ((strpos($value, '[{' . $k . '}]') !== false) && ($dataAry[$k])) {
                        $value = str_replace('[{' . $k . '}]', $dataAry[$k], $value);
                    }
                }
                $dataAry[$key] = $value;
            }
        }

        if (null === $data) {
            $this->data = $dataAry;
        }

        return $dataAry;
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
