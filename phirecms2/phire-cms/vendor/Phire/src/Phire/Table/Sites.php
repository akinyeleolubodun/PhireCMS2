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

    /**
     * Static method to get current site
     *
     * @param  int $id
     * @return \ArrayObject
     */
    public static function getSite($id = null)
    {
        $siteAry = array(
            'domain'        => null,
            'document_root' => null,
            'base_path'     => null
        );

        if (null !== $id) {
            $site = ((int)$id > 0) ? static::findById((int)$id) : static::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
            if (isset($site->id)) {
                $siteAry['domain']        = $site->domain;
                $siteAry['document_root'] = $site->document_root;
                $siteAry['base_path']     = $site->base_path;
            }
        }

        if (null === $siteAry['domain']) {
            $siteAry['domain']        = $_SERVER['HTTP_HOST'];
            $siteAry['document_root'] = $_SERVER['DOCUMENT_ROOT'];
            $siteAry['base_path']     = BASE_PATH;
        }

        return new \ArrayObject($siteAry, \ArrayObject::ARRAY_AS_PROPS);
    }

}

