<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class ContentToRoles extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'content_to_roles';

    /**
     * @var   string
     */
    protected $primaryId = array('content_id', 'role_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

