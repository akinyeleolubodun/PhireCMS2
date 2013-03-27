<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;

class Type extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return self
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {
        $yesNo = array('1' => 'Yes', '0' => 'No');

        $this->initFieldsValues = array (
            array (
                'type' => 'text',
                'name' => 'type',
                'label' => 'Type:',
                'required' => true,
                'attributes' => array ('size', 40)
            ),
            array (
                'type' => 'radio',
                'name' => 'login',
                'label' => 'Allow Login:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'registration',
                'label' => 'Allow Registration:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'multiple_sessions',
                'label' => 'Allow Multiple Sessions:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'mobile_access',
                'label' => 'Allow Mobile Access:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'email_as_username',
                'label' => 'Allow Email as Username:',
                'value' => $yesNo,
                'marked' => '0'
            ),
            array (
                'type' => 'radio',
                'name' => 'force_ssl',
                'label' => 'Force SSL:',
                'value' => $yesNo,
                'marked' => '0'
            ),
            array (
                'type' => 'radio',
                'name' => 'track_sessions',
                'label' => 'Track Sessions:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'verification',
                'label' => 'Require Verification:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'approval',
                'label' => 'Require Approval:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'unsubscribe_login',
                'label' => 'Require Login for Unsubscribe:',
                'value' => $yesNo,
                'marked' => '1'
            ),
            array (
                'type' => 'radio',
                'name' => 'global_access',
                'label' => 'Allow Global Access:',
                'value' => $yesNo,
                'marked' => '0'
            ),
            array (
                'type' => 'text',
                'name' => 'allowed_attempts',
                'label' => 'Allowed Attempts:',
                'attributes' => array ('size', 10),
                'value' => '0'
            ),
            array (
                'type' => 'text',
                'name' => 'session_expiration',
                'label' => 'Session Expiration:',
                'attributes' => array ('size', 10),
                'value' => '0'
            ),
            array (
                'type' => 'select',
                'name' => 'password_encryption',
                'label' => 'Password Encryption:',
                'value' => array(
                    '3' => 'Crypt',
                    '2' => 'SHA1',
                    '1' => 'MD5',
                    '0' => 'None'
                ),
                'marked' => '2'
            ),
            array (
                'type' => 'text',
                'name' => 'password_salt',
                'label' => 'Password Salt:',
                'attributes' => array ('size', 40)
            ),
            array (
                'type' => 'text',
                'name' => 'ip_allowed',
                'label' => 'IPs Allowed:',
                'attributes' => array ('size', 40)
            ),
            array (
                'type' => 'text',
                'name' => 'ip_blocked',
                'label' => 'IPs Blocked:',
                'attributes' => array ('size', 40)
            ),
            array (
                'type' => 'text',
                'name' => 'log_emails',
                'label' => 'Log Emails:',
                'attributes' => array ('size', 40)
            ),
            array (
                'type' => 'text',
                'name' => 'log_exclude',
                'label' => 'Log Exclude:',
                'attributes' => array ('size', 40)
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

        if ($_POST) {
            if ($this->id == 2001) {
                $this->getElement('type')
                    ->addValidator(new Validator\Equal('User', "The type name for this user type cannot change and must be 'User'."));
            }
            if ($this->password_encryption == 3) {
                $this->getElement('password_salt')
                     ->addValidator(new Validator\NotEmpty(null, 'The crypt password encryption requires a password salt.'));
            }
        }
    }

}

