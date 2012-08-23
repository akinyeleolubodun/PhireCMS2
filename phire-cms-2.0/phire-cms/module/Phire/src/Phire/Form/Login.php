<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form,
    Pop\Form\Element,
    Pop\Validator\Validator;

class Login extends Form
{

    /**
     * Constructer method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return void
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {
        $this->initFieldsValues = array (
            array (
                'type' => 'text',
                'name' => 'username',
                'label' => 'Username:',
                'required' => true,
                'attributes' => array('size', 40),
                'validators' => new Validator\AlphaNumeric()
            ),
            array (
                'type' => 'password',
                'name' => 'password',
                'label' => 'Password:',
                'required' => true,
                'attributes' => array('size', 40),
                'validators' => array(
                    new Validator\NotEmpty(),
                    new Validator\LengthGt(6),
                )
            ),
            array (
                'type' => 'submit',
                'name' => 'submit',
                'value' => 'LOGIN'
            )
        );

        parent::__construct($action, $method, $fields, $indent);
    }

}

