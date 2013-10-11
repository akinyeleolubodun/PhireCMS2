<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class Navigation extends AbstractContentModel
{

    /**
     * Get all content navigation method
     *
     * @param  string  $sort
     * @param  string  $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $navigation = Table\Navigation::findAll($order['field'] . ' ' . $order['order']);

        $this->data['navigation'] = $navigation->rows;

        if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\NavigationController', 'remove'))) {
            $removeCheckbox = '<input type="checkbox" name="remove_navigation[]" id="remove_navigation[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_navigation" />';
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

        $options = array(
            'form' => array(
                'id'      => 'navigation-remove-form',
                'action'  => BASE_PATH . APP_URI . '/content/navigation/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'       => '<a href="' . BASE_PATH . APP_URI . '/content/navigation?sort=id">#</a>',
                    'navigation' => '<a href="' . BASE_PATH . APP_URI . '/content/navigation?sort=navigation">Navigation</a>',
                    'process'  => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'indent' => '        '
        );

        $navAry = array();
        $navs = $this->navigation;
        foreach ($navs as $id => $nav) {
            if (isset($this->data['acl']) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\NavigationController', 'edit'))) {
                $nv = '<a href="' . BASE_PATH . APP_URI . '/content/navigation/edit/' . $nav->id . '">' . $nav->navigation . '</a>';
            } else {
                $nv = $nav->navigation;
            }
            $navAry[] = array(
                'id' => $nav->id,
                'navigation' => $nv
            );
        }

        if (isset($navAry[0])) {
            $table = Html::encode($navAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            $this->data['table'] = $table;
        }
    }

    /**
     * Get content navigation by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $navigation = Table\Navigation::findById($id);

        if (isset($navigation->id)) {
            $navigationValues = $navigation->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $navigationValues = array_merge($navigationValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $navigationValues);
        }
    }

    /**
     * Save content navigation
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $navigation = new Table\Navigation(array(
            'navigation'        => $fields['navigation'],
            'spaces'            => (($fields['spaces'] != '') ? (int)$fields['spaces'] : null),
            'top_node'          => (($fields['top_node'] != '') ? $fields['top_node'] : null),
            'top_id'            => (($fields['top_id'] != '') ? $fields['top_id'] : null),
            'top_class'         => (($fields['top_class'] != '') ? $fields['top_class'] : null),
            'top_attributes'    => (($fields['top_attributes'] != '') ? $fields['top_attributes'] : null),
            'parent_node'       => (($fields['parent_node'] != '') ? $fields['parent_node'] : null),
            'parent_id'         => (($fields['parent_id'] != '') ? $fields['parent_id'] : null),
            'parent_class'      => (($fields['parent_class'] != '') ? $fields['parent_class'] : null),
            'parent_attributes' => (($fields['parent_attributes'] != '') ? $fields['parent_attributes'] : null),
            'child_node'        => (($fields['child_node'] != '') ? $fields['child_node'] : null),
            'child_id'          => (($fields['child_id'] != '') ? $fields['child_id'] : null),
            'child_class'       => (($fields['child_class'] != '') ? $fields['child_class'] : null),
            'child_attributes'  => (($fields['child_attributes'] != '') ? $fields['child_attributes'] : null),
            'on_class'          => (($fields['on_class'] != '') ? $fields['on_class'] : null),
            'off_class'         => (($fields['off_class'] != '') ? $fields['off_class'] : null)
        ));

        $navigation->save();
        $this->data['id'] = $navigation->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $navigation->id);
        }
    }

    /**
     * Update content navigation
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();
        $navigation = Table\Navigation::findById($fields['id']);
        $navigation->navigation  = $fields['navigation'];
        $navigation->spaces            = (($fields['spaces'] != '') ? (int)$fields['spaces'] : null);
        $navigation->top_node          = (($fields['top_node'] != '') ? $fields['top_node'] : null);
        $navigation->top_id            = (($fields['top_id'] != '') ? $fields['top_id'] : null);
        $navigation->top_class         = (($fields['top_class'] != '') ? $fields['top_class'] : null);
        $navigation->top_attributes    = (($fields['top_attributes'] != '') ? $fields['top_attributes'] : null);
        $navigation->parent_node       = (($fields['parent_node'] != '') ? $fields['parent_node'] : null);
        $navigation->parent_id         = (($fields['parent_id'] != '') ? $fields['parent_id'] : null);
        $navigation->parent_class      = (($fields['parent_class'] != '') ? $fields['parent_class'] : null);
        $navigation->parent_attributes = (($fields['parent_attributes'] != '') ? $fields['parent_attributes'] : null);
        $navigation->child_node        = (($fields['child_node'] != '') ? $fields['child_node'] : null);
        $navigation->child_id          = (($fields['child_id'] != '') ? $fields['child_id'] : null);
        $navigation->child_class       = (($fields['child_class'] != '') ? $fields['child_class'] : null);
        $navigation->child_attributes  = (($fields['child_attributes'] != '') ? $fields['child_attributes'] : null);
        $navigation->on_class          = (($fields['on_class'] != '') ? $fields['on_class'] : null);
        $navigation->off_class         = (($fields['off_class'] != '') ? $fields['off_class'] : null);
        $navigation->update();

        $this->data['id'] = $navigation->id;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $navigation->id);
        }
    }

}

