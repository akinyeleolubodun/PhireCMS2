<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class Categories extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'categories';

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
     * Static method to get categories with model count
     *
     * @return self
     */
    public static function getCategoriesWithCount()
    {
        $site = Sites::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
        $siteId = (isset($site->id)) ? $site->id : '0';

        // Create SQL object and get first SQL result set of
        // content to category where content object is published or a file
        $firstSql = \Phire\Table\ContentToCategories::getSql();
        $firstSql->select(array(
            'content_id',
            'category_id',
            'site_id',
            'status'
        ))->join(DB_PREFIX . 'content', array('content_id', 'id'), 'LEFT JOIN')
          ->where()->isNull('status', 'OR')
                   ->equalTo('status', 2, 'OR')
                   ->equalTo('site_id', $siteId);

        $firstSql->setAlias('content_live');

        // Create SQL object and get second result set of the
        // actual count of content objects to categories, using
        // the first SQL object as a sub-select
        $secondSql = \Phire\Table\ContentToCategories::getSql();
        $secondSql->select(array(
            0     => 'category_id',
            'num' => 'COUNT(*)'
        ))->groupBy('category_id');

        $secondSql->setAlias('cat_count');
        $secondSql->setTable($firstSql);

        // Create SQL object to get the category/content data
        // using the the nested sub-selects for the JOIN
        $catSql = static::getSql();

        $catSql->select(array(
            DB_PREFIX . 'categories.id',
            DB_PREFIX . 'categories.parent_id',
            DB_PREFIX . 'categories.title',
            DB_PREFIX . 'categories.uri',
            DB_PREFIX . 'categories.slug',
            DB_PREFIX . 'categories.order',
            DB_PREFIX . 'categories.total',
            'cat_count.num'
        ))->join($secondSql, array('id', 'category_id'), 'LEFT JOIN')
          ->orderBy('order', 'ASC');

        return static::execute($catSql->render(true));
    }
}

