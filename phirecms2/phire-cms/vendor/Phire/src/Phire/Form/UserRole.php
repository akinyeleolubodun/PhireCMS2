<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table\UserPermissions;
use Phire\Table\UserTypes;

class UserRole extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string      $action
     * @param  string      $method
     * @param  int         $rid
     * @param  \Pop\Config $config
     * @param  boolean     $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $rid = 0, $config = null, $isFields = false)
    {
        $this->initFieldsValues = $this->getInitFields($rid, $config, $isFields);
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'user-role-form');
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

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if (($_FILES) && isset($_FILES[$key]) && ($_FILES[$key]['error'] == 1)) {
                    $this->getElement($key)
                         ->addValidator(new Validator\LessThanEqual(-1, "The 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . " exceeded."));
                } else if ($value['error'] != 4) {
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
     * @param  int         $rid
     * @param  \Pop\Config $config
     * @param  boolean     $isFields
     * @return array
     */
    protected function getInitFields($rid = 0, $config = null, $isFields = false)
    {
        // Get types for the user role
        $typesAry = array();
        $types = UserTypes::findAll('id ASC');
        foreach ($types->rows as $type) {
            $typesAry[$type->id] = $type->type;
        }

        // Create initial fields
        $fields1 = array(
            'type_id' => array(
                'type'     => 'select',
                'required' => true,
                'label'    => 'User Type:',
                'value'    => $typesAry
            ),
            'name' => array(
                'type'       => 'text',
                'label'      => 'Name:',
                'required'   => true,
                'attributes' => array('size' => 55)
            )
        );

        // Get any existing field values
        $fields2 = array();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, 0, $rid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                    if ($value['type'] == 'file') {
                        $this->hasFile = true;
                    }
                }
            }
        }

        // Get available resources with their corresponding permissions
        $resources = \Phire\Model\UserRole::getResources($config);
        $classes = array('0' => '(All)');
        $classActions = array();
        foreach ($resources as $key => $resource) {
            $classes[$key] = $resource['name'];
            $classActions[$key] = array('0' => '(All)');
            foreach ($resource['actions'] as $permAction) {
                $classActions[$key][$permAction] = $permAction;
            }
        }

        // Get any current resource/permission fields
        if ($rid != 0) {
            $permissions = UserPermissions::findAll(null, array('role_id' => $rid));
            $i = 1;
            foreach ($permissions->rows as $permission) {
                $fields2['resource_cur_' . $i] = array(
                    'type'       => 'select',
                    'label'      => "&nbsp;",
                    'value'      => $classes,
                    'marked'     => $permission->resource,
                    'attributes' => array(
                        'onchange' => 'changePermissions(this);',
                        'style' => 'display: block;'
                    ),
                );
                $fields2['permission_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => $classActions[$permission->resource],
                    'marked'     => $permission->permission,
                    'attributes' => array('style' => 'display: block; min-width: 150px;')
                );
                $fields2['allow_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => array(
                        '1' => 'allow',
                        '0' => 'deny'
                    ),
                    'marked'     => $permission->allow,
                    'attributes' => array('style' => 'display: block; min-width: 150px;')
                );
                $fields2['rm_resource_' . $i] = array(
                    'type'       => 'checkbox',
                    'value'      => array($permission->role_id . '_' . $permission->resource . '_' . $permission->permission => 'Remove?')
                );
                $i++;
            }
        }

        // Create new resource/permission fields
        $fields3 = array(
            'resource_new_1' => array(
                'type'       => 'select',
                'label'      => '<a href="#" onclick="addResource(); return false;">[+]</a> Resource / Permission:',
                'attributes' => array(
                    'onchange' => 'changePermissions(this);',
                    'style' => 'display: block; margin: 3px 0 3px 0;'
                ),
                'value'      => $classes
            ),
            'permission_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; min-width: 150px; margin: 3px 0 3px 0;'),
                'value'      => array('0' => '(All)')
            ),
            'allow_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; min-width: 150px; margin: 3px 0 3px 0;'),
                'value'      => array(
                    '1' => 'allow',
                    '0' => 'deny'
                )
            ),
        );
        $fields4 = array(
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'SAVE',
                'attributes' => array(
                    'class'   => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return updateForm('#user-role-form', true);",
                    'class'   => 'update-btn'
                )
            ),
            'id' => array(
                'type' => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            )
        );

        return array($fields4, $fields1, $fields2, $fields3);
    }
}

