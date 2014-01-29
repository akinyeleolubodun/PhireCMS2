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
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0)
    {
        $this->initFieldsValues = $this->getInitFields($tid);
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

    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @return array
     */
    protected function getInitFields($tid = 0)
    {
        // Create initial fields
        $fields1 = array(
            'name' => array(
                'type'       => 'text',
                'label'      => 'Name:',
                'required'   => true,
                'attributes' => array('size' => 80)
            )
        );

        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $tid);
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

        $fields2 = array();

        // If it's a redirect from an add content request
        if (isset($_GET['redirect'])) {
            $fields2['redirect'] = array(
                'type'  => 'hidden',
                'value' => 1
            );
        }

        // Create remaining fields
        $fields3 = array(
            'submit' => array(
                'type'  => 'submit',
                'value' => 'SAVE',
                'attributes' => array(
                    'class'   => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#content-type-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class'   => 'update-btn'
                )
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'uri' => array(
                'type'   => 'select',
                'label'  => 'URI Type:',
                'value'  => array(
                    '1' => 'URI',
                    '0' => 'File'
                ),
                'marked' => 1,
                'attributes' => array('style' => 'width: 100px;')
            ),
            'order' =>  array(
                'type'       => 'text',
                'label'      => 'Order:',
                'value'      => 0,
                'attributes' => array(
                    'size'  => 3,
                    'style' => 'padding: 5px 4px 5px 4px;'
                )
            )
        );

        $allFields = array($fields3, $fields1);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }
        $allFields[] = $fields2;

        return $allFields;
    }

}

