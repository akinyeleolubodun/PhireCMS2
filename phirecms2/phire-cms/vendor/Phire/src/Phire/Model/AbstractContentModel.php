<?php
/**
 * @namespace
 */
namespace Phire\Model;

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

                    if (($count) && ($this->config->category_totals)) {
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
        // Get main nav
        $sql = Table\Content::getSql();
        $sql->select(array(
            DB_PREFIX . 'content.id',
            DB_PREFIX . 'content.type_id',
            DB_PREFIX . 'content.parent_id',
            DB_PREFIX . 'content.template',
            DB_PREFIX . 'content.title',
            DB_PREFIX . 'content.uri',
            DB_PREFIX . 'content.slug',
            DB_PREFIX . 'content.order',
            DB_PREFIX . 'content.include',
            DB_PREFIX . 'content.feed',
            DB_PREFIX . 'content.force_ssl',
            DB_PREFIX . 'content.status',
            DB_PREFIX . 'content.created',
            DB_PREFIX . 'content.updated',
            DB_PREFIX . 'content.published',
            DB_PREFIX . 'content.expired',
            DB_PREFIX . 'content.created_by',
            DB_PREFIX . 'content.updated_by',
            'type_uri' => DB_PREFIX . 'content_types.uri'
        ))->where()->equalTo(DB_PREFIX . 'content.status', self::PUBLISHED);

        // If it's a draft and a user is logged in
        if (isset($this->data['acl']) && ($this->data['acl']->isAuth())) {
            $sql->select()->where()->equalTo(DB_PREFIX . 'content.status', self::DRAFT, 'OR');
        }

        $sql->select()->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN');
        $sql->select()->orderBy(DB_PREFIX . 'content.order', 'ASC');

        $allContent = Table\Content::execute($sql->render(true));

        if (isset($allContent->rows[0])) {
            $navConfig = array(
                'top' => array(
                    'id'    => 'main-nav'
                ),
                'parent' => array(
                    'class' => 'main-nav-level'
                )
            );
            if (isset($allContent->rows[0])) {
                $navChildren = $this->getChildren($allContent->rows, 0);
                if (count($navChildren) > 0) {
                    $this->data['nav'] = new Nav($navChildren, $navConfig);
                    $this->data['nav']->nav()->setIndent('    ');
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
                    'id'    => 'cat-nav'
                ),
                'parent' //if (isset($c->category) || (count($rolesAry) == 0) || ((count($rolesAry) > 0) && (isset($this->data['user'])) && in_array($this->data['user']['role_id'], $rolesAry))) {
                    => array(
                    'class' => 'cat-nav-level'
                )
            );
            $this->data['categories'] = $categories->rows;
            $navChildren = $this->getChildren($categories->rows, 0, true);
            if (count($navChildren) > 0) {
                $this->data['categoryNav'] = new Nav($navChildren, $catConfig);
                $this->data['categoryNav']->nav()->setIndent('    ');
            }
        }
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
