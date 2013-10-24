<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\Filter\String;
use Pop\Nav\Nav;
use Pop\Web\Session;
use Phire\Table;

class Navigation extends AbstractModel
{

    protected $trackNav = array();

    /**
     * Get all navigation method
     *
     * @param  string  $sort
     * @param  string  $page
     * @return array
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $navigation = Table\Navigation::findAll($order['field'] . ' ' . $order['order']);

        $navAry = array();

        foreach ($navigation->rows as $nav) {

            $sql = Table\ContentToNavigation::getSql();
            $sql->select(array(
                'content_id',
                'navigation_id',
                'order',
                'id',
                'parent_id',
                'title',
                'uri'
            ))->where()->equalTo('navigation_id', $nav->id);
            $sql->select()->join(DB_PREFIX . 'content', array('content_id', 'id'), 'LEFT JOIN');
            $sql->select()->orderBy('order', 'ASC');

            $content = Table\ContentToNavigation::execute($sql->render(true));
            $navChildren = array();
            $parents = array('0' => '----');
            if (isset($content->rows[0])) {
                foreach ($content->rows as $c) {
                    $parents[$c->id] = $c->title;
                }
                $this->trackNav = array();
                $navChildren = $this->getContentChildren($content->rows, null, true);
                $newChildren = array();
                foreach ($content->rows as $c) {
                    if (!in_array($c->id, $this->trackNav)) {
                        $newChildren = array_merge($newChildren, $this->getContentChildren($content->rows, $c->parent_id, true));
                    }
                }

                $navChildren = array_merge($newChildren, $navChildren);
            }

            $navAry[] = array(
                'nav'      => $nav,
                'parents'  => $parents,
                'children' => $this->getNavChildren($navChildren, array())
            );
        }

        return $navAry;
    }

    /**
     * Method to get all content navigation
     *
     * @return array
     */
    public function getContentNav()
    {
        // Get main navs
        $navigations = Table\Navigation::findAll();
        $navs = array();

        foreach ($navigations->rows as $nav) {
            $sql = Table\Content::getSql();
            $sql->select(array(
                DB_PREFIX . 'content.id',
                DB_PREFIX . 'content.type_id',
                DB_PREFIX . 'content.parent_id',
                DB_PREFIX . 'content.template',
                DB_PREFIX . 'content.title',
                DB_PREFIX . 'content.uri',
                DB_PREFIX . 'content.slug',
                DB_PREFIX . 'content.feed',
                DB_PREFIX . 'content.force_ssl',
                DB_PREFIX . 'content.status',
                DB_PREFIX . 'content.created',
                DB_PREFIX . 'content.updated',
                DB_PREFIX . 'content.published',
                DB_PREFIX . 'content.expired',
                DB_PREFIX . 'content.created_by',
                DB_PREFIX . 'content.updated_by',
                'type_uri' => DB_PREFIX . 'content_types.uri',
                DB_PREFIX . 'content_to_navigation.navigation_id',
                DB_PREFIX . 'content_to_navigation.order',
            ));

            // If it's a draft and a user is logged in
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth())) {
                $sql->select()->where()->notEqualTo(DB_PREFIX . 'content.status', 0, 'AND');
            } else {
                $sql->select()->where()->equalTo(DB_PREFIX . 'content.status', \Phire\Model\Content::PUBLISHED, 'AND');
            }

            $sql->select()->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN');
            $sql->select()->join(DB_PREFIX . 'content_to_navigation', array('id', 'content_id'), 'LEFT JOIN');
            $sql->select()->where()->equalTo(DB_PREFIX . 'content_to_navigation.navigation_id', $nav->id, 'AND');

            $sql->select()->orderBy(DB_PREFIX . 'content_to_navigation.order', 'ASC');

            $allContent = Table\Content::execute($sql->render(true));

