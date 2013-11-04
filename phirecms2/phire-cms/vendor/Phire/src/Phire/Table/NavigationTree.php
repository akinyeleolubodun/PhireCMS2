<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class NavigationTree extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'navigation_tree';

    /**
     * @var   string
     */
    protected $primaryId = array('navigation_id', 'content_id', 'category_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

