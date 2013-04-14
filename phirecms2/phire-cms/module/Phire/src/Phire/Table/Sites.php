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
     * Get a site
     *
     * @return self
     */
    public static function getSite()
    {
        $record = new static();
        $sql = self::getSql();
        $db = self::getDb();

        if ($record->isPrepared()) {
            $domain = '?';
            $aliases = '?';
            switch ($sql->getDbType()) {
                case 3;
                    $domain = '$1';
                    $aliases = '$2';
                    break;
                case 4;
                    $domain = ':domain';
                    $aliases = ':aliases';
                    break;
            }
            $sql->select()->where()
                ->equalTo('domain', '?', 'OR')
                ->like('aliases', '?');

            return self::execute($sql->render(true), array($_SERVER['HTTP_HOST'], '%' . $_SERVER['HTTP_HOST'] . '%'));
        } else {
            $sql->select()->where()
                ->equalTo('domain', $db->adapter()->escape($_SERVER['HTTP_HOST']), 'OR')
                ->like('aliases', $db->adapter()->escape('%' . $_SERVER['HTTP_HOST'] . '%'));
            return self::query($sql->render(true));
        }
    }

}

