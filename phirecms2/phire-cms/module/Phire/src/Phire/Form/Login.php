<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;

class Login extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return \Phire\Form\Login
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {
        $this->initFieldsValues = array (
            array (
                'type' => 'text',
                'name' => 'username',
                'label' => 'Username:',
                'required' => true,
                'attributes' => array('size', 40)
            ),
            array (
                'type' => 'password',
                'name' => 'password',
                'label' => 'Password:',
                'required' => true,
                'attributes' => array('size', 40)
            ),
            array (
                'type' => 'submit',
                'name' => 'submit',
                'label' => '&nbsp;',
                'value' => 'LOGIN'
            )
        );

        parent::__construct($action, $method, $fields, $indent);
    }

}

