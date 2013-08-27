<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Web\Session;

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

        if (isset($sess->user)) {
            $this->data['user'] = $sess->user;
            $this->data['role'] = \Phire\Table\UserRoles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
            if (isset($this->data['nav']) && isset($this->data['acl'])) {
                $this->data['nav']->setConfig(array(
                    'parent' => array(
                        'node'  => 'ul',
                        'id'    => 'main-nav'
                    ),
                ));
                $this->data['nav']->setAcl($this->data['acl']);
                $this->data['nav']->setRole($this->data['role']);
                $this->data['nav']->nav()->setIndent('    ');
            }
        }

        $this->config = \Phire\Table\Config::getSystemConfig();
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
