<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Sites extends Record
{

    /**
     * @var   string
     */
    protected $primaryId = 'id';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Get sites
     *
     * @return array
     */
    public static function getSites()
    {
        $sitesAry = array();

        $sites = static::findAll('id ASC');
        foreach ($sites->rows as $site) {
            $sitesAry[$site->id] = $site->domain;
        }

        return $sitesAry;
    }

}

