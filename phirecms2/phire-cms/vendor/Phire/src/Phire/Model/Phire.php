<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

class Phire extends AbstractModel
{

    /**
     * Execute a custom SQL query
     *
     * @param  string  $sql
     * @param  array   $params
     * @param  boolean $isFields
     * @return mixed
     */
    public function db($sql, $params = null, $isFields = false)
    {

    }

    /**
     * Get content object
     *
     * @param  mixed   $content
     * @param  boolean $isFields
     * @return mixed
     */
    public function getContent($content, $isFields = false)
    {

    }

    /**
     * Get category object
     *
     * @param  mixed   $category
     * @param  boolean $isFields
     * @return mixed
     */
    public function getCategory($category, $isFields = false)
    {

    }

    /**
     * Get template object
     *
     * @param  mixed   $template
     * @param  boolean $isFields
     * @return mixed
     */
    public function getTemplate($template, $isFields = false)
    {

    }

    /**
     * Get navigation object
     *
     * @param  mixed   $navigation
     * @param  boolean $isFields
     * @return mixed
     */
    public function getNavigation($navigation, $isFields = false)
    {

    }

    /**
     * Get user object
     *
     * @param  mixed   $user
     * @param  boolean $isFields
     * @return mixed
     */
    public function getUser($user, $isFields = false)
    {

    }

    /**
     * Get content by category method
     *
     * @param  mixed   $cat
     * @param  string  $orderBy
     * @param  int     $limit
     * @param  boolean $isFields
     * @return array
     */
    public function getContentByCategory($cat, $orderBy = 'id ASC', $limit = null, $isFields = false)
    {
        if (!is_numeric($cat)) {
            echo 123;
            $c = Table\Categories::findBy(array('title' => $cat));
        } else {
            $c = Table\Categories::findById($cat);
        }

        $contentAry = array();
        if (isset($c->id)) {
            $sql = Table\Content::getSql();
            $sql->select(array(
                0          => DB_PREFIX . 'content.id',
                1          => DB_PREFIX . 'content.type_id',
                2          => DB_PREFIX . 'content.parent_id',
                3          => DB_PREFIX . 'content.template',
                4          => DB_PREFIX . 'content.title',
                'uri'      => DB_PREFIX . 'content.uri',
                6          => DB_PREFIX . 'content.slug',
                9          => DB_PREFIX . 'content.feed',
                10         => DB_PREFIX . 'content.force_ssl',
                11         => DB_PREFIX . 'content.status',
                12         => DB_PREFIX . 'content.created',
                13         => DB_PREFIX . 'content.updated',
                14         => DB_PREFIX . 'content.published',
                15         => DB_PREFIX . 'content.expired',
                16         => DB_PREFIX . 'content.created_by',
                17         => DB_PREFIX . 'content.updated_by',
                'type_uri' => DB_PREFIX . 'content_types.uri'
            ));

            $sql->select()->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN');
            $sql->select()->join(DB_PREFIX . 'content_to_categories', array('id', 'content_id'), 'LEFT JOIN');
            $sql->select()->where()->equalTo(DB_PREFIX . 'content_to_categories.category_id', ':category_id');
            $order = explode(' ', $orderBy);
            $sql->select()->orderBy($order[0], $order[1]);
            if (null !== $limit) {
                $sql->select()->limit((int)$limit);
            }

            $content = Table\Content::execute($sql->render(true), array('category_id' => $c->id));

            if (isset($content->rows[0])) {
                foreach ($content->rows as $cont) {
                    if (\Phire\Model\Content::isAllowed($cont)) {
                        $contentValues = (array)$cont;

                        // If the Fields module is installed, and if there are fields for this form/model
                        if ($isFields) {
                            $contentValues = array_merge($contentValues, \Fields\Model\FieldValue::getAll($cont->id, true));
                        }
                        $contentAry[] = new \ArrayObject($contentValues, \ArrayObject::ARRAY_AS_PROPS);
                    }
                }
            }
        }

        return $contentAry;
    }

    /**
     * Get child categories
     *
     * @param  mixed   $cat
     * @param  int     $limit
     * @param  boolean $isFields
     * @return array
     */
    public function getChildCategories($cat, $limit = null, $isFields = false)
    {
        if (!is_numeric($cat)) {
            $c = Table\Categories::findBy(array('title' => $cat));
        } else {
            $c = Table\Categories::findById($cat);
        }

        $categoryAry = array();
        if (isset($c->id)) {
            $limit = (null !== $limit) ? (int)$limit : null;
            $children = Table\Categories::findBy(array('parent_id' => $c->id), 'order ASC', $limit);
            if (isset($children->rows[0])) {
                foreach ($children->rows as $child) {
                    $child = (array)$child;
                    if ($isFields) {
                        $child = array_merge($child, \Fields\Model\FieldValue::getAll($child['id'], true));
                    }
                    $categoryAry[] = new \ArrayObject($child, \ArrayObject::ARRAY_AS_PROPS);
                }
            }
        }

        return $categoryAry;
    }

}

