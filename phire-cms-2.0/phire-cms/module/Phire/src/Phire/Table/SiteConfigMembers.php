<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Db\Record;

class SiteConfigMembers extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = 'site_id';

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

}

