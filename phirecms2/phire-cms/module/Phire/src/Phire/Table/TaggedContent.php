<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class TaggedContent extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = array('tag_id', 'content_id');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

