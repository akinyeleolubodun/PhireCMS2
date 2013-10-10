<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\File\Dir;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table;

class Category extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $cid
     * @param  boolean $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $cid = 0, $isFields = false)
    {
        $this->initFieldsValues = $this->getInitFields($cid, $isFields);
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'category-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

        // Add validators for checking dupe slugs
        if (($_POST) && isset($_POST['id'])) {
            $slug = Table\Categories::findBy(array('slug' => $this->slug));
            if (isset($slug->id) && ((int)$this->parent_id == (int)$slug->parent_id) && ($this->id != $slug->id)) {
                $this->getElement('slug')
                     ->addValidator(new Validator\NotEqual($this->slug, 'That URI already exists under that parent category.'));
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if ($value['size'] > $config->media_max_filesize) {
                    $this->getElement($key)
                         ->addValidator(new Validator\LessThanEqual($config->media_max_filesize, 'The file must be less than ' . $config->media_max_filesize_formatted . '.'));
                }
                if (preg_match($regex, $value['name']) == 0) {
                    $type = strtoupper(substr($value['name'], (strrpos($value['name'], '.') + 1)));
                    $this->getElement($key)
                         ->addValidator(new Validator\NotEqual($value['name'], 'The ' . $type . ' file type is not allowed.'));
                }
            }
        }

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $cid
     * @param  boolean $isFields
     * @return array
     */
    protected function getInitFields($cid = 0, $isFields = false)
    {
        // Get children, if applicable
        $children = ($cid != 0) ? $this->children($cid) : array();
        $parents = array(0 => '----');

        // Prevent the object's children or itself from being in the parent drop down
        $cats = Table\Categories::findAll('id ASC');
        foreach ($cats->rows as $cat) {
            if (($cat->id != $cid) && (!in_array($cat->id, $children))) {
                $parents[$cat->id] = $cat->category;
            }
        }

        // Get parents and children, if applicable
        $parents = array(0 => '----');

        // Prevent the object's children or itself from being in the parent drop down
        $content = Table\Categories::findAll('order ASC');
        foreach ($content->rows as $c) {
            if (($c->parent_id == 0) && ($c->id != $cid)) {
                $parents[$c->id] = $c->category;
                $children = $this->children($c->id);
                if (count($children) > 0) {
                    foreach ($children as $id => $child) {
                        if ($id != $cid) {
                            $parents[$id] = $child;
                        }
                    }
                }
            }
        }

        // Create initial fields
        $fields1 = array(
            'parent_id' => array(
                'type'       => 'select',
                'label'      => 'Parent:',
                'value'      => $parents,
                'attributes' => array(
                    'onchange' => "catSlug(null, 'slug');"
                )
            ),
            'category' => array(
                'type'       => 'text',
                'label'      => 'Category:',
                'required'   => true,
                'attributes' => array(
                    'size'    => 80,
                    'onkeyup' => "catSlug('category', 'slug');"
                )
            ),
            'slug' => array(
                'type'       => 'text',
                'label'      => 'Slug:',
                'required'   => true,
                'attributes' => array(
                    'size' => 80,
                    'onkeyup' => "catSlug(null, 'slug');"
                )
            ),
            'order' => array(
                'type'       => 'text',
                'label'      => 'Order:',
                'attributes' => array('size' => 3),
                'value'      => 0
            ),
            'total' => array(
                'type'       => 'radio',
                'label'      => 'Show Total:',
                'value'      => array(
                    '1' => 'Yes',
                    '0' => 'No'
                ),
                'marked' => '1'
            )
        );

        $fields2 = array();
        $dynamicFields = false;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, 0, $cid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                    if ($value['type'] == 'file') {
                        $this->hasFile = true;
                    }
                    if (strpos($key, 'new_') !== false) {
                        $dynamicFields = true;
                    }
                }
            }
        }

        // Create remaining fields
        $fields3 = array(
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'SAVE',
                'attributes' => array(
                    'class' => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return updateForm('#category-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class' => 'update-btn'
                )
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            )
        );

        return array($fields3, $fields1, $fields2);
    }

    /**
     * Recursive method to get children of the category object
     *
     * @param  int   $pid
     * @param  array $children
     * @param  int   $depth
     * @return array
     */
    protected function children($pid, $children = array(), $depth = 0)
    {
        $c = Table\Categories::findBy(array('parent_id' => $pid));

        if (isset($c->rows[0])) {
            foreach ($c->rows as $child) {
                $children[$child->id] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', ($depth + 1)) . '&gt; ' . $child->category;
                $c = Table\Categories::findBy(array('parent_id' => $child->id));
                if (isset($c->rows[0])) {
                    $d = $depth + 1;
                    $children = $this->children($child->id, $children, $d);
                }
            }
        }

        return $children;
    }

}

