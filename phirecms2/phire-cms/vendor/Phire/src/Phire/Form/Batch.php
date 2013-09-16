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
                    'size' => 30,
                    'style' => 'display: block; margin: 0 0 5px 0; padding: 0 0 5px 0;'
                )
            ),
            'file_title_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 50,
                    'style' => 'display: block; margin: 0 0 5px 0; padding: 0 0 5px 0;'
                )
            )
        );

        $fields2 = array();
        $formats = \Pop\Archive\Archive::formats();

        if (count($formats) > 0) {
            $fields2 = array(
                'archive_file' => array(
                    'type'       => 'file',
                    'label'      => 'Archive of Multiple Files:<br /><span style="display: block; margin: 5px 0 0 0; font-size: 0.9em;"><strong>Supported Archive Types</strong><br />' . implode(', ', array_keys($formats)) . '</span>',
                    'attributes' => array('size' => 30)
                )
            );
        }

        $fields3 = array(
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

        $this->initFieldsValues = array($fields1, $fields2, $fields3);
        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'batch-form');
    }

}

