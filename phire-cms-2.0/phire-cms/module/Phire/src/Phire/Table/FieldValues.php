<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Record\Record;

class FieldValues extends Record
{

    /**
     * @var   array
     */
    protected $primaryId = array('field_id','object_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

