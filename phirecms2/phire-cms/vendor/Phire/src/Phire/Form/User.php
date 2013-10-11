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
                    'value' => 'SELECT',
                    'attributes' => array(
                        'class'   => 'save-btn',
                        'style' => 'padding: 5px 6px 6px 6px; width: 100px;'
                    )
                )
            );
            $id = 'user-select-form';
        // Else, create initial user fields
        } else {
            $this->initFieldsValues = $this->getInitFields($tid, $profile, $uid, $isFields, $action);
            $id = (strpos($action, '/install/user') !== false) ? 'user-install-form' : 'user-form';
        }

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', $id);
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
            if (null !== $this->getElement('email2')) {
                $this->getElement('email2')->setRequired(false);
            }
            if (null !== $this->getElement('password1')) {
                $this->getElement('password1')->setRequired(false);
                $this->getElement('password2')->setRequired(false);
            }
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

            if (null !== $this->getElement('email2')) {
                $this->getElement('email2')
                     ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
            }

            // If the password fields are set, check them for a match
            if (isset($this->password2)) {
                $this->getElement('password2')
                     ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if ($value['error'] != 4) {
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

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  boolean $profile
     * @param  int     $uid
     * @param  boolean $isFields
     * @param  string  $action
     * @return array
     */
    protected function getInitFields($tid = 0, $profile = false, $uid = 0, $isFields = false, $action)
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
                'value'    => $rolesAry,
                'marked'   => $type->default_role_id
            );
        }

        // Continue setting up initial user fields
        $fields1['email1'] = array(
            'type'       => 'text',
            'label'      => 'Email:',
            'required'   => true,
            'attributes' => array('size' => 30),
            'validators' => new Validator\Email()
        );
        if ($type->email_verification) {
            $fields1['email2'] = array(
                'type'       => 'text',
                'label'      => 'Re-Type Email:',
                'required'   => true,
                'attributes' => array('size' => 30),
                'validators' => new Validator\Email()
            );
        }

        // If not email as username, create username field
        if (!$type->email_as_username) {
            $fields2 = array(
                'username' => array(
                    'type'       => 'text',
                    'label'      => 'Username:',
                    'required'   => true,
                    'attributes' => array('size' => 30),
                    'validators' => array(
                        new Validator\AlphaNumeric(),
                        new Validator\LengthGte(4)
                    )
                )
            );
        } else {
            $fields2 = array();
        }

        // Continue setting up initial user fields
        if ($type->login) {
            $fields3 = array(
                'password1' => array(
                    'type'       => 'password',
                    'label'      => 'Enter Password:',
                    'required'   => true,
                    'attributes' => array('size' => 30),
                    'validators' => new Validator\LengthGte(6)
                ),
                'password2' => array(
                    'type'       => 'password',
                    'label'      => 'Re-Type Password:',
                    'required'   => true,
                    'attributes' => array('size' => 30),
                    'validators' => new Validator\LengthGte(6)
                )
            );
        } else {
            $fields3 = array();
        }

        $dynamicFields = false;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, $tid, $uid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields3[$key] = $value;
                    if ($value['type'] == 'file') {
                        $this->hasFile = true;
                    }
                    if (strpos($key, 'new_') !== false) {
                        $dynamicFields = true;
                    }
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

        $fields4 = array();

        // Finish the initial fields
        $fields4['submit'] = array(
            'type'  => 'submit',
            'label' => '&nbsp;',
            'value' => 'SAVE',
            'attributes' => array(
                'class' => 'save-btn'
            )
        );

        if (!$profile) {
            $fields4['update'] = array(
                'type'       => 'button',
                'value'      => 'Update',
                'attributes' => array(
                    'onclick' => "return updateForm('#user-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class' => 'update-btn'
                )
            );
        }

        $fields4['type_id'] = array(
            'type'  => 'hidden',
            'value' => $tid
        );
        $fields4['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );

        if (!$profile) {
            $fields4['update_value'] = array(
                'type'  => 'hidden',
                'value' => 0
            );
        }

        $allFields = (strpos($action, '/install/user') !== false) ?  array($fields1, $fields2, $fields3, $fields4) : array($fields4, $fields1, $fields2, $fields3);

        return $allFields;
    }

}

