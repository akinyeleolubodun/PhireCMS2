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
        parent::__construct($action, $method, null, '    ');
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
                    'size'    => 40,
                    'onkeyup' => "catSlug('category', 'slug');"
                )
            ),
            'slug' => array(
                'type'       => 'text',
                'label'      => 'Slug:',
                'required'   => true,
                'attributes' => array(
                    'size' => 40,
                    'onkeyup' => "catSlug(null, 'slug');"
                )
            )
        );

        $fields2 = array();

        // If the Phields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Phields\Model\Field::getByModel($model, 0, $cid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                }
            }
        }

        // Create remaining fields
        $fields3 = array(
            'order' => array(
                'type'       => 'text',
                'label'      => 'Order:',
                'attributes' => array('size' => 3),
                'value'      => 0
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Save'
            )
        );

        return array($fields1, $fields2, $fields3);
    }

    /**
     * Recursive method to get children of the category object
     *
     * @param  int   $pid
     * @param  array $children
     * @return array
     */
    protected function children($pid, $children = array())
    {
        $c = Table\Categories::findBy(array('parent_id' => $pid));

        if (isset($c->rows[0])) {
            foreach ($c->rows as $child) {
                $children[] = $child->id;
                $c = Table\Categories::findBy(array('parent_id' => $child->id));
                if (isset($c->rows[0])) {
                    $children = $this->children($child->id, $children);
                }
            }
        }

        return $children;
    }

}

