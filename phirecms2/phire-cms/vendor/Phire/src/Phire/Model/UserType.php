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
                'value' => $this->i18n->__('Remove')
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove'),
                'style' => 'display: none;'
            );
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
                    'edit'    => '<span style="display: block; margin: 0 auto; width: 100%; text-align: center;">' . $this->i18n->__('Edit') . '</span>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/users/types?sort=type">' . $this->i18n->__('Type') . '</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'separator' => '',
            'exclude'   => array(
                'process' => array('id' => $this->data['user']->type_id)
            ),
            'indent'    => '        '
        );

        if (isset($types->rows[0])) {
            $typeRows = array();
            foreach ($types->rows as $type) {
                if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\TypesController', 'edit')) {
                    $edit = '<a class="edit-link" title="' . $this->i18n->__('Edit') . '" href="' . BASE_PATH . APP_URI . '/users/types/edit/' . $type->id . '">Edit</a>';
                } else {
                    $edit = null;
                }

                $type->type = ucwords(str_replace('-', ' ', $type->type));
                $tAry = array(
                    'id'   => $type->id,
                    'type' => $type->type
                );

                if (null !== $edit) {
                    $tAry['edit'] = $edit;
                }

                $typeRows[] = $tAry;
            }
            $this->data['table'] = Html::encode($typeRows, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get type by ID method
     *
     * @param  int     $id
     * @return void
     */
    public function getById($id)
    {
        $type = Table\UserTypes::findById($id);
        if (isset($type->id)) {
            $typeValues = $type->getValues();
            $typeValues['log_in'] = $typeValues['login'];
            unset($typeValues['login']);
            $typeValues = array_merge($typeValues, FieldValue::getAll($id));

            $this->data = array_merge($this->data, $typeValues);
        }
    }

    /**
     * Save type
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $fields['type'] = String::slug($fields['type']);
        $fields['login'] = $fields['log_in'];
        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        if ($fields['default_role_id'] == 0) {
            $fields['default_role_id'] = null;
        }

        unset($fields['log_in']);
        unset($fields['id']);
        unset($fields['submit']);
        unset($fields['update_value']);
        unset($fields['update']);

        $fieldsAry = array();
        foreach ($fields as $key => $value) {
            if (substr($key, 0, 6) == 'field_') {
                $fieldsAry[$key] = $value;
                unset($fields[$key]);
            }
        }

        $type = new Table\UserTypes($fields);
        $type->save();
        $this->data['id'] = $type->id;

        FieldValue::save($fieldsAry, $type->id);
    }

    /**
     * Update type
     *
     * @param \Pop\Form\Form $form
     * @param  \Pop\Config   $config
     * @return void
     */
    public function update(\Pop\Form\Form $form, $config)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $fields['type'] = String::slug($fields['type']);
        $fields['login'] = $fields['log_in'];
        $fields['log_emails'] = str_replace(', ', ',', $fields['log_emails']);
        $fields['log_exclude'] = str_replace(', ', ',', $fields['log_exclude']);

        if ($fields['default_role_id'] == 0) {
            $fields['default_role_id'] = null;
        }

        unset($fields['log_in']);
        unset($fields['submit']);
        unset($fields['update_value']);
        unset($fields['update']);

        $type = Table\UserTypes::findById($form->id);
        $oldEnc = $type->password_encryption;
        $newEnc = $fields['password_encryption'];

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
        $this->data['id'] = $type->id;

        // If the password encryption changed
        if ($oldEnc != $newEnc) {
            $users = Table\Users::findAll(null, array('type_id' => $type->id));
            foreach ($users->rows as $u) {
                $user = Table\Users::findById($u->id);
                if (isset($user->id)) {
                    $u = new User();
                    $u->sendReminder($user->email, $config);
                }
            }
        }

        FieldValue::update($fieldsAry, $type->id);
    }

    /**
     * Remove user type
     *
     * @param  array   $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_types'])) {
            foreach ($post['remove_types'] as $id) {
                $type = Table\UserTypes::findById($id);
                if (isset($type->id)) {
                    \Phire\Table\Fields::deleteByType($type->id);
                    $type->delete();
                }
            }
        }
    }

}

