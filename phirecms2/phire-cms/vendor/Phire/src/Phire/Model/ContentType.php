<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class ContentType extends AbstractContentModel
{

    /**
     * Get all content types method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $types = Table\ContentTypes::findAll($order['field'] . ' ' . $order['order']);

        $options = array(
            'form' => array(
                'id'      => 'content-type-remove-form',
                'action'  => BASE_PATH . APP_URI . '/content/types/remove',
                'method'  => 'post',
                'process' => '<input type="checkbox" name="remove_types[]" id="remove_types[{i}]" value="[{id}]" />',
                'submit'  => array(
                    'class' => 'remove-btn',
                    'value' => 'Remove'
                )
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/content/types?sort=id">#</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/content/types?sort=name">Name</a>',
                    'process' => '<input type="checkbox" id="checkall" name="checkall" value="remove_types" />'
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array('uri'),
            'name' => '<a href="' . BASE_PATH . APP_URI . '/content/types/edit/[{id}]">[{name}]</a>'
        );

        if (isset($types->rows[0])) {
            $this->data['table'] = Html::encode($types->rows, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }

        $this->data['types'] = $types->rows;
    }

    /**
     * Get content type by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $type = Table\ContentTypes::findById($id);
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
     * Save content type
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $type = new Table\ContentTypes(array(
            'name'  => $fields['name'],
            'uri'   => (int)$fields['uri'],
            'order' => (int)$fields['order']
        ));

        $type->save();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $type->id);
        }
    }

    /**
     * Update content type
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $type = Table\ContentTypes::findById($fields['id']);
        $type->name  = $fields['name'];
        $type->uri   = (int)$fields['uri'];
        $type->order = (int)$fields['order'];
        $type->update();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $type->id);
        }
    }

}

