<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;

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
                'attributes' => array(
                    'size'  => 30,
                    'style' => 'display: block; width: 288px; margin: 0 auto;'
                )
            ),
            'password' => array(
                'type'       => 'password',
                'label'      => 'Password:',
                'required'   => true,
                'attributes' => array(
                    'size'  => 30,
                    'style' => 'display: block; width: 288px; margin: 0 auto;'
                )
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'LOGIN',
                'attributes' => array(
                    'class' => 'save-btn',
                    'style' => 'display: block; width: 300px; margin: 0 auto;'
                )
            )
        );

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'login-form');
    }

    /**
     * Set the field values
     *
     * @param  array                  $values
     * @param  mixed                  $filters
     * @param  mixed                  $params
     * @param  \Phire\Auth\Auth       $auth
     * @param  \Phire\Table\UserTypes $type
     * @param  \Phire\Model\User      $user
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null, $auth = null, $type = null, $user = null)
    {
        parent::setFieldValues($values, $filters, $params);

        if ($_POST) {
            // Authenticate and get the auth result
            $auth->authenticate($this->username, $this->password);
            $result = $auth->getAuthResult($type, $this->username);

            if (null !== $result) {
                $user->login($this->username, $type, false);
                if ($auth->getResult() == \Pop\Auth\Auth::PASSWORD_INCORRECT) {
                    $this->getElement('password')
                         ->addValidator(new Validator\NotEqual($this->password, $result));
                } else {
                    $this->getElement('username')
                         ->addValidator(new Validator\NotEqual($this->username, $result));
                }
            }
        }

        return $this;
    }

}

