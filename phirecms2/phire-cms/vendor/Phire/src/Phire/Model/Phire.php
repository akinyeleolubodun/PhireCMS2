<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

class Phire extends AbstractModel
{

    /**
     * Modules array
     */
    protected $modules = array();

    /**
     * Get content object
     *
     * @param  mixed   $id
     * @return \ArrayObject
     */
    public function getContent($id)
    {
        $contentValues = array();
        $content = (is_numeric($id)) ? Table\Content::findById($id) : Table\Content::findBy(array('uri' => $id));

        if (isset($content->id)) {
            $contentValues = $content->getValues();
            $contentValues = $this->filterContent(array_merge($contentValues, FieldValue::getAll($content->id)));
        }

        return new \ArrayObject($contentValues, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get category object
     *
     * @param  mixed   $id
     * @return mixed
     */
    public function getCategory($id)
    {
        $categoryValues = array();
        $category = (is_numeric($id)) ? Table\Categories::findById($id) : Table\Categories::findBy(array('uri' => $id));

        if (isset($category->id)) {
            $categoryValues = $category->getValues();
            $categoryValues = $this->filterContent(array_merge($categoryValues, FieldValue::getAll($category->id)));
        }

        return new \ArrayObject($categoryValues, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get template object
     *
     * @param  mixed   $id
     * @return mixed
     */
    public function getTemplate($id)
    {
        $templateValues = array();
        $template = (is_numeric($id)) ? Table\Templates::findById($id) : Table\Templates::findBy(array('name' => $id));

        if (isset($template->id)) {
            $templateValues = $template->getValues();
            $templateValues = array_merge($templateValues, FieldValue::getAll($template->id));
            $templateValues['template'] = Template::parse($templateValues['template'], $template->id);
        }

        return new \ArrayObject($templateValues, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get navigation object
     *
     * @param  mixed $name
     * @return mixed
     */
    public function getNavigation($name)
    {
        $nav = new Navigation();
        $navAry = $nav->getContentNav();

        return (isset($navAry[$name])) ? $navAry[$name] : null;
    }

    /**
     * Get content by date method
     *
     * @param  string  $from
     * @param  string  $to
     * @param  int     $limit
     * @return array
     */
    public function getContentByDate($from = null, $to = null, $limit = 5)
    {
        $from = (null === $from) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($from));

        if (null !== $to) {
            $to = date('Y-m-d H:i:s', strtotime($to));
        }

        $sql = Table\Content::getSql();
        $sql->select()
            ->where()->isNotNull('status')
                     ->lessThanOrEqualTo('published', ':published1');

        $params = array('published1' => $from);

        if (null !== $to) {
            $sql->select()
                ->where()->greaterThanOrEqualTo('published', ':published2');
            $params['published2'] = $to;
        }
        if ((int)$limit > 0) {
            $sql->select()->limit((int)$limit);
        }

        $sql->select()->orderBy('published', 'DESC');

        $content = Table\Content::execute($sql->render(true), $params);
        $results = $content->rows;

        foreach ($results as $key => $result) {
            if (\Phire\Model\Content::isAllowed($result)) {
                $fv = \Phire\Model\FieldValue::getAll($result->id, true);
                if (count($fv) > 0) {
                    foreach ($fv as $k => $v) {
                        $results[$key]->{$k} = $v;
                    }
                }
            } else {
                unset($results[$key]);
            }
        }

        return $results;
    }

    /**
     * Get content by category method
     *
     * @param  mixed   $cat
     * @param  string  $orderBy
     * @param  int     $limit
     * @return array
     */
    public function getContentByCategory($cat, $orderBy = 'id ASC', $limit = null)
    {
        if (!is_numeric($cat)) {
            $c = Table\Categories::findBy(array('title' => $cat));
        } else {
            $c = Table\Categories::findById($cat);
        }

        $contentAry = array();
        if (isset($c->id)) {
            $sql = Table\Content::getSql();
            $sql->select(array(
                0          => DB_PREFIX . 'content.id',
                1          => DB_PREFIX . 'content.site_id',
                2          => DB_PREFIX . 'content.type_id',
                3          => DB_PREFIX . 'content.parent_id',
                4          => DB_PREFIX . 'content.template',
                5          => DB_PREFIX . 'content.title',
                'uri'      => DB_PREFIX . 'content.uri',
                6          => DB_PREFIX . 'content.slug',
                7          => DB_PREFIX . 'content.feed',
                8          => DB_PREFIX . 'content.force_ssl',
                9          => DB_PREFIX . 'content.status',
                10         => DB_PREFIX . 'content.roles',
                11         => DB_PREFIX . 'content.created',
                12         => DB_PREFIX . 'content.updated',
                13         => DB_PREFIX . 'content.published',
                14         => DB_PREFIX . 'content.expired',
                15         => DB_PREFIX . 'content.created_by',
                16         => DB_PREFIX . 'content.updated_by',
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
                        $contentValues = $this->filterContent(array_merge($contentValues, FieldValue::getAll($cont->id, true)));
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
     * @return array
     */
    public function getChildCategories($cat, $limit = null)
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
                    $child = array_merge($child, FieldValue::getAll($child['id'], true));
                    $categoryAry[] = new \ArrayObject($child, \ArrayObject::ARRAY_AS_PROPS);
                }
            }
        }

        return $categoryAry;
    }

    /**
     * Check is a module is loaded
     *
     * @param  string $name
     * @return boolean
     */
    public function isLoaded($name)
    {
        return isset($this->modules[strtolower($name)]);
    }

    /**
     * Lazy load a module
     *
     * @param  string $name
     * @param  string $module
     * @return self
     */
    public function loadModule($name, $module)
    {
        $this->modules[strtolower($name)] = $module;
        return $this;
    }

    /**
     * Unload a module
     *
     * @param  string $name
     * @return self
     */
    public function unloadModule($name)
    {
        if (isset($this->modules[strtolower($name)])) {
            unset($this->modules[strtolower($name)]);
        }
        return $this;
    }

    /**
     * Get module model
     *
     * @param  string $name
     * @param  mixed  $args
     * @throws \Exception
     * @return mixed
     */
    public function module($name, $args = null)
    {
        $name = strtolower($name);
        if (!isset($this->modules[$name])) {
            throw new \Exception('That module has not been loaded.');
        }

        if (is_string($this->modules[$name])) {
            $class = $this->modules[$name];
            if (null !== $args) {
                if (!is_array($args)) {
                    $args = array($args);
                }
                $reflect = new \ReflectionClass($class);
                $result = $reflect->newInstanceArgs($args);
            } else {
                $result = new $class;
            }
            $this->modules[$name] = $result;
        }

        return $this->modules[$name];
    }

}

