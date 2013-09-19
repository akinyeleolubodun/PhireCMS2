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

class ContentType extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $tid
     * @param  boolean $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $isFields = false)
    {
        $this->initFieldsValues = $this->getInitFields($tid, $isFields);
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'content-type-form');
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

        // Check for dupe content type names
        if ($_POST) {
            $type = Table\ContentTypes::findBy(array('name' => $this->name));
            if (isset($type->id) && ($this->id != $type->id)) {
                $this->getElement('name')
                     ->addValidator(new Validator\NotEqual($this->name, 'That content type name already exists. The name must be unique.'));
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

    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  boolean $isFields
     * @return array
     */
    protected function getInitFields($tid = 0, $isFields = false)
    {
        // Create initial fields
        $fields1 = array(
            'name' => array(
                'type'       => 'text',
                'label'      => 'Name:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'uri' => array(
                'type'   => 'select',
                'label'  => 'URI Type:',
                'value'  => array(
                    '1' => 'URI',
                    '0' => 'File',
                    '2' => 'Event'
                ),
                'marked' => 1
            )
        );

        $fields2 = array();
        $dynamicFields = false;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, 0, $tid);
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

        // Create additional fields
        $fields2['order'] = array(
            'type'       => 'text',
            'label'      => 'Order:',
            'attributes' => array('size' => 3),
            'value'      => 0
        );

        // If it's a redirect from an add content request
        if (isset($_GET['redirect'])) {
            $fields2['redirect'] = array(
                'type'  => 'hidden',
                'value' => 1
            );
        }

        // Create remaining fields
        $fields3 = array(
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'SAVE',
                'attributes' => array(
                    'class'   => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return updateForm('#content-type-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class'   => 'update-btn'
                )
            )
        );

        return array($fields1, $fields2, $fields3);
    }

}

