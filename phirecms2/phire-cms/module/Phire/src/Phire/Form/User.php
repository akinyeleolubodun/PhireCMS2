<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table;

class User extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @param  int    $tid
     * @return self
     */
    public function __construct($action, $method, array $fields = null, $indent = null, $tid = 0)
    {
        if ($tid == 0) {
            $typesAry = array();
            $types = Table\UserTypes::findAll('id ASC');
            foreach ($types->rows as $type) {
                $typesAry[$type->id] = $type->type;
            }
            $this->initFieldsValues = array (
                array(
                    'type' => 'select',
                    'name' => 'type_id',
                    'required' => true,
                    'label' => 'Select User Type:',
                    'value' => $typesAry
                ),
                array (
                    'type' => 'submit',
                    'name' => 'submit',
                    'label' => '&nbsp;',
                    'value' => 'Select'
                )
            );
        } else {
            $yesNo = array('1' => 'Yes', '0' => 'No');
            $type = Table\UserTypes::findById($tid);
            $rolesAry = array('0' => '(Blocked)');

            if ($tid != 0) {
                $roles = Table\UserRoles::findBy(array('type_id' => $tid), 'id ASC');
                foreach ($roles->rows as $role) {
                    $rolesAry[$role->id] = $role->name;
                }
            }

            $fields1 = array (
                array(
                    'type' => 'select',
                    'name' => 'role_id',
                    'required' => true,
                    'label' => 'User Role:',
                    'value' => $rolesAry
                ),
                array (
                    'type' => 'text',
                    'name' => 'first_name',
                    'label' => 'First Name:',
                    'required' => true,
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'last_name',
                    'label' => 'Last Name:',
                    'required' => true,
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'email1',
                    'label' => 'Email:',
                    'required' => true,
                    'attributes' => array('size', 40),
                    'validators' => new Validator\Email()
                ),
                array (
                    'type' => 'text',
                    'name' => 'email2',
                    'label' => 'Re-Type Email:',
                    'required' => true,
                    'attributes' => array('size', 40),
                    'validators' => new Validator\Email()
                )
             );

            if (!$type->email_as_username) {
                $fields2 = array(
                    array (
                        'type' => 'text',
                        'name' => 'username',
                        'label' => 'Username:',
                        'required' => true,
                        'attributes' => array('size', 40),
                        'validators' => new Validator\AlphaNumeric()
                    )
                );
            } else {
                $fields2 = array();
            }

            $fields3 = array(
                array (
                    'type' => 'password',
                    'name' => 'password1',
                    'label' => 'Enter Password:',
                    'required' => true,
                    'attributes' => array('size', 20),
                    'validators' => new Validator\LengthGte(6)
                ),
                array (
                    'type' => 'password',
                    'name' => 'password2',
                    'label' => 'Re-Type Password:',
                    'required' => true,
                    'attributes' => array('size', 20),
                    'validators' => new Validator\LengthGte(6)
                ),
                array (
                    'type' => 'text',
                    'name' => 'address',
                    'label' => 'Address:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'city',
                    'label' => 'City:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'state',
                    'label' => 'State:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'zip',
                    'label' => 'Zip:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'select',
                    'name' => 'country',
                    'label' => 'Country:',
                    'value' => 'COUNTRIES'
                ),
                array (
                    'type' => 'text',
                    'name' => 'phone',
                    'label' => 'Phone:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'organization',
                    'label' => 'Organization:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'text',
                    'name' => 'position',
                    'label' => 'Position:',
                    'attributes' => array('size', 40)
                ),
                array (
                    'type' => 'select',
                    'name' => 'birth_date_month',
                    'label' => 'Birth Date:',
                    'value' => Element\Select::MONTHS_SHORT
                ),
                array (
                    'type' => 'select',
                    'name' => 'birth_date_day',
                    'value' => Element\Select::DAYS_OF_MONTH
                ),
                array (
                    'type' => 'select',
                    'name' => 'birth_date_year',
                    'value' => 'YEAR_1900'
                ),
                array (
                    'type' => 'select',
                    'name' => 'gender',
                    'label' => 'Gender:',
                    'value' => array (
                        '--' => '----',
                        'M' => 'Male',
                        'F' => 'Female',
                    )
                ),
                array (
                    'type' => 'radio',
                    'name' => 'updates',
                    'label' => 'Receive Updates:',
                    'value' => $yesNo,
                    'marked' => '1'
                ),
                array (
                    'type' => 'radio',
                    'name' => 'verified',
                    'label' => 'Verified:',
                    'value' => $yesNo,
                    'marked' => '0'
                ),
                array(
                    'type' => 'text',
                    'name' => 'failed_attempts',
                    'label' => 'Failed Attempts:',
                    'attributes' => array ('size', 5)
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'type_id',
                    'value' => $tid
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id',
                    'value' => 0
                ),
                array (
                    'type' => 'submit',
                    'name' => 'submit',
                    'label' => '&nbsp;',
                    'value' => 'Save'
                )
            );

            $this->initFieldsValues = array_merge($fields1, $fields2, $fields3);
        }

        parent::__construct($action, $method, $fields, $indent);
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

        if ($this->id != 0) {
            $this->getElement('email2')->setRequired(false);
            $this->getElement('password1')->setRequired(false);
            $this->getElement('password2')->setRequired(false);
        }

        // Add validators for checking dupe usernames
        // and matching the emails and passwords
        if (($_POST) && isset($_POST['id'])) {
            if (isset($this->fields['username'])) {
                $username =  $this->username;
                $usernameField = 'username';
            } else {
                $username =  $this->email1;
                $usernameField = 'email1';
            }

            $user = Table\Users::findBy(array('username' => $username));
            if (isset($user->id) && ($this->id != $user->id)) {
                $this->getElement($usernameField)
                      ->addValidator(new Validator\NotEqual($username, 'That user already exists.'));
            }

            $email = Table\Users::findBy(array('email' => $this->email1));
            if (isset($email->id) && ($this->id != $email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, 'That email already exists.'));
            }

            $this->getElement('email2')
                 ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
            $this->getElement('password2')
                 ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
        }

        return $this;
    }

}

