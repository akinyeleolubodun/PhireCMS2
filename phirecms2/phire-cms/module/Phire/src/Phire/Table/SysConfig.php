<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Db\Record;

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

