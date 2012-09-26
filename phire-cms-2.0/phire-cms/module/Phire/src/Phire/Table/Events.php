<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Record\Record;

class Events extends Record
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

