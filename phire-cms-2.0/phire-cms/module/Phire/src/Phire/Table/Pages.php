<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Db\Record;

class Pages extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = 'content_id';

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

