<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class Category extends AbstractContentModel
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
     * Get all content categories method
     *
     * @param  string  $sort
     * @param  string  $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $categories = Table\Categories::findAll($order['field'] . ' ' . $order['order']);

        $this->data['categories'] = $categories->rows;
        $this->data['categoryTree'] = $this->getChildren($categories->rows, 0);
        $this->getCategories($this->data['categoryTree']);

        if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Content\CategoriesController', 'remove'))) {
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
                'action'  => BASE_PATH . APP_URI . '/content/categories/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'       => '<a href="' . BASE_PATH . APP_URI . '/content/categories?sort=id">#</a>',
                    'category' => '<a href="' . BASE_PATH . APP_URI . '/content/categories?sort=category">Category</a>',
                    'process'  => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            )
        );

        $catAry = array();
        $cats = $this->categories;
        unset($cats[0]);

        foreach ($cats as $id => $name) {
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Content\CategoriesController', 'edit'))) {
                $name = '<a href="' . BASE_PATH . APP_URI . '/content/categories/edit/' . $id . '">' . $name . '</a>';
            }
            $catAry[] = array(
                'id' => $id,
                'category' => $name
            );
        }

        if (isset($catAry[0])) {
            $table = Html::encode($catAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Content\CategoriesController', 'edit'))) {
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
            $this->getNav($category);
            $categoryValues = $category->getValues();
            $categoryValues['title'] = $categoryValues['category'];

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
                        $c['category_title'] = $category->category;
                        $c['category_uri'] = $category->uri;
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
                            $c['category_title'] = $childCat->category;
                            $c['category_uri'] = $childCat->uri;
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
     * Get content categories by ID method
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

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $categoryValues = array_merge($categoryValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $categoryValues);
        }
    }

    /**
     * Save content categories
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
            'category'  => $fields['category'],
            'uri'       => $uri,
            'slug'      => $fields['slug'],
            'order'     => (int)$fields['order']
        ));

        $category->save();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $category->id);
        }
    }

    /**
     * Update content categories
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
        $category->category  = $fields['category'];
        $category->uri       = $uri;
        $category->slug      = $fields['slug'];
        $category->order     = (int)$fields['order'];
        $category->update();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $category->id);
        }
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
            $this->categories[$category['id']] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $depth) . '&gt; ' . $category['category'];
            if (count($category['children']) > 0) {
                $this->getCategories($category['children'], ($depth + 1));
            }
        }
    }

}

