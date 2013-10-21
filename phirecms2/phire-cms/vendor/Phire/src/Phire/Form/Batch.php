<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;

class Batch extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  int $tid
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0)
    {
        $fields1 = array(
            'file_name_1' => array(
                'type'       => 'file',
                'label'      => '<a href="#" onclick="addBatchFields(); return false;">[+]</a> File / Title:',
                'attributes' => array(
                    'size' => 40,
                    'style' => 'display: block; margin: 0 0 10px 0;'
                )
            ),
            'file_title_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 40,
                    'style' => 'display: block; margin: 0 0 10px 0;'
                )
            )
        );

        $formats = \Pop\Archive\Archive::formats();

        if (count($formats) > 0) {
            $fields2 = array(
                'archive_file' => array(
                    'type'       => 'file',
                    'label'      => 'Archive of Multiple Files:<br /><span style="display: block; margin: 5px 0 0 0; font-size: 0.9em;"><strong>Supported Types:</strong> ' . implode(', ', array_keys($formats)) . '</span>',
                    'attributes' => array('size' => 40)
                )
            );
            $fields3 = array(
                'type_id' => array(
                    'type'  => 'hidden',
                    'value' => $tid
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'label' => '&nbsp;',
                    'value' => 'UPLOAD',
                    'attributes' => array(
                        'class' => 'save-btn'
                    )
                )
            );
            $this->initFieldsValues = array($fields1, $fields2, $fields3);
        } else {
            $fields1['type_id'] = array(
                'type'  => 'hidden',
                'value' => $tid
            );
            $fields1['submit'] = array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'UPLOAD',
                'attributes' => array(
                    'class' => 'save-btn'
                )
            );
            $this->initFieldsValues = $fields1;
        }

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'batch-form')
             ->setAttributes('onsubmit', 'showLoading();');
    }

}

