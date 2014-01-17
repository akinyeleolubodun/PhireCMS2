<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table\UserRoles;
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
     * @return self
     */
    public function __construct($action = null, $method = 'post', $rid = 0, $config = null)
    {
        $this->initFieldsValues = $this->getInitFields($rid, $config);
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
     * @return array
     */
    protected function getInitFields($rid = 0, $config = null)
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
        $fieldGroups = array();

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $rid);
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        // Get available resources with their corresponding permissions
        $resources = \Phire\Model\UserRole::getResources($config);
        $classes = array('0' => '(All)');
        $classTypes = array();
        $classActions = array();
        foreach ($resources as $key => $resource) {
            $classes[$key] = $resource['name'];
            $classTypes[$key] = array('0' => '(All)');
            $classActions[$key] = array('0' => '(All)');
            foreach ($resource['types'] as $id => $type) {
                if ((int)$id != 0) {
                    $classTypes[$key][$id] = $type;
                }
            }
            foreach ($resource['actions'] as $permAction) {
                $classActions[$key][$permAction] = $permAction;
            }
        }

        // Get any current resource/permission fields
        if ($rid != 0) {
            $role = UserRoles::findById($rid);
            $permissions = (null !== $role->permissions) ? unserialize($role->permissions) : array();
            $i = 1;
            foreach ($permissions as $permission) {
                if (strpos($permission['permission'], '_') !== false) {
                    $permAry = explode('_', $permission['permission']);
                    $p = $permAry[0];
                    $t = $permAry[1];
                } else {
                    $p = $permission['permission'];
                    $t = '0';
                }
                $fields2['resource_cur_' . $i] = array(
                    'type'       => 'select',
                    'label'      => "&nbsp;",
                    'value'      => $classes,
                    'marked'     => $permission['resource'],
                    'attributes' => array(
                        'onchange' => 'phire.changePermissions(this);',
                        'style' => 'display: block;'
                    ),
                );
                $fields2['permission_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => $classActions[$permission['resource']],
                    'marked'     => $p,
                    'attributes' => array('style' => 'display: block; min-width: 150px;')
                );
                $fields2['type_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => $classTypes[$permission['resource']],
                    'marked'     => $t,
                    'attributes' => array('style' => 'display: block; min-width: 150px;')
                );
                $fields2['allow_cur_' . $i] = array(
                    'type'       => 'select',
                    'value'      => array(
                        '1' => 'allow',
                        '0' => 'deny'
                    ),
                    'marked'     => $permission['allow'],
                    'attributes' => array('style' => 'display: block; min-width: 150px;')
                );
                $fields2['rm_resource_' . $i] = array(
                    'type'       => 'checkbox',
                    'value'      => array($rid . '_' . $permission['resource'] . '_' . $permission['permission'] => 'Remove?')
                );
                $i++;
            }
        }

        // Create new resource/permission fields
        $fields3 = array(
            'resource_new_1' => array(
                'type'       => 'select',
                'label'      => '<span class="label-pad-2"><a href="#" onclick="phire.addResource(); return false;">[+]</a> Resource:</span><span class="label-pad-2">Action:</span><span class="label-pad-2">Type:</span><span class="label-pad-2">Permission:</span>',
                'attributes' => array(
                    'onchange' => 'phire.changePermissions(this);',
                    'style' => 'display: block; margin: 3px 0 3px 0;'
                ),
                'value'      => $classes
            ),
            'permission_new_1' => array(
                'type'       => 'select',
                'attributes' => array('style' => 'display: block; min-width: 150px; margin: 3px 0 3px 0;'),
                'value'      => array('0' => '(All)')
            ),
            'type_new_1' => array(
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
                    'onclick' => "return phire.updateForm('#user-role-form', true);",
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

        $allFields = array($fields4, $fields1);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }
        $allFields[] = $fields3;
        $allFields[] = $fields2;

        return $allFields;
    }
}

