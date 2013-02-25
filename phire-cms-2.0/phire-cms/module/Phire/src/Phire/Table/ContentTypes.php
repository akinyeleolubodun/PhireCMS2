<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Db\Record;

class ContentTypes extends Record
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

