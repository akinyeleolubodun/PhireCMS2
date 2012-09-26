<?php
/**
 * @namespace 
 */
namespace Phire\Table;

use Pop\Record\Record;

class SiteConfigComments extends Record
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

