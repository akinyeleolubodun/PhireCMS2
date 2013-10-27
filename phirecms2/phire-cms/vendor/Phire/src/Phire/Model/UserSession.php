<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class UserSession extends AbstractModel
{

    /**
     * Get all roles method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'user_sessions.id' : $order['field'];

        // Create SQL object to get session data
        $sql = Table\UserSessions::getSql();
        $sqlString = 'SELECT ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.id') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_types.type') . ', ' .
            $sql->quoteId(DB_PREFIX . 'users.username') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.ip') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.user_id') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.ua') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.start') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.last') . ' AS ' . $sql->quoteId('last_date') . ', ' .
            $sql->quoteId(DB_PREFIX . 'users.type_id') . ' FROM ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions') . ' LEFT JOIN ' .
            $sql->quoteId(DB_PREFIX . 'users') . ' ON ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.user_id') . ' = ' .
            $sql->quoteId(DB_PREFIX . 'users.id') . ' LEFT JOIN ' .
            $sql->quoteId(DB_PREFIX . 'user_types') . ' ON ' .
            $sql->quoteId(DB_PREFIX . 'users.type_id') . ' = ' .
            $sql->quoteId(DB_PREFIX . 'user_types.id') . ' ORDER BY ' .
            $sql->quoteId($order['field']) . ' ' . $order['order'];

        // Execute SQL query
        $sessions = Table\UserSessions::execute($sqlString);

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\SessionsController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_sessions[]" id="remove_sessions[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_sessions" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove'
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove',
                'style' => 'display: none;'
            );
        }

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\IndexController', 'edit')) {
            $username = '<a href="' . BASE_PATH . APP_URI . '/users/edit/[{user_id}]">[{username}]</a>';
        } else {
            $username = '[{username}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'session-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/sessions/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'         => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=id">#</a>',
                    'type'       => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=type">Type</a>',
                    'username'   => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=type">Username</a>',
                    'ip'         => 'IP',
                    'ua'         => 'User Agent',
                    'started'    => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=start">Started</a>',
                    'last_date'  => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=last">Last Action</a>',
                    'process'    => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0,
            ),
            'date' => $this->config->datetime_format,
            'exclude' => array(
                'type_id', 'user_id', 'start', 'process' => array('id' => $this->data['user']->sess_id)
            ),
            'username' => $username,
            'indent'   => '        '
        );

        $sessAry = array();
        foreach ($sessions->rows as $session) {
            $session->started = date($this->config->datetime_format, strtotime($session->start)) . ' (' . \Pop\Feed\Format\AbstractFormat::calculateTime($session->start) . ')';
            $sessAry[] = $session;
        }

        if (isset($sessAry[0])) {
            $this->data['table'] = Html::encode($sessAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Remove content navigation
     *
     * @param  array   $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_sessions'])) {
            foreach ($post['remove_sessions'] as $id) {
                $session = Table\UserSessions::findById($id);
                if (isset($session->id)) {
                    $session->delete();
                }
            }
        }
    }

}

