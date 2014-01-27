<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;
use Phire\Table;

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
        $sess = \Pop\Web\Session::getInstance();
        $siteIds = array(0 => $_SERVER['HTTP_HOST']);

        $sites = Table\Sites::findAll();
        foreach ($sites->rows as $site) {
            if (in_array($site->id, $sess->user->site_ids)) {
                $siteIds[$site->id] = $site->domain;
            }
        }

        $fields1 = array(
            'file_name_1' => array(
                'type'       => 'file',
                'label'      => '<a href="#" onclick="phire.addBatchFields(); return false;">[+]</a> File / Title:',
                'attributes' => array(
                    'size' => 40,
                    'style' => 'display: block; margin: 0 0 10px 0; padding: 1px 4px 1px 1px; margin: 0px 0px 10px 0; height: 26px;'
                )
            ),
            'file_title_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 60,
                    'style' => 'display: block; margin: 0 0 10px 0; padding: 5px 4px 6px 4px; margin: 0px 0px 10px 0; height: 17px;'
                )
            )
        );

        $formats = \Pop\Archive\Archive::formats();

        if (count($formats) > 0) {
            $fields2 = array(
                'archive_file' => array(
                    'type'       => 'file',
                    'label'      => 'Archive of Multiple Files:<br /><span style="display: block; margin: 5px 0 0 0; font-size: 0.9em;"><strong>Supported Types:</strong> ' . implode(', ', array_keys($formats)) . '</span>',
                    'attributes' => array(
                        'size' => 40,
                        'style' => 'display: block; margin: 0 0 10px 0; padding: 1px 4px 1px 1px; margin: 0px 0px 10px 0; height: 26px;'
                    )
                )
            );
            $fields3 = array(
                'type_id' => array(
                    'type'  => 'hidden',
                    'value' => $tid
                ),
                'site_id' => array(
                    'type'       => 'select',
                    'label'      => 'Site:',
                    'value'      => $siteIds,
                    'marked'     => 0,
                    'attributes' => array('style' => 'min-width: 200px;')
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'label' => '&nbsp;',
                    'value' => 'UPLOAD',
                    'attributes' => array(
                        'class' => 'save-btn',
                    'style' => 'width: 200px;'
                    )
                )
            );
            $this->initFieldsValues = array($fields1, $fields2, $fields3);
        } else {
            $fields1['type_id'] = array(
                'type'  => 'hidden',
                'value' => $tid
            );
            $fields1['site_id'] = array(
                'type'       => 'select',
                'label'      => 'Site:',
                'value'      => $siteIds,
                'marked'     => 0,
                'attributes' => array('style' => 'min-width: 200px;')
            );
            $fields1['submit'] = array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'UPLOAD',
                'attributes' => array(
                    'class' => 'save-btn',
                    'style' => 'width: 200px;'
                )
            );
            $this->initFieldsValues = $fields1;
        }

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'batch-form')
             ->setAttributes('onsubmit', 'phire.showLoading();');
    }

}

