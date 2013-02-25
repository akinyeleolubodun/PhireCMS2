<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Site404s extends Record
{

    /**
     * @var   array
     */
    protected $primaryId = array('site_id', 'uri');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

