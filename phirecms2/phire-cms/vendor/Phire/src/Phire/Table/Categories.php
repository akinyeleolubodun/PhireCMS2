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
    protected $tableName = 'content_categories';

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
        // Create SQL object and get first SQL result set of
        // content to category where content object is published or a file
        $firstSql = \Phire\Table\ContentToCategories::getSql();
        $firstSql->select(array(
            'content_id',
            'category_id',
            'status'
        ))->join(DB_PREFIX . 'content', array('content_id', 'id'), 'LEFT JOIN')
          ->where()->isNull('status', 'OR')->equalTo('status', 2, 'OR');

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
            DB_PREFIX . 'content_categories.id',
            DB_PREFIX . 'content_categories.parent_id',
            DB_PREFIX . 'content_categories.title',
            DB_PREFIX . 'content_categories.uri',
            DB_PREFIX . 'content_categories.slug',
            DB_PREFIX . 'content_categories.order',
            DB_PREFIX . 'content_categories.total',
            'cat_count.num'
        ))->join($secondSql, array('id', 'category_id'), 'LEFT JOIN')
          ->orderBy('order', 'ASC');

        return static::execute($catSql->render(true));
    }
}

