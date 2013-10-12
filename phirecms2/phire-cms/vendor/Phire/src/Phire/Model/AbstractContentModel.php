<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Filter\String;
use Pop\Nav\Nav;
use Pop\Web\Session;
use Phire\Table;

abstract class AbstractContentModel extends \Phire\Model\AbstractModel
{

    /**
     * Constant for unpublished
     */
    const UNPUBLISHED = 0;

    /**
     * Constant for draft
     */
    const DRAFT = 1;

    /**
     * Constant for published
     */
    const PUBLISHED = 2;

    /**
     * Image types regex
     *
     * @var   string
     */
    protected static $imageRegex = '/^.*\.(ai|eps|gif|jpe|jpg|jpeg|pdf|png|psd)$/i';

    /**
     * Method to get image regex
     *
     * @return string
     */
    public static function getImageRegex()
    {
        return self::$imageRegex;
    }

    /**
     * Recursive method to get content or category children
     *
     * @param array   $content
     * @param int     $pid
     * @param boolean $count
     * @return  array
     */
    protected function getChildren($content, $pid, $count = false)
    {
        $children = array();
        foreach ($content as $c) {
            if (($c->parent_id == $pid) &&
                (!isset($c->include) || (isset($c->include) && ($c->include)) ||
                (isset($c->include) && (!$c->include) && ($c->uri == '/')))) {
                if (isset($c->include) && (!$c->include) && ($c->uri == '/')) {
                    $children = $this->getChildren($content, $c->id, $count);
                } else {
                    // Get any content roles
                    $rolesAry = array();
                    if (isset($c->title)) {
                        $roles = Table\ContentToRoles::findAll(null, array('content_id' => $c->id));
                        foreach ($roles->rows as $role) {
                            $rolesAry[] = $role->role_id;
                        }
                    }

                    $p = (array)$c;
                    $p['uri'] = BASE_PATH . (isset($c->category) ? '/category' : null) . $c->uri;
                    $p['href'] = $p['uri'];
                    $p['name'] = (isset($c->category)) ? $c->category : $c->title;

                    if (($count) && ($c->total)) {
                        $p['name'] .= ' (' . ((isset($c->num)) ? (int)$c->num : 0). ')';
                    }
                    if (isset($c->category) || $this->isAllowed($c, true)) {
                        $p['children'] = $this->getChildren($content, $c->id, $count);
                        $children[] = $p;
                    }
                }
            }
        }

        return $children;
    }

    /**
     * Recursive method to get content breadcrumb
     *
     * @param  mixed $content
     * @return string
     */
    protected function getBreadcrumb($content)
    {
        $breadcrumb = (isset($content->category)) ? $content->category : $content->title;
        $pId = $content->parent_id;

        while ($pId != 0) {
            $contentClass = get_class($content);
            $content = $contentClass::findById($pId);
            if (isset($content->id)) {
                if (!(isset($content->include) && (!$content->include) && ($content->uri == '/'))) {
                    if (!isset($content->status) || (isset($content->status) && ($content->status == self::PUBLISHED))) {
                        $breadcrumb = '<a href="' . BASE_PATH . (isset($content->category) ? '/category' : null) . $content->uri . '">' .
                            (isset($content->category) ? $content->category : $content->title) . '</a> ' . $this->config->separator . ' ' . $breadcrumb;
                    }
                }
                $pId = $content->parent_id;
            }
        }

        return $breadcrumb;
    }

