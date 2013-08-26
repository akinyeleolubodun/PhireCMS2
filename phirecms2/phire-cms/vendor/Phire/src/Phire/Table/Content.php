<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Content extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'content';

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
     * Static method to get content by date range
     *
     * @param  array $date
     * @return self
     */
    public static function findByDate($date)
    {
        $dateAry = explode('/', $date['match']);

        if (count($dateAry) == 3) {
            $start = $dateAry[0] . '-' . $dateAry[1] . '-' . $dateAry[2] . ' 00:00:00';
            $end = $dateAry[0] . '-' . $dateAry[1] . '-' . $dateAry[2] . ' 23:59:59';
        } else if (count($dateAry) == 2) {
            $start = $dateAry[0] . '-' . $dateAry[1] . '-01 00:00:00';
            $end = $dateAry[0] . '-' . $dateAry[1] . '-' . date('t', strtotime($dateAry[0] . '-' . $dateAry[1] . '-01')) . ' 23:59:59';
        } else {
            $start = $dateAry[0] . '-01-01 00:00:00';
            $end = $dateAry[0] . '-12-31 23:59:59';
        }

        // Create SQL object and build SQL statement
        $sql = static::getSql();

        // Get the correct placeholder
        if ($sql->getDbType() == \Pop\Db\Sql::PGSQL) {
            $p1 = '$1';
            $p2 = '$2';
            $p3 = '$3';
            $p4 = '$4';
        } else if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
            $p1 = ':published1';
            $p2 = ':published2';
            $p3 = ':uri';
            $p4 = ':status';
        } else {
            $p1 = '?';
            $p2 = '?';
            $p3 = '?';
            $p4 = '?';
        }

        $sql->select()
            ->where()
            ->greaterThanOrEqualTo('published', $p1)
            ->lessThanOrEqualTo('published', $p2);

        // If there is a content URI
        if (!empty($date['uri'])) {
            $sql->select()->where()->equalTo('uri', $p3);
            $sql->select()->where()->equalTo('status', $p4);
            $content = static::execute($sql->render(true), array('published' => array($start, $end), 'uri' => $date['uri'], 'status' => 2));
        } else {
            $p4 = ($p4 == '$4') ? '$3' : $p4;
            $sql->select()->where()->equalTo('status', $p4);
            $content = static::execute($sql->render(true), array('published' => array($start, $end), 'status' => 2));
        }

        return $content;
    }

}

