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

class Navigation extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $nid
     * @return self
     */
    public function __construct($action = null, $method = 'post', $nid = 0)
    {
        $this->initFieldsValues = $this->getInitFields($nid);
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'navigation-form');
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
            $nav = Table\Navigation::findBy(array('navigation' => $this->navigation));
            if (isset($nav->id) && ((int)$this->id != (int)$nav->id)) {
                $this->getElement('navigation')
                     ->addValidator(new Validator\NotEqual($this->navigation, 'That navigation name already exists.'));
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if (($_FILES) && isset($_FILES[$key]) && ($_FILES[$key]['error'] == 1)) {
                    $this->getElement($key)
                         ->addValidator(new Validator\LessThanEqual(-1, "The 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . " exceeded."));
                } else if ($value['error'] != 4) {
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
        }

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $nid
     * @return array
     */
    protected function getInitFields($nid = 0)
    {

        // Create initial fields
        $fields1 = array(
            'navigation' => array(
                'type'       => 'text',
                'label'      => 'Navigation:',
                'required'   => true,
                'attributes' => array(
                    'size'    => 80
                )
            ),
            'top_node' => array(
                'type'       => 'text',
                'label'      => '<span class="label-pad-1">Top Node</span><span class="label-pad-1">ID</span><span class="label-pad-1">Class</span><span>Attributes:</span>',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'top_id' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'top_class' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'top_attributes' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 40
                )
            ),
            'parent_node' => array(
                'type'       => 'text',
                'label'      => '<span class="label-pad-1">Parent Node</span><span class="label-pad-1">ID</span><span class="label-pad-1">Class</span><span>Attributes:</span>',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'parent_id' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'parent_class' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'parent_attributes' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 40
                )
            ),
            'child_node' => array(
                'type'       => 'text',
                'label'      => '<span class="label-pad-1">Child Node</span><span class="label-pad-1">ID</span><span class="label-pad-1">Class</span><span>Attributes:</span>',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'child_id' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'child_class' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 10
                )
            ),
            'child_attributes' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size'    => 40
                )
            ),
            'on_class' => array(
                'type'       => 'text',
                'label'      => '&quot;On&quot; Class:',
                'attributes' => array(
                    'size'    => 15
                )
            ),
            'off_class' => array(
                'type'       => 'text',
                'label'      => '&quot;Off&quot; Class:',
                'attributes' => array(
                    'size'    => 15
                )
            ),
            'spaces' => array(
                'type'       => 'text',
                'label'      => 'Indentation Spaces:',
                'attributes' => array(
                    'size'    => 15
                ),
                'value'      => 4
            )
        );

        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $nid);
        if ($newFields['dynamic']) {
            $dynamicFields = true;
        }
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
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
                    'onclick' => "return updateForm('#navigation-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
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

        $allFields = array($fields3, $fields1);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }

        return $allFields;
    }

}

