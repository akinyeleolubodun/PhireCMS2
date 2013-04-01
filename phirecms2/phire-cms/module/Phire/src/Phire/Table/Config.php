<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Config extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = 'setting';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

