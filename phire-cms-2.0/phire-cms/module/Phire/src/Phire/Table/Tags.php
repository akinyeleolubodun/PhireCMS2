<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Record\Record;

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

