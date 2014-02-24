<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Users extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'users';

    /**
     * @var   string
     */
    protected $primaryId = 'id';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Static method to get count of users
     *
     * @param  int $tid
     * @return int
     */
    public static function getCount($tid = null)
    {
        $sql = 'SELECT COUNT(*) AS total_users FROM ' . DB_PREFIX . 'users';
        if (null !== $tid) {
            $sql .= ' WHERE type_id = ' . (int)$tid;
        }

        return static::execute($sql)->total_users;
    }

}

