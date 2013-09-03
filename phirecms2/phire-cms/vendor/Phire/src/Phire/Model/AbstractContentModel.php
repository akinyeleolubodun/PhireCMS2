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
     * Recursive method to get content children
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
                    $p = (array)$c;
                    $p['uri'] = BASE_PATH . (isset($c->category) ? '/category' : null) . $c->uri;
                    $p['href'] = $p['uri'];
                    $p['name'] = (isset($c->category)) ? $c->category : $c->title;

                    if (($count) && ($this->config->category_totals)) {
                        $p['name'] .= ' (' . ((isset($c->num)) ? (int)$c->num : 0). ')';
                    }
                    $p['children'] = $this->getChildren($content, $c->id, $count);
                    $children[] = $p;
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
        $allContent = Table\Content::findAll('order ASC', array('status' => self::PUBLISHED));
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
                'parent' => array(
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
     * @param  mixed $content
     * @return void
     */
    protected function isAllowed($content)
    {
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
            $this->data['allowed'] = true;
            // Else, not allowed
        } else {
            $this->data['allowed'] = false;
        }

        // Check if the content is published, a draft or expired
        if (isset($content->title) && (null !== $content->status)) {
            $sess = Session::getInstance();
            if ((strtotime($content->published) >= time()) ||
                ((null !== $content->expired) && (strtotime($content->expired) <= time()))) {
                $this->data['allowed'] = false;
            } else if ((int)$content->status == self::UNPUBLISHED) {
                $this->data['allowed'] = false;
            } else if ((int)$content->status == self::DRAFT) {
                $this->data['allowed'] = (isset($sess->user) && (strtolower($sess->user->type) == 'user'));
            }
        }

        // Check is the site is live
        if (!$this->config->live) {
            $this->data['allowed'] = false;
        }

    }

}
