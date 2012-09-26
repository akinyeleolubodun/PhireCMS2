<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Record\Record;

class SysConfig extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = 'setting';

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

