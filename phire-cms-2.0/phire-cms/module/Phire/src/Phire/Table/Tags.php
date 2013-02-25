<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Db\Record;

class Tags extends Record
{

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

}

