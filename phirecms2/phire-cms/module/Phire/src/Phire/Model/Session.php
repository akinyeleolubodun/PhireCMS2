<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table\UserRoles;
use Phire\Table\UserSessions;

class Session extends \Pop\Mvc\Model
{

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

        if (isset($sess->user)) {
            $this->data['user'] = $sess->user;
            $this->data['role'] = UserRoles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
        }
    }

    /**
     * Get all roles method
     *
     * @return void
     */
    public function getAll()
    {
        $sql = UserSessions::getSql();

        $sqlString = 'SELECT ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.id') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.user_id') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.ip') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.ua') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.start') . ', ' .
            $sql->quoteId(DB_PREFIX . 'users.username') . ', ' .
            $sql->quoteId(DB_PREFIX . 'users.type_id') . ', ' .
            $sql->quoteId(DB_PREFIX . 'user_types.type') . ' FROM ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions') . ' LEFT JOIN ' .
            $sql->quoteId(DB_PREFIX . 'users') . ' ON ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.user_id') . ' = ' .
            $sql->quoteId(DB_PREFIX . 'users.id') . ' LEFT JOIN ' .
            $sql->quoteId(DB_PREFIX . 'user_types') . ' ON ' .
            $sql->quoteId(DB_PREFIX . 'users.type_id') . ' = ' .
            $sql->quoteId(DB_PREFIX . 'user_types.id') . ' ORDER BY ' .
            $sql->quoteId(DB_PREFIX . 'user_sessions.id') . ' ASC';

        $sessions = UserSessions::execute($sqlString);
        $this->data['sessions'] = $sessions->rows;
    }

}