    /**
     * Method to get content navigation
     *
     * @param  mixed $content
     * @return void
     */
    protected function getNav($content = null)
    {
        // Get main navs
        $navs = Table\Navigation::findAll();

        foreach ($navs->rows as $nav) {
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
                $sql->select()->where()->equalTo(DB_PREFIX . 'content.status', self::PUBLISHED, 'AND');
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
                    $navChildren = $this->getChildren($allContent->rows, 0);
                    if (count($navChildren) > 0) {
                        $navName = str_replace('-', '_', String::slug($nav->navigation));
                        echo
                        $indent = (null !== $nav->spaces) ? str_repeat(' ', $nav->spaces) : '    ';
                        $newNav = new Nav($navChildren, $navConfig);
                        $newNav->nav()->setIndent($indent);
                        $this->data[$navName] = $newNav;
                    }
                }
            }
        }

        // Get breadcrumb
        if (null !== $content) {
            $this->data['breadcrumb'] = $this->getBreadcrumb($content);
        }

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
            $this->data['categories'] = $categories->rows;
            $navChildren = $this->getChildren($categories->rows, 0, true);
            if (count($navChildren) > 0) {
                $this->data['category_nav'] = new Nav($navChildren, $catConfig);
                $this->data['category_nav']->nav()->setIndent('    ');
            }
        }

        $this->data['get_category'] = function($cat, $fields = false) {
            $content = new \Phire\Model\Content();
            return $content->getByCategory($cat, $fields);
        };

        $this->data['get_categories'] = function($cat, $fields = false) {
            $category = new \Phire\Model\Category();
            return $category->getChildCategories($cat, $fields);
        };
    }

    /**
     * Set content by category method
     *
     * @param  mixed   $content
     * @param  boolean $isFields
     * @return array
     */
    public function setByCategory($content, $isFields = false)
    {
        $cats = array();
        preg_match_all('/\[\{category_.*\}\]/', $content, $cats);
        if (isset($cats[0]) && isset($cats[0][0])) {
            foreach ($cats[0] as $cat) {
                $c = str_replace('}]', '', substr($cat, (strpos($cat, '_') + 1)));
                $cont = $this->getByCategory($c, $isFields);
                $this->set('category_' . $c, $cont);
            }
        }

        $cats = array();
        preg_match_all('/\[\{categories_.*\}\]/', $content, $cats);
        if (isset($cats[0]) && isset($cats[0][0])) {
            foreach ($cats[0] as $cat) {
                $c = str_replace('}]', '', substr($cat, (strpos($cat, '_') + 1)));
                $category = new \Phire\Model\Category();
                $this->set('categories_' . $c, $category->getChildCategories($c, $isFields));
            }
        }
    }

    /**
     * Get content by category method
     *
     * @param  mixed   $cat
     * @param  boolean $isFields
     * @return array
     */
    public function getByCategory($cat, $isFields = false)
    {
        if (!is_numeric($cat)) {
            $c = Table\Categories::findBy(array('category' => $cat));
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
            $content = Table\Content::execute($sql->render(true), array('category_id' => $c->id));

            if (isset($content->rows[0])) {
                foreach ($content->rows as $cont) {
                    if ($this->isAllowed($cont, true)) {
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
     * Method to check is content object is allowed
     * and set model data accordingly
     *
     * @param  mixed   $content
     * @param  boolean $ret
     * @return mixed
     */
    protected function isAllowed($content, $ret = false)
    {
        $allowed = true;

        // Get any content roles
        $rolesAry = array();
        if (isset($content->title)) {
            $roles = Table\ContentToRoles::findAll(null, array('content_id' => $content->id));
            foreach ($roles->rows as $role) {
                $rolesAry[] = $role->role_id;
            }
            $this->data['roles'] = $rolesAry;
        }

        // If there are no roles, or the user's role is allowed
        if ((count($rolesAry) == 0) || ((count($rolesAry) > 0) && (isset($this->data['user'])) && in_array($this->data['user']['role_id'], $rolesAry))) {
            $allowed = true;
        // Else, not allowed
        } else {
            $allowed = false;
        }

        // Check if the content is published, a draft or expired
        if (isset($content->title) && (null !== $content->status)) {
            $sess = Session::getInstance();

            // If a regular URI type
            if (($content->type_uri == 1) && ((strtotime($content->published) >= time()) ||
                ((null !== $content->expired) && ($content->expired != '0000-00-00 00:00:00') && (strtotime($content->expired) <= time())))) {
                $allowed = false;
            // Else, if an event type
            } else if ($content->type_uri == 2) {
                // If no end date
                if ((null === $content->expired) || ($content->expired == '0000-00-00 00:00:00')) {
                    if (strtotime($content->published) < time()) {
                        $allowed = false;
                    }
                } else {
                    if (strtotime($content->expired) <= time()) {
                        $allowed = false;
                    }
                }
            }

            // Published status override
            if ((int)$content->status == self::UNPUBLISHED) {
                $allowed = false;
            } else if ((int)$content->status == self::DRAFT) {
                $allowed = (isset($sess->user) && (strtolower($sess->user->type) == 'user'));
            }
        }

        // Check is the site is live
        if (!$this->config->live) {
            $allowed = false;
        }

        if (!$ret) {
            $this->data['allowed'] = $allowed;
        } else {
            return $allowed;
        }
    }

}
