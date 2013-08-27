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
        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'user-role-form');
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
                'attributes' => array('size' => 40)
            )
        );

        // Get any existing field values
        $fields2 = array();

        // If the Phields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Phields\Model\Field::getByModel($model, 0, $rid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
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
                    'style' => 'display: block;'
                ),
                'value'      => $classes
            ),
            'permission_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; min-width: 150px;'),
                'value'      => array('0' => '(All)')
            ),
            'id' => array(
                'type' => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Save'
            )
        );

        return array($fields1, $fields2, $fields3);
    }
}