            if (isset($allContent->rows[0])) {
                $top = array(
                    'node' => (null !== $nav->top_node) ? $nav->top_node : 'ul',
                    'id'   => (null !== $nav->top_id) ? $nav->top_id : String::slug($nav->navigation)
                );
                $parent = array();
                $child  = array();

                if (null !== $nav->top_class) {
                    $top['class'] = $nav->top_class;
                }
                if (null !== $nav->top_attributes) {
                    $attribs = array();
                    $attsAry = explode(' ', $nav->top_attributes);
                    foreach ($attsAry as $att) {
                        $a = explode('=', $att);
                        if (isset($a[0]) && isset($a[1])) {
                            $attribs[trim($a[0])] = str_replace('"', '', trim($a[1]));
                        }
                    }
                    $top['attributes'] = $attribs;
                }

                if (null !== $nav->parent_node) {
                    $parent['node'] = $nav->parent_node;
                }
                if (null !== $nav->parent_id) {
                    $parent['id'] = $nav->parent_id;
                }
                if (null !== $nav->parent_class) {
                    $parent['class'] = $nav->parent_class;
                }
                if (null !== $nav->parent_attributes) {
                    $attribs = array();
                    $attsAry = explode(' ', $nav->parent_attributes);
                    foreach ($attsAry as $att) {
                        $a = explode('=', $att);
                        if (isset($a[0]) && isset($a[1])) {
                            $attribs[trim($a[0])] = str_replace('"', '', trim($a[1]));
                        }
                    }
                    $parent['attributes'] = $attribs;
                }

                if (null !== $nav->child_node) {
                    $child['node'] = $nav->child_node;
                }
                if (null !== $nav->child_id) {
                    $child['id'] = $nav->child_id;
                }
                if (null !== $nav->child_class) {
                    $child['class'] = $nav->child_class;
                }
                if (null !== $nav->child_attributes) {
                    $attribs = array();
                    $attsAry = explode(' ', $nav->child_attributes);
                    foreach ($attsAry as $att) {
                        $a = explode('=', $att);
                        if (isset($a[0]) && isset($a[1])) {
                            $attribs[trim($a[0])] = str_replace('"', '', trim($a[1]));
                        }
                    }
                    $child['attributes'] = $attribs;
                }

                $on  = (null !== $nav->on_class) ? $nav->on_class : null;
                $off = (null !== $nav->off_class) ? $nav->off_class : null;

                $navConfig = array(
                    'top'    => $top,
                    'parent' => $parent,
                    'child'  => $child,
                    'on'     => $on,
                    'off'    => $off
                );

                if (isset($allContent->rows[0])) {
                    $navChildren = $this->getContentChildren($allContent->rows, 0);
                    if (count($navChildren) > 0) {
                        $navName = str_replace('-', '_', String::slug($nav->navigation));
                        $indent = (null !== $nav->spaces) ? str_repeat(' ', $nav->spaces) : '    ';
                        $newNav = new Nav($navChildren, $navConfig);
                        $newNav->nav()->setIndent($indent);
                        $navs[$navName] = $newNav;
                    }
                }
            }
        }

        return $navs;
    }

    /**
     * Method to get category navigation
     *
     * @return mixed
     */
    public function getCategoryNav()
    {
        $nav = null;
        $categories = Table\Categories::getCategoriesWithCount();
        if (isset($categories->rows[0])) {
            // Get category nav
            $catConfig = array(
                'top' => array(
                    'id'    => 'category-nav'
                ),
                'parent' => array(
                    'class' => 'category-nav-level'
                ),
                'on' => 'category-nav-on'
            );
            $navChildren = $this->getCategoryChildren($categories->rows, 0, true);
            if (count($navChildren) > 0) {
                $nav = new Nav($navChildren, $catConfig);
                $nav->nav()->setIndent('    ');
            }
        }

        return $nav;
    }

    /**
     * Get navigation by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $navigation = Table\Navigation::findById($id);

        if (isset($navigation->id)) {
            $navigationValues = $navigation->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $navigationValues = array_merge($navigationValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $navigationValues);
        }
    }

    /**
     * Save navigation
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $navigation = new Table\Navigation(array(
            'navigation'        => $fields['navigation'],
            'spaces'            => (($fields['spaces'] != '') ? (int)$fields['spaces'] : null),
            'top_node'          => (($fields['top_node'] != '') ? $fields['top_node'] : null),
            'top_id'            => (($fields['top_id'] != '') ? $fields['top_id'] : null),
            'top_class'         => (($fields['top_class'] != '') ? $fields['top_class'] : null),
            'top_attributes'    => (($fields['top_attributes'] != '') ? $fields['top_attributes'] : null),
            'parent_node'       => (($fields['parent_node'] != '') ? $fields['parent_node'] : null),
            'parent_id'         => (($fields['parent_id'] != '') ? $fields['parent_id'] : null),
            'parent_class'      => (($fields['parent_class'] != '') ? $fields['parent_class'] : null),
            'parent_attributes' => (($fields['parent_attributes'] != '') ? $fields['parent_attributes'] : null),
            'child_node'        => (($fields['child_node'] != '') ? $fields['child_node'] : null),
            'child_id'          => (($fields['child_id'] != '') ? $fields['child_id'] : null),
            'child_class'       => (($fields['child_class'] != '') ? $fields['child_class'] : null),
            'child_attributes'  => (($fields['child_attributes'] != '') ? $fields['child_attributes'] : null),
            'on_class'          => (($fields['on_class'] != '') ? $fields['on_class'] : null),
            'off_class'         => (($fields['off_class'] != '') ? $fields['off_class'] : null)
        ));

        $navigation->save();
        $this->data['id'] = $navigation->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $navigation->id);
        }
    }

    /**
     * Update navigation
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();
        $navigation = Table\Navigation::findById($fields['id']);
        $navigation->navigation  = $fields['navigation'];
        $navigation->spaces            = (($fields['spaces'] != '') ? (int)$fields['spaces'] : null);
        $navigation->top_node          = (($fields['top_node'] != '') ? $fields['top_node'] : null);
        $navigation->top_id            = (($fields['top_id'] != '') ? $fields['top_id'] : null);
        $navigation->top_class         = (($fields['top_class'] != '') ? $fields['top_class'] : null);
        $navigation->top_attributes    = (($fields['top_attributes'] != '') ? $fields['top_attributes'] : null);
        $navigation->parent_node       = (($fields['parent_node'] != '') ? $fields['parent_node'] : null);
        $navigation->parent_id         = (($fields['parent_id'] != '') ? $fields['parent_id'] : null);
        $navigation->parent_class      = (($fields['parent_class'] != '') ? $fields['parent_class'] : null);
        $navigation->parent_attributes = (($fields['parent_attributes'] != '') ? $fields['parent_attributes'] : null);
        $navigation->child_node        = (($fields['child_node'] != '') ? $fields['child_node'] : null);
        $navigation->child_id          = (($fields['child_id'] != '') ? $fields['child_id'] : null);
        $navigation->child_class       = (($fields['child_class'] != '') ? $fields['child_class'] : null);
        $navigation->child_attributes  = (($fields['child_attributes'] != '') ? $fields['child_attributes'] : null);
        $navigation->on_class          = (($fields['on_class'] != '') ? $fields['on_class'] : null);
        $navigation->off_class         = (($fields['off_class'] != '') ? $fields['off_class'] : null);
        $navigation->update();

        $this->data['id'] = $navigation->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $navigation->id);
        }
    }

    /**
     * Process navigation
     *
     * @param  array   $post
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function process(array $post, $id, $isFields = false)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'navigation_order_') !== false) {
                $key = str_replace('navigation_order_', '', $key);
                $ids = explode('_', $key);
                $navId = $ids[0];
                $contentId = $ids[1];
                $content2Nav = Table\ContentToNavigation::findById(array($contentId, $navId));
                if (isset($content2Nav->content_id)) {
                    $content2Nav->order = (int)$value;
                    $content2Nav->update();
                }
            } else if (strpos($key, 'parent_id_') !== false) {
                $id = str_replace('parent_id_', '', $key);
                $content = Table\Content::findById($id);
                if (isset($content->id)) {
                    $pId = ((int)$value == 0) ? null : (int)$value;
                    $content->parent_id = $pId;
                    $content->update();
                }
            }
        }

        if (isset($post['rm_nav'])) {
            $navigation = Table\Navigation::findById($id);
            if (isset($navigation->id)) {
                $navigation->delete();
            }
            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                \Fields\Model\FieldValue::remove($id);
            }
        }
    }

    /**
     * Recursive method to get content children
     *
     * @param  array   $content
     * @param  int     $pid
     * @param  boolean $override
     * @return array
     */
    protected function getContentChildren($content, $pid, $override = false)
    {
        $children = array();
        foreach ($content as $c) {
            if ($c->parent_id == $pid) {
                if (!in_array($c->id, $this->trackNav)) {
                    $this->trackNav[] = $c->id;
                }
                $p = (array)$c;
                $p['uri'] = BASE_PATH . $c->uri;
                $p['href'] = $p['uri'];
                $p['name'] = $c->title;

                if (($override) || (\Phire\Model\Content::isAllowed($c))) {
                    $p['children'] = $this->getContentChildren($content, $c->id, $override);
                    $children[] = $p;
                }
            }
        }

        return $children;
    }

    /**
     * Recursive method to get category children
     *
     * @param array   $category
     * @param int     $pid
     * @param boolean $count
     * @return  array
     */
    protected function getCategoryChildren($category, $pid, $count = false)
    {
        $children = array();
        foreach ($category as $c) {
            if ($c->parent_id == $pid) {
                $p = (array)$c;
                $p['uri'] = BASE_PATH . '/category'  . $c->uri;
                $p['href'] = $p['uri'];
                $p['name'] = $c->title;

                if (($count) && ($c->total)) {
                    $p['name'] .= ' (' . ((isset($c->num)) ? (int)$c->num : 0). ')';
                }

                $p['children'] = $this->getCategoryChildren($category, $c->id, $count);
                $children[] = $p;
            }
        }

        return $children;
    }

    /**
     * Recursive method to get category children
     *
     * @param array $children
     * @param array $set
     * @param int   $depth
     * @return array
     */
    protected function getNavChildren($children, $set, $depth = 0) {
        foreach ($children as $nav) {
            $set[] = array(
                'title'         => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '&gt; ' . $nav['title'],
                'content_id'    => $nav['content_id'],
                'parent_id'     => $nav['parent_id'],
                'navigation_id' => $nav['navigation_id'],
                'order'         => $nav['order'],
                'children'      => $this->children($nav['content_id'])
            );
            if (count($nav['children']) > 0) {
                $set = $this->getNavChildren($nav['children'], $set, ($depth + 1));
            }
        }

        return $set;
    }

    /**
     * Recursive method to get children of the content object
     *
     * @param  int   $pid
     * @param  array $children
     * @param  int   $depth
     * @return array
     */
    protected function children($pid, $children = array(), $depth = 0)
    {
        $c = Table\Content::findBy(array('parent_id' => $pid));

        if (isset($c->rows[0])) {
            foreach ($c->rows as $child) {
                $children[] = $child->id;
                $c = Table\Content::findBy(array('parent_id' => $child->id));
                if (isset($c->rows[0])) {
                    $d = $depth + 1;
                    $children = $this->children($child->id, $children, $d);
                }
            }
        }

        return $children;
    }

}

