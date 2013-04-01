<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class SiteRelationships extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = array('id', 'site_id', 'relationship');

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}
