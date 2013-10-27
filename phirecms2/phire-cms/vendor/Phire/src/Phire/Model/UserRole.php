<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\Dir;
use Phire\Table;

class UserRole extends AbstractModel
{

    /**
     * Static method to get model types
     *
     * @param  \Pop\Config $config
     * @return array
     */
    public static function getResources($config = null)
    {
        $resources = array();
        $exclude = array();
        $override = null;

        // Get any exclude or override config values
        if (null !== $config) {
            $configAry = $config->asArray();
            if (isset($configAry['exclude'])) {
                $exclude = $configAry['exclude'];
            }
            if (isset($configAry['override'])) {
                $override = $configAry['override'];
            }
        }

        // If override, set overridden resources
        if (null !== $override) {
            foreach ($override as $resource) {
                $resources[] = $resource;
            }
        // Else, get all controllers from the system and module directories
        } else {
            $systemDirectory = new Dir(realpath(__DIR__ . '/../../../../'), true);
            $systemModuleDirectory = new Dir(realpath(__DIR__ . '/../../../../../module/'), true);
            $moduleDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules'), true);
            $dirs = array_merge($systemDirectory->getFiles(), $systemModuleDirectory->getFiles(), $moduleDirectory->getFiles());
            sort($dirs);

            // Dir clean up
            foreach ($dirs as $key => $dir) {
                unset($dirs[$key]);
                if (!((strpos($dir, 'config') !== false) || (strpos($dir, 'index.html') !== false))) {
                    $k = $dir;
                    if (substr($dir, -1) == DIRECTORY_SEPARATOR) {
                        $k = substr($k, 0, -1);
                    }
                    $k = substr($k, (strrpos($k, DIRECTORY_SEPARATOR) + 1));
                    $dirs[$k] = $dir;
                }
            }

            // Loop through each directory, looking for controller class files
            foreach ($dirs as $mod => $dir) {
                if (file_exists($dir . 'src/' . $mod . '/Controller')) {
                    $d = new Dir($dir . 'src/' . $mod . '/Controller', true, true, false);
                    $dFiles = $d->getFiles();
                    sort($dFiles);

                    // If found, loop through the files, getting the methods as the "permissions"
                    foreach ($dFiles as $c) {
                        if ((strpos($c, 'index.html') === false) && (strpos($c, 'Abstract') === false)) {
                            // Get all public methods from class
                            $class = str_replace(array('.php', DIRECTORY_SEPARATOR), array('', '\\'), substr($c, (strpos($c, 'src') + 4)));
                            $code = new \ReflectionClass($class);
                            $methods = ($code->getMethods(\ReflectionMethod::IS_PUBLIC));

                            $actions = array();
                            foreach ($methods as $value) {
                                if (($value->getName() !== '__construct') && ($value->class == $class)) {
                                    $action = $value->getName();
                                    if (!isset($exclude[$class]) ||
                                        (isset($exclude[$class]) && (is_array($exclude[$class])) && (!in_array($action, $exclude[$class])))) {
                                        $actions[] = $action;
                                    }
                                }
                            }

                            $types = array(0 => '(All)');

                            if ($class != 'Phire\Controller\IndexController') {
                                $classAry = explode('\\', $class);
                                $end1 = count($classAry) - 2;
                                $end2 = count($classAry) - 1;
                                $model = $classAry[0] . '_Model_';
                                if (stripos($classAry[$end2], 'index') !== false) {
                                    $model .= $classAry[$end1];
                                } else if (substr($classAry[$end2], 0, 4) == 'Type') {
                                    $model .= $classAry[$end1] . 'Type';
                                } else {
                                    $model .= str_replace('Controller', '', $classAry[$end2]);

                                }

                                if (substr($model, -3) == 'ies') {
                                    $model = substr($model, 0, -3) . 'y';
                                } else if (substr($model, -1) == 's') {
                                    $model = substr($model, 0, -1);
                                }
                                $types = \Phire\Project::getModelTypes($model);
                            }


                            // Format the resource and permissions
                            $c = str_replace(array('Controller.php', '\\'), array('', '/'), $c);
                            $c = substr($c, (strpos($c, 'Controller') + 11));
                            $c = str_replace('Phire/', '', $c);

                            if (!in_array($class, $exclude) || (isset($exclude[$class]) && is_array($exclude[$class]))) {
                                $resources[$class] = array(
                                    'name'    => $c,
                                    'types'   => $types,
                                    'actions' => $actions
                                );
                            }
                        }
                    }
                }
            }
        }

        return $resources;
    }

