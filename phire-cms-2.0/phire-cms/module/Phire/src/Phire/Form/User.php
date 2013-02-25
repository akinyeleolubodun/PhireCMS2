<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;

class User extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return \Phire\Form\User
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {

        $access = Table\SysAccess::findAll('level DESC', array('type' => 'user'));
        $accessAry = array();

        if (isset($access->rows[0])) {
            foreach ($access->rows as $access) {
                $accessAry[$access->id] = $access->name;
            }
        }

        $accessAry[0] = 'Blocked';

        $this->initFieldsValues = array (
            array (
                'type' => 'text',
                'name' => 'first_name',
                'label' => 'First Name:',
                'required' => true,
                'attributes' => array (
                  0 => 'size',
                  1 => 40,
                )
            ),
            array (
                'type' => 'text',
                'name' => 'last_name',
                'label' => 'Last Name:',
                'required' => true,
                'attributes' => array (
                  0 => 'size',
                  1 => 40,
                )
            ),
            array (
                'type' => 'text',
                'name' => 'email1',
                'label' => 'Email:',
                'required' => true,
                'attributes' => array (
                  0 => 'size',
                  1 => 40,
                ),
                'validators' => new Validator\Email()
            ),
            array (
                'type' => 'text',
                'name' => 'email2',
                'label' => 'Re-Type Email:',
                'required' => true,
                'attributes' => array (
                  0 => 'size',
                  1 => 40,
                ),
                'validators' => new Validator\Email()
            )
        );

        if (!Table\SysConfig::findById('email_as_username')->value) {
            $this->initFieldsValues[] = array (
                'type' => 'text',
                'name' => 'username',
                'label' => 'Username:',
                'required' => true,
                'attributes' => array (
                  0 => 'size',
                  1 => 40,
                ),
                'validators' => array(
                    new Validator\AlphaNumeric(),
                    new Validator\LengthGte(5)
                )
            );
        }

        $this->initFieldsValues[] = array (
            'type' => 'password',
            'name' => 'password1',
            'label' => 'Enter Password:',
            'required' => true,
            'attributes' => array (
              0 => 'size',
              1 => 20,
            ),
            'validators' => new Validator\LengthGte(6)
        );
        $this->initFieldsValues[] = array (
            'type' => 'password',
            'name' => 'password2',
            'label' => 'Re-Type Password:',
            'required' => true,
            'attributes' => array (
              0 => 'size',
              1 => 20,
            ),
            'validators' => new Validator\LengthGte(6)
        );
        $this->initFieldsValues[] = array(
            'type' => 'checkbox',
            'name' => 'allowed_sites',
            'label' => 'Allowed Sites:',
            'required' => true,
            'value' => Table\Sites::getSites()
        );
        $this->initFieldsValues[] = array(
            'type' => 'select',
            'name' => 'access_id',
            'label' => 'Access:',
            'value' => $accessAry
        );
        $this->initFieldsValues[] = array(
            'type' => 'checkbox',
            'name' => 'send_creds',
            'label' => '&nbsp;',
            'value' => array(
                'Yes' => 'Send credentials to user?'
            )
        );
        $this->initFieldsValues[] = array(
            'type' => 'hidden',
            'name' => 'id',
            'value' => 0
        );
        $this->initFieldsValues[] = array (
            'type' => 'submit',
            'name' => 'submit',
            'label' => '&nbsp;',
            'value' => 'Save'
        );

        parent::__construct($action, $method, $fields, $indent);
    }

    /**
     * Set the field values
     *
     * @param array $values
     * @param mixed $filters
     * @param mixed $params
     * @return \Phire\Form\User
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

        // Add validators for checking dupe usernames
        // and matching the emails and passwords
        if ($_POST) {
            if ($this->username != '') {
                $username =  $this->username;
                $usernameField = 'username';
            } else {
                $username =  $this->email1;
                $usernameField = 'email1';
            }

            $user = Table\Users::findBy('username', $username);
            if (isset($user->id) && ($this->id != $user->id)) {
                $this->getElement($usernameField)
                     ->addValidator(new Validator\NotEqual($username, 'That user already exists.'));
            }

            $this->getElement('email2')
                 ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
            $this->getElement('password2')
                 ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
        }

        return $this;
    }

}

