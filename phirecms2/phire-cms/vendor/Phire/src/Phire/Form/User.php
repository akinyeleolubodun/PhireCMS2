<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;
use Phire\Table;

class User extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $tid
     * @param  boolean $profile
     * @param  int     $uid
     * @param  boolean $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $profile = false, $uid = 0, $isFields = false)
    {
        // Create user type fields/form first
        if ($tid == 0) {
            $typesAry = array();
            $types = Table\UserTypes::findAll('id ASC');
            foreach ($types->rows as $type) {
                $typesAry[$type->id] = $type->type;
            }
            $this->initFieldsValues = array(
                'type_id' => array(
                    'type'     => 'select',
                    'required' => true,
                    'label'    => 'Select User Type:',
                    'value'    => $typesAry
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'value' => 'Select'
                )
            );
        // Else, create initial user fields
        } else {
            $this->initFieldsValues = $this->getInitFields($tid, $profile, $uid, $isFields);
        }

        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'user-form');
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

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  boolean $profile
     * @param  int     $uid
     * @param  boolean $isFields
     * @return array
     */
    protected function getInitFields($tid = 0, $profile = false, $uid = 0, $isFields = false)
    {
        $type = Table\UserTypes::findById($tid);
        $fields1 = array();

        // If not a profile edit, add role field
        if (!$profile) {
            // Get roles for user type
            $rolesAry = array('0' => '(Blocked)');

            if ($tid != 0) {
                $roles = Table\UserRoles::findBy(array('type_id' => $tid), 'id ASC');
                foreach ($roles->rows as $role) {
                    $rolesAry[$role->id] = $role->name;
                }
            }

            $fields1['role_id'] = array(
                'type'     => 'select',
                'required' => true,
                'label'    => 'User Role:',
                'value'    => $rolesAry
            );
        }

        // Continue setting up initial user fields
        $fields1['email1'] = array(
            'type'       => 'text',
            'label'      => 'Email:',
            'required'   => true,
            'attributes' => array('size' => 40),
            'validators' => new Validator\Email()
        );
        $fields1['email2'] = array(
            'type'       => 'text',
            'label'      => 'Re-Type Email:',
            'required'   => true,
            'attributes' => array('size' => 40),
            'validators' => new Validator\Email()
        );

        // If not email as username, create username field
        if (!$type->email_as_username) {
            $fields2 = array(
                'username' => array(
                    'type'       => 'text',
                    'label'      => 'Username:',
                    'required'   => true,
                    'attributes' => array('size' => 40),
                    'validators' => new Validator\AlphaNumeric()
                )
            );
        } else {
            $fields2 = array();
        }

        // Continue setting up initial user fields
        $fields3 = array(
            'password1' => array(
                'type'       => 'password',
                'label'      => 'Enter Password:',
                'required'   => true,
                'attributes' => array('size' => 20),
                'validators' => new Validator\LengthGte(6)
            ),
            'password2' => array(
                'type'       => 'password',
                'label'      => 'Re-Type Password:',
                'required'   => true,
                'attributes' => array('size' => 20),
                'validators' => new Validator\LengthGte(6)
            )
        );

        // If the Phields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Phields\Model\Field::getByModel($model, $tid, $uid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields3[$key] = $value;
                }
            }
        }

        // If not profile edit, add verify and attempts fields
        if (!$profile) {
            $fields3['verified'] = array(
                'type'   => 'radio',
                'label'  => 'Verified:',
                'value'  => array('1' => 'Yes', '0' => 'No'),
                'marked' => '0'
            );
            $fields3['failed_attempts'] = array(
                'type'       => 'text',
                'label'      => 'Failed Attempts:',
                'attributes' => array('size' => 5)
            );
        }

        // Finish the initial fields
        $fields3['type_id'] = array(
            'type'  => 'hidden',
            'value' => $tid
        );
        $fields3['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields3['submit'] = array(
            'type'  => 'submit',
            'label' => '&nbsp;',
            'value' => 'Save'
        );

        return array($fields1, $fields2, $fields3);
    }

}

