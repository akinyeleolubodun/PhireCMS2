<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class ContentToCategories extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'content_to_categories';

    /**
     * @var   string
     */
    protected $primaryId = array('content_id', 'category_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;


}

