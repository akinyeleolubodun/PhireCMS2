<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class FieldsToModels extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'fields_to_models';

    /**
     * @var   string
     */
    protected $primaryId = array('field_id', 'model', 'type_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

