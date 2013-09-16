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
        $this->initFieldsValues = array(
            'file_name_1' => array(
                'type'       => 'file',
                'label'      => '<a href="#" onclick="addBatchFields(); return false;">[+]</a> Single File / Title:',
                'attributes' => array(
                    'size' => 30,
                    'style' => 'display: block;'
                )
            ),
            'file_title_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 50,
                    'style' => 'display: block;'
                )
            ),
            'archive_file' => array(
                'type'       => 'file',
                'label'      => 'Archive File (Multiple Files):<br /><span style="display: block; margin: 1px 0 0 0; font-size: 0.9em;"><strong>Supported Archive Types</strong><br />' . implode(', ', array_keys(\Pop\Archive\Archive::formats())) . '</span>',
                'attributes' => array('size' => 30)
            ),
            'type_id' => array(
                'type'  => 'hidden',
                'value' => $tid
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Upload'
            )
        );

        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'batch-form');
    }

}

