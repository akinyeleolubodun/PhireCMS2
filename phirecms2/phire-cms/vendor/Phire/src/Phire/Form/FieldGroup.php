<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;

class FieldGroup extends Form
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
        $this->initFieldsValues = array(
            'name' => array(
                'type'       => 'text',
                'label'      => 'Name:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'order' => array(
                'type'       => 'text',
                'label'      => 'Order:',
                'attributes' => array('size' => 3),
                'value'      => 0
            ),
            'dynamic' => array(
                'type'  => 'radio',
                'label' => 'Dynamic?',
                'value' => array(
                    '0' => 'No',
                    '1' => 'Yes'
                ),
                'marked' => '0'
            ),
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
                    'class' => 'save-btn',
                    'style' => 'width: 167px;'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#field-group-form', false);",
                    'class' => 'update-btn',
                    'style' => 'width: 167px;'
                )
            )
        );

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'field-group-form');
    }

}

