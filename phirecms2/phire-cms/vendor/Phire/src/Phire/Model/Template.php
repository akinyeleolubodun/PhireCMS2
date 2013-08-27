<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Form;
use Phire\Table;

class Template extends AbstractContentModel
{

    /**
     * Get all templates method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $templates = Table\Templates::findAll($order['field'] . ' ' . $order['order']);
        $templateAry = array();

        foreach ($templates->rows as $template) {
            if (null === $template->parent_id) {
                $tmplAry = array(
                    'template' => $template,
                    'children' => array()
                );
                $children = Table\Templates::findAll('id ASC', array('parent_id' => $template->id));
                foreach ($children->rows as $child) {
                    $tmplAry['children'][] = $child;
                }
                $templateAry[] = $tmplAry;
            }
        }

        $options = array(
            'form' => array(
                'id'      => 'template-remove-form',
                'action'  => BASE_PATH . APP_URI . '/content/templates/remove',
                'method'  => 'post',
                'process' => '<input type="checkbox" name="remove_templates[]" id="remove_templates[{i}]" value="[{id}]" />',
                'submit'  => array(
                    'class' => 'remove-btn',
                    'value' => 'Remove'
                )
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/content/templates?sort=id">#</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/content/templates?sort=name">Name</a>',
                    'process' => '<input type="checkbox" id="checkall" name="checkall" value="remove_templates" />'
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array('parent_id', 'template'),
            'name' => '<a href="' . BASE_PATH . APP_URI . '/content/templates/edit/[{id}]">[{name}]</a>'
        );

        // Get template children
        $tmplAry = array();
        $devices = Form\Template::getMobileTemplates();
        if (isset($templateAry[0])) {
            foreach ($templateAry as $tmpl) {
                $t = (array)$tmpl['template'];
                $t['device'] = $devices[$t['device']];
                $tmplAry[] = $t;

                // Get child templates
                if (count($tmpl['children']) > 0) {
                    foreach ($tmpl['children'] as $child) {
                        $c = (array)$child;
                        $c['name'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt; ' . $c['name'];
                        $c['device'] = $devices[$c['device']];
                        $tmplAry[] = $c;
                    }
                }
            }

            $table = Html::encode($tmplAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            $tableLines = explode(PHP_EOL, $table);

            // Clean up the table
            foreach ($tableLines as $key => $value) {
                if (strpos($value, '">&') !== false) {
                    $str = substr($value, (strpos($value, '">&') + 2));
                    $str = substr($str, 0, (strpos($str, ' ') + 1));
                    $value = str_replace($str, '', $value);
                    $tableLines[$key] = str_replace('<td><a', '<td>' . $str . '<a', $value);
                }
            }
            $this->data['table'] = implode(PHP_EOL, $tableLines);
        }

        $this->data['templates'] = $templateAry;
    }

    /**
     * Get template by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $template = Table\Templates::findById($id);

        if (isset($template->id)) {
            $templateValues = $template->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $templateValues = array_merge($templateValues, \Fields\Model\FieldValue::getAll($id));
            }

            $this->data = array_merge($this->data, $templateValues);
        }
    }

    /**
     * Save template
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $template = new Table\Templates(array(
            'parent_id'    => (((int)$fields['parent_id'] != 0) ? (int)$fields['parent_id'] : null),
            'name'         => $fields['name'],
            'content_type' => $fields['content_type'],
            'device'       => $fields['device'],
            'template'     => $fields['template']
        ));

        $template->save();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $template->id);
        }
    }

    /**
     * Update template
     *
     * @param \Pop\Form\Form $form
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $template = Table\Templates::findById($fields['id']);
        $template->parent_id    = (((int)$fields['parent_id'] != 0) ? (int)$fields['parent_id'] : null);
        $template->name         = $fields['name'];
        $template->content_type = $fields['content_type'];
        $template->device       = $fields['device'];
        $template->template     = $fields['template'];
        $template->update();

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $template->id);
        }
    }

}

