<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\File\Dir;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;

class ContentType extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $tid
     * @param  boolean $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $isFields = false)
    {
        $this->initFieldsValues = $this->getInitFields($tid, $isFields);
        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'content-type-form');
    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  boolean $isFields
     * @return array
     */
    protected function getInitFields($tid = 0, $isFields = false)
    {
        // Create initial fields
        $fields1 = array(
            'name' => array(
                'type'       => 'text',
                'label'      => 'Name:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'uri' => array(
                'type'   => 'radio',
                'label'  => 'URI or File:',
                'value'  => array(
                    '1' => 'URI',
                    '0' => 'File'
                ),
                'marked' => 1
            )
        );

        $fields2 = array();

        // If the Phields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Phields\Model\Field::getByModel($model, 0, $tid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                }
            }
        }

        // Create additional fields
        $fields2['order'] = array(
            'type'       => 'text',
            'label'      => 'Order:',
            'attributes' => array('size' => 3),
            'value'      => 0
        );

        // If it's a redirect from an add content request
        if (isset($_GET['redirect'])) {
            $fields2['redirect'] = array(
                'type'  => 'hidden',
                'value' => 1
            );
        }

        // Create remaining fields
        $fields3 = array(
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Save'
            )
        );

        return array($fields1, $fields2, $fields3);
    }

}

