<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\Filter\String;
use Phire\Table;

class UserType extends AbstractModel
{

    /**
     * Get all types method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);

        $sql = Table\UserTypes::getSql();
        $sql->select(array(DB_PREFIX . 'user_types.id', DB_PREFIX . 'user_types.type'))
            ->orderBy($order['field'], $order['order']);

        $types = Table\UserTypes::execute($sql->render(true));

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\TypesController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_types[]" id="remove_types[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_types" />';
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

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\TypesController', 'edit')) {
            $type = '<a href="' . BASE_PATH . APP_URI . '/users/types/edit/[{id}]">[{type}]</a>';
        } else {
            $type = '[{type}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'type-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/types/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/users/types?sort=id">#</a>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/users/types?sort=type">Type</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array(
                'process' => array('id' => $this->data['user']->type_id)
            ),
            'type' => $type
        );

        if (isset($types->rows[0])) {
            $this->data['table'] = Html::encode($types->rows, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get type by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $type = Table\UserTypes::findById($id);
        if (isset($type->id)) {
            $typeValues = $type->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $typeValues = array_merge($typeValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $typeValues);
        }
    }

    /**
     * Save type
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $fields['type'] = String::slug($fields['type']);
        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        if ($fields['default_role_id'] == 0) {
            $fields['default_role_id'] = null;
        }

        unset($fields['id']);
        unset($fields['submit']);

        $fieldsAry = array();
        foreach ($fields as $key => $value) {
            if (substr($key, 0, 6) == 'field_') {
                $fieldsAry[$key] = $value;
                unset($fields[$key]);
            }
        }

        $type = new Table\UserTypes($fields);
        $type->save();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fieldsAry, $type->id);
        }
    }

    /**
     * Update type
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $fields['type'] = String::slug($fields['type']);
        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        if ($fields['default_role_id'] == 0) {
            $fields['default_role_id'] = null;
        }

        unset($fields['submit']);

        $type = Table\UserTypes::findById($form->id);

        // If the password encryption changed
        if ($type->password_encryption != $fields['password_encryption']) {
            $users = Table\Users::findAll(null, array('type_id' => $type->id));
            foreach ($users->rows as $u) {
                $user = Table\Users::findById($u->id);
                if (isset($user->id)) {
                    switch ($fields['password_encryption']) {
                        case 0:
                            $fields['password_salt'] = '';
                            $newPassword = $this->password;
                            break;
                        case 1;
                            $fields['password_salt'] = '';
                            $newPassword = md5((string)String::random(8, String::ALPHANUM));
                            break;
                        case 2:
                            $fields['password_salt'] = '';
                            $newPassword = sha1((string)String::random(8, String::ALPHANUM));
                            break;
                        case 3:
                            $newPassword = crypt((string)String::random(8, String::ALPHANUM), $fields['password_salt']);
                            break;
                    }

                    $user->password = $newPassword;
                    $user->save();
                }
            }
        }

        // Extract dynamic field values out of the form
        $fieldsAry = array();
        foreach ($fields as $key => $value) {
            if (substr($key, 0, 6) == 'field_') {
                $fieldsAry[$key] = $value;
                unset($fields[$key]);
            }
        }

        // Save updated type fields
        $type->setValues($fields);
        $type->update();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fieldsAry, $type->id);
        }
    }

}

