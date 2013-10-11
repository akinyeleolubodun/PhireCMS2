<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class ContentToNavigation extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'content_to_navigation';

    /**
     * @var   string
     */
    protected $primaryId = array('content_id', 'navigation_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