    /**
     * Get all roles method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);

        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'user_roles.id' : $order['field'];

        // Create SQL object to get role data
        $sql = Table\UserRoles::getSql();
        $sql->select(array(
            DB_PREFIX . 'user_roles.id',
            DB_PREFIX . 'user_roles.type_id',
            DB_PREFIX . 'user_types.type',
            DB_PREFIX . 'user_roles.name'
        ))->join(DB_PREFIX . 'user_types', array('type_id', 'id'), 'LEFT JOIN')
          ->orderBy($order['field'], $order['order']);

        // Execute SQL query
        $roles = Table\UserRoles::execute($sql->render(true));

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\RolesController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_roles[]" id="remove_roles[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_roles" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove'
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove',
                'style' => 'display: none;'
            );
        }

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\RolesController', 'edit')) {
            $name = '<a href="' . BASE_PATH . APP_URI . '/users/roles/edit/[{id}]">[{name}]</a>';
        } else {
            $name = '[{name}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'role-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/roles/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/users/roles?sort=id">#</a>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/users/roles?sort=type">Type</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/users/roles?sort=name">Role</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array('type_id', 'process' => array('id' => $this->data['user']->role_id)),
            'name'    => $name,
            'indent'  => '        '
        );

        if (isset($roles->rows[0])) {
            $this->data['table'] = Html::encode($roles->rows, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get role by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $role = Table\UserRoles::findById($id);
        if (isset($role->id)) {
            $roleValues = $role->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $roleValues = array_merge($roleValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $roleValues);
        }
    }

    /**
     * Save role
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $role = new Table\UserRoles(array(
            'type_id' => $fields['type_id'],
            'name'    => $fields['name']
        ));

        $role->save();
        $this->data['id'] = $role->id;

        // Add new permissions if any
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'resource_new_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                if ($value != '0') {
                    $perm = (($_POST['permission_new_' . $id] != '0') ? $_POST['permission_new_' . $id] : '');
                    if ($perm != '') {
                        $perm .= (($_POST['type_new_' . $id] != '0') ? '_' . $_POST['type_new_' . $id] : '');
                    }
                    $permission = new Table\UserPermissions(array(
                        'role_id'    => $role->id,
                        'resource'   => $value,
                        'permission' => $perm,
                        'allow'      => (int)$_POST['allow_new_' . $id]
                    ));
                    $permission->save();
                }
            }
        }

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $role->id);
        }
    }

    /**
     * Update role
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $role = Table\UserRoles::findById($fields['id']);
        if (isset($role->id)) {
            $role->type_id = $fields['type_id'];
            $role->name    = $fields['name'];
            $role->update();

            $this->data['id'] = $role->id;
        }

        // Delete all resource/permissions to re-enter them
        $permissions = new Table\UserPermissions();
        $permissions->delete(array('role_id' => $role->id));

        // Add new permissions if any
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'resource_new_') !== false) || (strpos($key, 'resource_cur_') !== false)) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $cur = (strpos($key, 'resource_new_') !== false) ? 'new' : 'cur';
                if ($value != '0') {
                    $perm = (($_POST['permission_' . $cur . '_' . $id] != '0') ? $_POST['permission_' . $cur . '_' . $id] : '');
                    if ($perm != '') {
                        $perm .= (($_POST['type_' . $cur . '_' . $id] != '0') ? '_' . $_POST['type_' . $cur . '_' . $id] : '');
                    }
                    $permission = new Table\UserPermissions(array(
                        'role_id'    => $role->id,
                        'resource'   => $value,
                        'permission' => $perm,
                        'allow'      => (int)$_POST['allow_' . $cur . '_' . $id]
                    ));
                    $permission->save();
                }
            }
        }

        // Remove and resource/permissions
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'rm_resource_') !== false) && isset($value[0])) {
                $ids = explode('_', $value[0]);
                $perm = (count($ids) == 4) ? $ids[2] . '_' . $ids[3] : $ids[2];
                $permission = Table\UserPermissions::findById(array($ids[0], $ids[1], $perm));
                if (isset($permission->role_id)) {
                    $permission->delete();
                }
            }
        }

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $role->id);
        }

    }

    /**
     * Remove user role
     *
     * @param  array   $post
     * @param  boolean $isFields
     * @return void
     */
    public function remove(array $post, $isFields = false)
    {
        if (isset($post['remove_roles'])) {
            foreach ($post['remove_roles'] as $id) {
                $role = Table\UserRoles::findById($id);
                if (isset($role->id)) {
                    $role->delete();
                }

                $sql = Table\UserTypes::getSql();

                if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
                    $sql->update(array(
                        'default_role_id' => null
                    ))->where()->equalTo('default_role_id', $role->id);
                    Table\UserTypes::execute($sql->render(true));
                }

                // If the Fields module is installed, and if there are fields for this form/model
                if ($isFields) {
                    \Fields\Model\FieldValue::remove($id);
                }
            }
        }
    }

}

