<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class UserType extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $tid
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0)
    {
        parent::__construct($action, $method, null, '        ');
        $this->initFieldsValues = $this->getInitFields($tid);
        $this->setAttributes('id', 'user-type-form');
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
                     ->addValidator(new Validator\Equal('user', $this->i18n->__("The type name for this user type cannot change and must be 'user'.")));
            }
        }

        $this->checkFiles();

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @return array
     */
    protected function getInitFields($tid = 0)
    {
        $yesNo = array('1' => $this->i18n->__('Yes'), '0' => $this->i18n->__('No'));

        // Get roles for the user type
        $roles = Table\UserRoles::findAll('id ASC', array('type_id' => $tid));
        $rolesAry = array('0' => '(' . $this->i18n->__('Blocked') . ')');
        foreach ($roles->rows as $role) {
            $rolesAry[$role->id] = $role->name;
        }

        // Set up initial fields
        $fields1 = array(
            'type' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Type') . ':',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'ip_allowed' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('IPs Allowed') . ':',
                'attributes' => array('size' => 40)
            ),
            'ip_blocked' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('IPs Blocked') . ':',
                'attributes' => array('size' => 40)
            ),
            'log_emails' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Log Emails') . ':',
                'attributes' => array('size' => 40)
            ),
            'log_exclude' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Log Exclude') . ':',
                'attributes' => array('size' => 40)
            ),
            'controller' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Controller') . ':',
                'attributes' => array('size' => 40)
            ),
            'sub_controllers' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Sub Controllers') . ':',
                'attributes' => array('size' => 40)
            )
        );
        $fields2a = array(
            'log_in' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Allow Login') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'registration' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Allow Registration') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'multiple_sessions' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Allow Multiple Sessions') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'timeout_warning' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Session Timeout Warning') . ':',
                'value'  => $yesNo,
                'marked' => '0'
            ),
            'mobile_access' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Allow Mobile Access') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'email_as_username' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Allow Email as Username') . ':',
                'value'  => $yesNo,
                'marked' => '0'
            ),
        );
        $fields2b = array(
            'email_verification' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('User Email Verification') . ':',
                'value'  => $yesNo,
                'marked' => '0'
            ),
            'force_ssl' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Force SSL') . ':',
                'value'  => $yesNo,
                'marked' => '0'
            ),
            'track_sessions' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Track Sessions') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'verification' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('System Email Verification') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'approval' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Require Approval') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            ),
            'unsubscribe_login' => array(
                'type'   => 'radio',
                'label'  => $this->i18n->__('Require Login for Unsubscribe') . ':',
                'value'  => $yesNo,
                'marked' => '1'
            )
        );

        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $tid);
        if ($newFields['dynamic']) {
            $dynamicFields = true;
        }
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        $fields4 = array();

        $fields4['submit'] = array(
            'type'  => 'submit',
            'value' => $this->i18n->__('SAVE'),
            'attributes' => array(
                'class'   => 'save-btn'
            )
        );
        $fields4['update'] = array(
            'type'       => 'button',
            'value'      => $this->i18n->__('UPDATE'),
            'attributes' => array(
                'onclick' => "return phire.updateForm('#user-type-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                'class'   => 'update-btn'
            )
        );
        $fields4['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields4['update_value'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields4['default_role_id'] = array(
            'type'   => 'select',
            'label'  => $this->i18n->__('Default Role') . ':',
            'value'  => $rolesAry,
            'attributes' => array(
                'style' => 'min-width: 200px;'
            )
        );
        $fields4['password_encryption'] = array(
            'type'  => 'select',
            'label' => $this->i18n->__('Password Encryption') . ':',
            'value' => array(
                '1' => 'MD5',
                '2' => 'SHA1',
                '3' => 'Crypt',
                '4' => 'Bcrypt',
                '5' => 'Mcrypt (2-Way)',
                '6' => 'Crypt_MD5',
                '7' => 'Crypt_SHA256',
                '8' => 'Crypt_SHA512',
                '0' => 'None'
            ),
            'marked' => '4',
            'attributes' => array(
                'style' => 'min-width: 200px;'
            )
        );
        $fields4['global_access'] = array(
            'type'   => 'select',
            'label'  => $this->i18n->__('Allow Global Access') . ':',
            'value'  => $yesNo,
            'marked' => '0'
        );
        $fields4['allowed_attempts'] = array(
            'type'       => 'text',
            'label'      => $this->i18n->__('Allowed Attempts') . ':',
            'attributes' => array('size' => 3),
            'value'      => '0'
        );
        $fields4['session_expiration'] = array(
            'type'       => 'text',
            'label'      => $this->i18n->__('Session Expiration') . ': <span style="font-size: 0.9em; font-weight: normal;">(' . $this->i18n->__('Minutes') . ')</span>',
            'attributes' => array('size' => 3),
            'value'      => '0'
        );

        $allFields = array($fields4, $fields1, $fields2a, $fields2b);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }

        return $allFields;
    }

}

