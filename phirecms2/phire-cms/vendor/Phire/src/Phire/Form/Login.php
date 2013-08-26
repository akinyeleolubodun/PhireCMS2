<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;

class Login extends Form
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
            'username' => array(
                'type'       => 'text',
                'label'      => 'Username:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'password' => array(
                'type'       => 'password',
                'label'      => 'Password:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Login'
            )
        );

        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'login-form');
    }

}

