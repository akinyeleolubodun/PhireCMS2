<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class UserRoles extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'user_roles';

    /**
     * @var   string
     */
    protected $primaryId = 'id';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Get a role with its permissions
     *
     * @param  int $roleId
     * @return \Pop\Auth\Role
     */
    public static function getRole($roleId)
    {
        if ($roleId != 0) {
            $role = self::findById($roleId);
            $r = \Pop\Auth\Role::factory($role->name);

            $permissions = \Phire\Table\UserPermissions::findAll(null, array('role_id' => $role->id));

            foreach ($permissions->rows as $permission) {
                $r->addPermission($permission->permission);
            }
        } else {
            $r = \Pop\Auth\Role::factory('Blocked');
        }

        return $r;
    }

    /**
     * Get all roles, resources and permissions
     *
     * @param  int $typeId
     * @return array
     */
    public static function getAllRoles($typeId)
    {
        $results = array(
            'roles'     => array(),
            'resources' => array()
        );

        $roles = self::findAll('id ASC', array('type_id' => $typeId));
        if (isset($roles->rows[0])) {
            foreach ($roles->rows as $role) {
                $r = \Pop\Auth\Role::factory($role->name);
                $results['resources'][$role->name] = array();
                $permissions = \Phire\Table\UserPermissions::findAll(null, array('role_id' => $role->id));
                if (isset($permissions->rows[0])) {
                    foreach ($permissions->rows as $permission) {
                        if (!isset($results['resources'][$role->name][$permission->resource])) {
                            $results['resources'][$role->name][$permission->resource] = array();
                        }
                        if ($permission->permission != '') {
                            $r->addPermission($permission->permission);
                            if ($permission->resource != '') {
                                $results['resources'][$role->name][$permission->resource][] = $permission->permission;
                            }
                        }
                    }
                }
                $results['roles'][] = $r;
            }
        }

        return $results;
    }

}

