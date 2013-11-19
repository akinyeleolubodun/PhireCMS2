<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class UserPermissions extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'user_permissions';

    /**
     * @var   string
     */
    protected $primaryId = array('role_id', 'resource', 'permission');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

