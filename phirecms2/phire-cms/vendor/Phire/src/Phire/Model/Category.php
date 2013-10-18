<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class Category extends AbstractModel
{

    /**
     * @var   array
     */
    protected $categories = array(0 => '----');

    /**
     * Get categories array
     *
     * @return array
     */
    public function getCategoryArray()
    {
        return $this->categories;
    }

    /**
     * Get all categories method
     *
     * @param  string  $sort
     * @param  string  $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $categories = Table\Categories::findAll($order['field'] . ' ' . $order['order']);
        $this->getCategories($this->getChildren($categories->rows, 0));

        if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\CategoriesController', 'remove'))) {
            $removeCheckbox = '<input type="checkbox" name="remove_categories[]" id="remove_categories[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_categories" />';
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
                'id'      => 'category-remove-form',
                'action'  => BASE_PATH . APP_URI . '/structure/categories/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'       => '<a href="' . BASE_PATH . APP_URI . '/structure/categories?sort=id">#</a>',
                    'title'    => '<a href="' . BASE_PATH . APP_URI . '/structure/categories?sort=title">Title</a>',
                    'process'  => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'indent' => '        '
        );

        $catAry = array();
        $cats = $this->categories;
        unset($cats[0]);

        foreach ($cats as $id => $name) {
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\CategoriesController', 'edit'))) {
                $name = '<a href="' . BASE_PATH . APP_URI . '/structure/categories/edit/' . $id . '">' . $name . '</a>';
            }
            $catAry[] = array(
                'id'    => $id,
                'title' => $name
            );
        }

        if (isset($catAry[0])) {
            $table = Html::encode($catAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\CategoriesController', 'edit'))) {
                $tableLines = explode(PHP_EOL, $table);

                // Clean up the table
                foreach ($tableLines as $key => $value) {
                    if (strpos($value, '">&') !== false) {
                        $str = substr($value, (strpos($value, '">&') + 2));
                        $str = substr($str, 0, (strpos($str, ' ') + 1));
                        $value = str_replace($str, '', $value);
                        $tableLines[$key] = str_replace('<td><a', '<td>' . $str . '<a', $value);
                    }
                }
                $table = implode(PHP_EOL, $tableLines);
            }

            $this->data['table'] = $table;
        }
    }

    /**
     * Get category by URI method
     *
     * @param  string  $uri
     * @param  boolean $isFields
     * @return void
     */
    public function getByUri($uri, $isFields = false)
    {
        $category = Table\Categories::findBy(array('uri' => $uri));
        if (isset($category->id)) {
            $categoryValues = $category->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $categoryValues = array_merge($categoryValues, \Fields\Model\FieldValue::getAll($category->id, true));
            }

            // Get content object within the category
            $categoryValues['items'] = array();
            $content = Table\ContentToCategories::findBy(array('category_id' => $category->id));
            if (isset($content->rows[0])) {
                foreach ($content->rows as $cont) {
                    $c = Table\Content::findById($cont->content_id);
                    if (isset($c->id) && ((null === $c->status) || ($c->status == 2))) {
                        $c = $c->getValues();
                        if (substr($c['uri'], 0, 1) != '/') {
                            $c['uri'] = CONTENT_PATH . '/media/' . $c['uri'];
                            $c['isFile'] = true;
                        } else {
                            $c['isFile'] = false;
                        }
                        $c['category_title'] = $category->title;
                        $c['category_uri'] = $category->uri;
                        if ($isFields) {
                            $c = array_merge($c, \Fields\Model\FieldValue::getAll($c['id'], true));
                        }
                        $categoryValues['items'][] = new \ArrayObject($c, \ArrayObject::ARRAY_AS_PROPS);
                    }
                }
            }

            // Get any child category content objects
            $childCat = Table\Categories::findBy(array('parent_id' => $category->id));
            while (isset($childCat->id)) {
                $childId = $childCat->id;
                $content = Table\ContentToCategories::findBy(array('category_id' => $childId));
                if (isset($content->rows[0])) {
                    foreach ($content->rows as $cont) {
                        $c = Table\Content::findById($cont->content_id);
                        if (isset($c->id) && ((null === $c->status) || ($c->status == 2))) {
                            $c = $c->getValues();
                            if (substr($c['uri'], 0, 1) != '/') {
                                $c['uri'] = CONTENT_PATH . '/media/' . $c['uri'];
                                $c['isFile'] = true;
                            } else {
                                $c['isFile'] = false;
                            }
                            $c['category_title'] = $childCat->title;
                            $c['category_uri'] = $childCat->uri;
                            if ($isFields) {
                                $c = array_merge($c, \Fields\Model\FieldValue::getAll($c['id'], true));
                            }
                            $categoryValues['items'][] = new \ArrayObject($c, \ArrayObject::ARRAY_AS_PROPS);
                        }
                    }
                }
                $childCat = Table\Categories::findBy(array('parent_id' => $childId));
            }

            $this->data = array_merge($this->data, $categoryValues);
        }
    }

    /**
     * Get category by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $category = Table\Categories::findById($id);

        if (isset($category->id)) {
            $categoryValues = $category->getValues();
            $categoryValues['category_title'] = $categoryValues['title'];
            unset($categoryValues['title']);

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $categoryValues = array_merge($categoryValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $categoryValues);
        }
    }

    /**
     * Save category
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $uri = $fields['slug'];

        if ((int)$fields['parent_id'] != 0) {
            $pId = $fields['parent_id'];
            while ($pId != 0) {
                $category = Table\Categories::findById($pId);
                if (isset($category->id)) {
                    $pId = $category->parent_id;
                    $uri = $category->slug . '/' . $uri;
                }
            }
        }

        if (substr($uri, 0, 1) != '/') {
            $uri = '/' . $uri;
        } else if (substr($uri, 0, 2) == '//') {
            $uri = substr($uri, 1);
        }

        $category = new Table\Categories(array(
            'parent_id' => (($fields['parent_id'] != 0) ? $fields['parent_id'] : null),
            'title'     => $fields['category_title'],
            'uri'       => $uri,
            'slug'      => $fields['slug'],
            'order'     => (int)$fields['order'],
            'total'     => (int)$fields['total']
        ));

        $category->save();
        $this->data['id'] = $category->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $category->id);
        }
    }

    /**
     * Update category
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $uri = $fields['slug'];

        if ((int)$fields['parent_id'] != 0) {
            $pId = $fields['parent_id'];
            while ($pId != 0) {
                $category = Table\Categories::findById($pId);
                if (isset($category->id)) {
                    $pId = $category->parent_id;
                    $uri = $category->slug . '/' . $uri;
                }
            }
        }

        if (substr($uri, 0, 1) != '/') {
            $uri = '/' . $uri;
        } else if (substr($uri, 0, 2) == '//') {
            $uri = substr($uri, 1);
        }

        $category = Table\Categories::findById($fields['id']);
        $category->parent_id = (((int)$fields['parent_id'] != 0) ? $fields['parent_id'] : null);
        $category->title     = $fields['category_title'];
        $category->uri       = $uri;
        $category->slug      = $fields['slug'];
        $category->order     = (int)$fields['order'];
        $category->total     = (int)$fields['total'];
        $category->update();

        $this->data['id'] = $category->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $category->id);
        }
    }

    /**
     * Remove category
     *
     * @param  array   $post
     * @param  boolean $isFields
     * @return void
     */
    public function remove(array $post, $isFields = false)
    {
        if (isset($post['remove_categories'])) {
            foreach ($post['remove_categories'] as $id) {
                $category = Table\Categories::findById($id);
                if (isset($category->id)) {
                    $category->delete();
                }

                // If the Fields module is installed, and if there are fields for this form/model
                if ($isFields) {
                    \Fields\Model\FieldValue::remove($id);
                }
            }
        }
    }

    /**
     * Method to get category breadcrumb
     *
     * @return string
     */
    public function getBreadcrumb()
    {
        $breadcrumb = $this->title;
        $pId = $this->parent_id;

        while ($pId != 0) {
            $category = Table\Categories::findById($pId);
            if (isset($category->id)) {
                $breadcrumb = '<a href="' . BASE_PATH . '/category' . $category->uri . '">' . $category->title . '</a> ' .
                    $this->config->separator . ' ' . $breadcrumb;
                $pId = $category->parent_id;
            }
        }

        return $breadcrumb;
    }

    /**
     * Recursive function to get a formatted array of nested categories id => category
     *
     * @param  array $categories
     * @param  int   $depth
     * @return array
     */
    protected function getCategories($categories, $depth = 0) {
        foreach ($categories as $category) {
            $this->categories[$category['id']] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '&gt; ' . $category['title'];
            if (count($category['children']) > 0) {
                $this->getCategories($category['children'], ($depth + 1));
            }
        }
    }

    /**
     * Recursive method to get category children
     *
     * @param array   $category
     * @param int     $pid
     * @param boolean $count
     * @return  array
     */
    protected function getChildren($category, $pid, $count = false)
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

                $p['children'] = $this->getChildren($category, $c->id, $count);
                $children[] = $p;
            }
        }

        return $children;
    }

}

