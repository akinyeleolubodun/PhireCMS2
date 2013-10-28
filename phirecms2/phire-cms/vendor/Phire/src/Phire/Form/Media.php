<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;

class Media extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @return self
     */
    public function __construct($action = null, $method = 'post')
    {
        $typesAry = array();
        $types = \Phire\Table\ContentTypes::findAll('order ASC', array('uri' => 0));
        foreach ($types->rows as $type) {
            $typesAry[$type->id] = $type->name;
        }

        $this->initFieldsValues = array(
            'type_id' => array(
                'type'     => 'select',
                'required' => true,
                'value'    => $typesAry
            ),
            'uri' => array(
                'type'       => 'file',
                'required'   => true,
                'attributes' => array('size' => 30)
            ),
            'submit' => array(
                'type'  => 'submit',
                'value' => 'UPLOAD',
                'attributes' => array(
                    'class' => 'upload-btn'
                )
            )
        );

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'media-form')
             ->setAttributes('onsubmit', 'showLoading();');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @throws \Pop\File\Exception
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

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

}

