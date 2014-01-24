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
    protected $tableName = 'sites';

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
     * Static method to get current site root
     *
     * @return string
     */
    public static function getBasePath()
    {
        $site = static::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
        return (isset($site->id)) ? $site->base_path : BASE_PATH;
    }

}

