<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class FieldGroup extends \Phire\Model\AbstractModel
{

    /**
     * Get all groups method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $groups = Table\FieldGroups::findAll($order['field'] . ' ' . $order['order']);

        if ($this->data['acl']->isAuth('Phire\Controller\Structure\GroupsController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_groups[]" id="remove_groups[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_groups" />';
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

        if ($this->data['acl']->isAuth('Phire\Controller\Structure\GroupsController', 'edit')) {
            $name = '<a href="' . BASE_PATH . APP_URI . '/structure/fields/groups/edit/[{id}]">[{name}]</a>';
        } else {
            $name = '[{name}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'field-group-remove-form',
                'action'  => BASE_PATH . APP_URI . '/structure/fields/groups/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/structure/fields/groups?sort=id">#</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/structure/fields/groups?sort=name">' . $this->i18n->__('Name') . '</a>',
                    'order'   => '<a href="' . BASE_PATH . APP_URI . '/structure/fields/groups?sort=order">' . $this->i18n->__('Order') . '</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'separator' => '',
            'name'      => $name,
            'indent'    => '        ',
            'exclude'   => 'dynamic'
        );

        if (isset($groups->rows[0])) {
            $this->data['table'] = Html::encode($groups->rows, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get group by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $group = Table\FieldGroups::findById($id);
        if (isset($group->id)) {
            $this->data = array_merge($this->data, $group->getValues());
        }
    }

    /**
     * Save group
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $group = new Table\FieldGroups(array(
            'name'    => $fields['name'],
            'order'   => (int)$fields['order'],
            'dynamic' => (int)$fields['dynamic']
        ));

        $group->save();
        $this->data['id'] = $group->id;
    }

    /**
     * Update group
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $group = Table\FieldGroups::findById($fields['id']);
        $group->name    = $fields['name'];
        $group->order   = (int)$fields['order'];
        $group->dynamic = (int)$fields['dynamic'];
        $group->update();
        $this->data['id'] = $group->id;
    }

    /**
     * Remove groups
     *
     * @param array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_groups'])) {
            foreach ($post['remove_groups'] as $id) {
                $group = Table\FieldGroups::findById($id);
                if (isset($group->id)) {
                    $group->delete();
                }
            }
        }
    }

}

