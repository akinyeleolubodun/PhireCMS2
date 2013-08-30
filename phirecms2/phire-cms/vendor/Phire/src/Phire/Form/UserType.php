<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;
use Phire\Table;

class UserType extends Form
{


    /**
     * Has file flag
     *
     * @var boolean
     */
    protected $hasFile = false;

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $tid
     * @param  boolean $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $isFields = false)
    {
        $this->initFieldsValues = $this->getInitFields($tid, $isFields);
        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'user-type-form');

        if ($this->hasFile) {
            $this->setAttributes('enctype', 'multipart/form-data');
        }
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

        if ($_POST) {
            if ($this->id == 2001) {
                $this->getElement('type')
                     ->addValidator(new Validator\Equal('user', "The type name for this user type cannot change and must be 'user'."));
            }
            if ($this->password_encryption == 3) {
                $this->getElement('password_salt')
                     ->addValidator(new Validator\NotEmpty(null, 'The crypt password encryption requires a password salt.'));
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
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

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  boolean $isFields
     * @return array
     */
    protected function getInitFields($tid = 0, $isFields = false)
    {
        $yesNo = array('1' => 'Yes', '0' => 'No');

        // Get roles for the user type
        $roles = Table\UserRoles::findAll('id ASC', array('type_id' => $tid));
        $rolesAry = array('0' => '(Blocked)');
        foreach ($roles->rows as $role) {
            $rolesAry[$role->id] = $role->name;
        }

        // Set up initial fields
        $fields1 = array(
            'type' => array(
                'type'       => 'text',
                'label'      => 'Type:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'default_role_id' => array(
                'type'   => 'select',
                'label'  => 'Default Role:',
                'value'  => $rolesAry
            ),
            'login' => array(
                'type'   => 'radio',
                'label'  => 'Allow Login:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'registration' => array(
                'type'   => 'radio',
                'label'  => 'Allow Registration:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'multiple_sessions' => array(
                'type'   => 'radio',
                'label'  => 'Allow Multiple Sessions:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'mobile_access' => array(
                'type'   => 'radio',
                'label'  => 'Allow Mobile Access:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'email_as_username' => array(
                'type'   => 'radio',
                'label'  => 'Allow Email as Username:',
                'value'  => $yesNo,
                'marked' => '0'
            ),
            'force_ssl' => array(
                'type'   => 'radio',
                'label'  => 'Force SSL:',
                'value'  => $yesNo,
                'marked' => '0'
            ),
            'track_sessions' => array(
                'type'   => 'radio',
                'label'  => 'Track Sessions:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'verification' => array(
                'type'   => 'radio',
                'label'  => 'Require Verification:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'approval' => array(
                'type'   => 'radio',
                'label'  => 'Require Approval:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'unsubscribe_login' => array(
                'type'   => 'radio',
                'label'  => 'Require Login for Unsubscribe:',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'global_access' => array(
                'type'   => 'radio',
                'label'  => 'Allow Global Access:',
                'value'  => $yesNo,
                'marked' => '0'
            ),
            'allowed_attempts' => array(
                'type'       => 'text',
                'label'      => 'Allowed Attempts:',
                'attributes' => array('size' => 10),
                'value'      => '0'
            ),
            'session_expiration' => array(
                'type'       => 'text',
                'label'      => 'Session Expiration:',
                'attributes' => array('size' => 10),
                'value'      => '0'
            ),
            'password_encryption' => array(
                'type'  => 'select',
                'label' => 'Password Encryption:',
                'value' => array(
                    '3' => 'Crypt',
                    '2' => 'SHA1',
                    '1' => 'MD5',
                    '0' => 'None'
                ),
                'marked' => '2'
            ),
            'password_salt' => array(
                'type'       => 'text',
                'label'      => 'Password Salt:',
                'attributes' => array('size' => 40)
            ),
            'ip_allowed' => array(
                'type'       => 'text',
                'label'      => 'IPs Allowed:',
                'attributes' => array('size' => 40)
            ),
            'ip_blocked' => array(
                'type'       => 'text',
                'label'      => 'IPs Blocked:',
                'attributes' => array('size' => 40)
            ),
            'log_emails' => array(
                'type'       => 'text',
                'label'      => 'Log Emails:',
                'attributes' => array('size' => 40)
            ),
            'log_exclude' => array(
                'type'       => 'text',
                'label'      => 'Log Exclude:',
                'attributes' => array('size' => 40)
            ),
            'controller' => array(
                'type'       => 'text',
                'label'      => 'Controller:',
                'attributes' => array('size' => 40)
            ),
            'sub_controllers' => array(
                'type'       => 'text',
                'label'      => 'Sub Controllers:',
                'attributes' => array('size' => 40)
            )
        );

        $fields2 = array();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, 0, $tid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                    if ($value['type'] == 'file') {
                        $this->hasFile = true;
                    }
                }
            }
        }

        $fields2['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields2['submit'] = array(
            'type'  => 'submit',
            'label' => '&nbsp;',
            'value' => 'Save'
        );

        return array($fields1, $fields2);
    }

}

