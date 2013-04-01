<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class FieldValues extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = array('content_id', 'field_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

