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

    /**
     * Get all navigation method
     *
     * @param  string  $sort
     * @param  string  $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $navigation = Table\Navigation::findAll($order['field'] . ' ' . $order['order']);

        if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\NavigationController', 'remove'))) {
            $removeCheckbox = '<input type="checkbox" name="remove_navigation[]" id="remove_navigation[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_navigation" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove'
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove',
                'style' => 'display: none;'
            );
        }

        $options = array(
            'form' => array(
                'id'      => 'navigation-remove-form',
                'action'  => BASE_PATH . APP_URI . '/structure/navigation/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'       => '<a href="' . BASE_PATH . APP_URI . '/structure/navigation?sort=id">#</a>',
                    'navigation' => '<a href="' . BASE_PATH . APP_URI . '/structure/navigation?sort=navigation">Navigation</a>',
                    'process'  => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'indent' => '        '
        );

        $navAry = array();
        foreach ($navigation->rows as $id => $nav) {
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\NavigationController', 'edit'))) {
                $nv = '<a href="' . BASE_PATH . APP_URI . '/structure/navigation/edit/' . $nav->id . '">' . $nav->navigation . '</a>';
            } else {
                $nv = $nav->navigation;
            }
            $navAry[] = array(
                'id' => $nav->id,
                'navigation' => $nv
            );
        }

        if (isset($navAry[0])) {
            $table = Html::encode($navAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            $this->data['table'] = $table;
        }
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
     * Remove navigation
     *
     * @param  array   $post
     * @param  boolean $isFields
     * @return void
     */
    public function remove(array $post, $isFields = false)
    {
        if (isset($post['remove_navigation'])) {
            foreach ($post['remove_navigation'] as $id) {
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
    }

    /**
     * Recursive method to get content children
     *
     * @param  array   $content
     * @param  int     $pid
     * @return array
     */
    protected function getContentChildren($content, $pid)
    {
        $children = array();
        foreach ($content as $c) {
            if ($c->parent_id == $pid) {
                $p = (array)$c;
                $p['uri'] = BASE_PATH . $c->uri;
                $p['href'] = $p['uri'];
                $p['name'] = $c->title;

                if (\Phire\Model\Content::isAllowed($c)) {
                    $p['children'] = $this->getContentChildren($content, $c->id);
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

}

