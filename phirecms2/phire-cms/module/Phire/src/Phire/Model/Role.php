<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table\UserPermissions;
use Phire\Table\UserRoles;

class Role extends \Pop\Mvc\Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return self
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);

        $sess = \Pop\Web\Session::getInstance();

        if (isset($sess->user)) {
            $this->data['user'] = $sess->user;
            $this->data['role'] = UserRoles::getRole($sess->user->role_id);
            $this->data['globalAccess'] = $sess->user->global_access;
        }
    }

    /**
     * Get all roles method
     *
     * @return void
     */
    public function getAll()
    {
        $sql = UserRoles::getSql();
        $sql->select(array(
            DB_PREFIX . 'user_roles.id',
            DB_PREFIX . 'user_roles.type_id',
            DB_PREFIX . 'user_roles.name',
            DB_PREFIX . 'user_types.type'
            ))
            ->join(DB_PREFIX . 'user_types', array('type_id', 'id'), 'LEFT JOIN')
            ->orderBy(DB_PREFIX . 'user_roles.id', 'ASC');

        $roles = UserRoles::execute($sql->render(true));
        $this->data['roles'] = $roles->rows;
    }

    /**
     * Get role by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $role = UserRoles::findById($id);
        $this->data = array_merge($this->data, $role->getValues());
    }

    /**
     * Save role
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $resource = null;
        $permissions = null;

        if ($fields['resource_new'] != '') {
            $resource = $fields['resource_new'];
            $permissions = $fields['permissions_new'];
        }

        unset($fields['resource_new']);
        unset($fields['permissions_new']);
        unset($fields['id']);
        unset($fields['submit']);

        $role = new UserRoles($fields);
        $role->save();

        // Add new permissions if any
        if (null !== $resource) {
            $permission = new UserPermissions(array(
                'role_id'     => $role->id,
                'resource'    => $resource,
                'permissions' => $permissions
            ));
            $permission->save();
        }
    }

    /**
     * Update role
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        // Edit permissions
        foreach ($fields as $key => $value) {
            $id = substr($key, (strrpos($key, '_') + 1));
            if ($id != 'new') {
                $permission = UserPermissions::findById($id);
                if (isset($permission->id) && ($permission->role_id == $fields['id'])) {
                    $permission->resource = $fields['resource_' . $id];
                    $permission->permissions = $fields['permissions_' . $id];
                    $permission->update();
                }
            }
        }

        // Remove permissions
        foreach ($fields as $key => $value) {
            if ((substr($key, 0, 3) == 'rm_') && isset($value[0])) {
                $permission = UserPermissions::findById($value[0]);
                if (isset($permission->id) && ($permission->role_id == $fields['id'])) {
                    $permission->delete();
                }
            }
        }

        $resource = null;
        $permissions = null;

        if ($fields['resource_new'] != '') {
            $resource = $fields['resource_new'];
            $permissions = $fields['permissions_new'];
            unset($fields['resource_new']);
            unset($fields['permissions_new']);
        }

        $role = UserRoles::findById($form->id);
        $role->setValues(array(
            'id'      => $fields['id'],
            'type_id' => $fields['type_id'],
            'name'    => $fields['name']
        ));
        $role->update();

        // Add new permissions if any
        if (null !== $resource) {
            $permission = new UserPermissions(array(
                'role_id'     => $role->id,
                'resource'    => $resource,
                'permissions' => $permissions
            ));
            $permission->save();
        }
    }

}

