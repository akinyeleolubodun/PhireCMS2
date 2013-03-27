<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table\Permissions;
use Phire\Table\Types;

class Role extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @param  int    $rid
     * @return self
     */
    public function __construct($action, $method, array $fields = null, $indent = null, $rid = 0)
    {
        $fields2 = array();
        $typesAry = array();
        $types = Types::findAll('id ASC');
        foreach ($types->rows as $type) {
            $typesAry[$type->id] = $type->type;
        }

        if ($rid != 0) {
            $permissions = Permissions::findAll('id ASC', array('role_id' => $rid));
            foreach ($permissions->rows as $permission) {
                $fields2[] = array (
                    'type' => 'text',
                    'name' => 'resource_' . $permission->id,
                    'label' => "Resource / Permissions:",
                    'value' => $permission->resource,
                    'attributes' => array ('size', 10)
                );
                $fields2[] = array(
                    'type' => 'text',
                    'name' => 'permissions_' . $permission->id,
                    'value' => $permission->permissions,
                    'attributes' => array ('size', 25)
                );
                $fields2[] = array(
                    'type' => 'checkbox',
                    'name' => 'rm_permission_' . $permission->id,
                    'value' => array($permission->id => 'Remove?'),
                    'attributes' => array ('size', 25)
                );
            }
        }

        $fields1 = array (
            array(
                'type' => 'select',
                'name' => 'type_id',
                'required' => true,
                'label' => 'User Type:',
                'value' => $typesAry
            ),
            array (
                'type' => 'text',
                'name' => 'name',
                'label' => 'Name:',
                'required' => true,
                'attributes' => array ('size', 40)
            )
        );

        $fields3 = array (
            array (
                'type' => 'text',
                'name' => 'resource_new',
                'label' => "New Resource / Permissions:<br />(i.e. users / add,edit,remove)",
                'attributes' => array ('size', 10)
            ),
            array (
                'type' => 'text',
                'name' => 'permissions_new',
                'attributes' => array ('size', 25)
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

        parent::__construct($action, $method, $fields, $indent);
    }

}

