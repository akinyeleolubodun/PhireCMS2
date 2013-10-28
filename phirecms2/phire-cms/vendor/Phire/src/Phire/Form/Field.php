<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;
use Phire\Model;
use Phire\Table;

class Field extends Form
{

    /**
     * @var array
     */
    protected $validators = array(
        '----'                 => '----',
        'AlphaNumeric'         => 'AlphaNumeric',
        'Alpha'                => 'Alpha',
        'BetweenInclude'       => 'BetweenInclude',
        'Between'              => 'Between',
        'CreditCard'           => 'CreditCard',
        'Email'                => 'Email',
        'Equal'                => 'Equal',
        'Excluded'             => 'Excluded',
        'GreaterThanEqual'     => 'GreaterThanEqual',
        'GreaterThan'          => 'GreaterThan',
        'Included'             => 'Included',
        'Ipv4'                 => 'Ipv4',
        'Ipv6'                 => 'Ipv6',
        'IsSubnetOf'           => 'IsSubnetOf',
        'LengthBetweenInclude' => 'LengthBetweenInclude',
        'LengthBetween'        => 'LengthBetween',
        'LengthGte'            => 'LengthGte',
        'LengthGt'             => 'LengthGt',
        'LengthLte'            => 'LengthLte',
        'LengthLt'             => 'LengthLt',
        'Length'               => 'Length',
        'LessThanEqual'        => 'LessThanEqual',
        'LessThan'             => 'LessThan',
        'NotEmpty'             => 'NotEmpty',
        'NotEqual'             => 'NotEqual',
        'Numeric'              => 'Numeric',
        'RegEx'                => 'RegEx',
        'Subnet'               => 'Subnet'
    );

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string      $action
     * @param  string      $method
     * @param  int         $id
     * @param  \Pop\Config $config
     * @return self
     */
    public function __construct($action = null, $method = 'post', $id = 0, $config = null)
    {
        $this->initFieldsValues = $this->getInitFields($id, $config);
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'field-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

        if ($_POST) {
            if ((strpos($this->type, 'history') !== false) && ($this->group_id != '0')) {
                $this->getElement('group_id')
                     ->addValidator(new Validator\NotEqual($this->group_id, 'A field with history tracking cannot be assigned to a dynamic field group.'));
            }
            if (($this->editor != 'source') && ($this->group_id != '0')) {
                $this->getElement('group_id')
                     ->addValidator(new Validator\NotEqual($this->group_id, 'An editor cannot be used on a field assigned to a dynamic field group.'));
            }
        }
    }

    /**
     * Get the init field values
     *
     * @param  int         $id
     * @param  \Pop\Config $config
     * @return array
     */
    protected function getInitFields($id = 0, $config = null)
    {
        // Get field groups
        $groups = array('0' => '----');

        $grps = Table\FieldGroups::findAll('id ASC');
        if (isset($grps->rows[0])) {
            foreach ($grps->rows as $grp) {
                $groups[$grp->id] = $grp->name;
            }
        }

        $editors = array('source' => 'Source');
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/js/ckeditor')) {
            $editors['ckeditor'] = 'CKEditor';
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/js/tinymce')) {
            $editors['tinymce'] = 'TinyMCE';
        }

        // Get any current validators
        $fields2 = array();
        $editorDisplay = 'none;';

        if ($id != 0) {
            $fld = Table\Fields::findById($id);
            if (isset($fld->id)) {
                if (strpos($fld->type, 'textarea') !== false) {
                    $editorDisplay = 'block;';
                }
                $validators = unserialize($fld->validators);
                if ($validators != '') {
                    $i = 1;
                    foreach ($validators as $key => $value) {
                        $fields2['validator_cur_' . $i] = array(
                            'type'       => 'select',
                            'label'      => '&nbsp;',
                            'value'      => $this->validators,
                            'marked'     => $key
                        );

                        $fields2['validator_value_cur_' . $i] = array(
                            'type'       => 'text',
                            'attributes' => array('size' => 10),
                            'value'      => $value['value']
                        );
                        $fields2['validator_message_cur_' . $i] = array(
                            'type'       => 'text',
                            'attributes' => array('size' => 30),
                            'value'      => $value['message']
                        );
                        $fields2['validator_remove_cur_' . $i] = array(
                            'type'       => 'checkbox',
                            'value'      => array('Yes' => 'Remove?')
                        );
                        $i++;
                    }
                }
            }
        }

        $browser = new \Pop\Web\Browser();

        $selectStyle = 'display: block; margin: 0 0 4px 0; padding-bottom: 5px;';
        if ($browser->isMsie()) {
            $selectStyle = 'display: block; margin: 0 0 6px 0; padding-top: 5px; padding-bottom: 5px;';
        } else if ($browser->isChrome()) {
            $selectStyle = 'display: block; margin: 0 0 5px 0; padding-top: 5px; padding-bottom: 5px;';
        }



        // Start creating initial fields
        $fields1 = array(
            'type' => array(
                'type'       => 'select',
                'label'      => 'Field Type:',
                'required'   => true,
                'value'      => array(
                    'text'             => 'text',
                    'text-history'     => 'text (history)',
                    'textarea'         => 'textarea',
                    'textarea-history' => 'textarea (history)',
                    'select'           => 'select',
                    'checkbox'         => 'checkbox',
                    'radio'            => 'radio',
                    'file'             => 'file',
                    'hidden'           => 'hidden'
                ),
                'attributes' => array(
                    'onchange' => 'toggleEditor(this);'
                )
            ),
            'editor' => array(
                'type'       => 'select',
                'value'      => $editors,
                'marked'     => 0,
                'attributes' => array(
                    'style' => 'display: ' . $editorDisplay
                )
            ),
            'name' => array(
                'type'       => 'text',
                'label'      => 'Field Name:',
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'label' => array(
                'type'       => 'text',
                'label'      => 'Field Label:',
                'attributes' => array('size' => 40)
            ),
            'values' => array(
                'type'       => 'text',
                'label'      => 'Field Values:<br /><span style="font-size: 0.9em;">(Pipe delimited)</span>',
                'attributes' => array('size' => 40)
            ),
            'default_values' => array(
                'type'       => 'text',
                'label'      => 'Default Field Values:<br /><span style="font-size: 0.9em;">(Pipe delimited)</span>',
                'attributes' => array('size' => 40)
            ),
            'attributes' => array(
                'type'       => 'text',
                'label'      => 'Field Attributes:',
                'attributes' => array('size' => 40)
            ),
            'validator_new_1' => array(
                'type'       => 'select',
                'label'      => '<a href="#" onclick="addValidator(); return false;">[+]</a> Field Validators:<br /><span style="font-size: 0.9em;">(Type / Value / Message)</span>',
                'value'      => $this->validators,
                'attributes' => array('style' => $selectStyle)
            ),
            'validator_value_new_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 10,
                    'style' => 'display: block; padding: 6px; margin: 0 0 4px 0;'
                )
            ),
            'validator_message_new_1' => array(
                'type'       => 'text',
                'attributes' => array(
                    'size' => 30,
                    'style' => 'display: block; padding: 6px; margin: 0 0 4px 0;'
                )
            )
        );

        // Create next set of fields
        $fields3 = array();

        $models = Model\Field::getModels($config);

        $f2mLabel = '<a href="#" onclick="addModel(); return false;">[+]</a> Model &amp; Type:';
        if ($id != 0) {
            $fieldToModels = Table\FieldsToModels::findBy(array('field_id' => $id));
            if (isset($fieldToModels->rows[0])) {
                $i = 1;
                foreach ($fieldToModels->rows as $f2m) {
                    $fields3['model_' . $i] = array(
                        'type'       => 'select',
                        'label'      => ($i == 1) ? $f2mLabel : '&nbsp;',
                        'value'      => $models,
                        'marked'     => $f2m->model,
                        'attributes' => array(
                            'style'    => 'display: block; margin: 0 0 4px 0;',
                            'onchange' => 'changeModelTypes(this);'
                        )
                    );
                    $fields3['type_id_' . $i] = array(
                        'type'       => 'select',
                        'value'      => \Phire\Project::getModelTypes(str_replace('\\', '_', $f2m->model)),
                        'marked'     => $f2m->type_id,
                        'attributes' => array(
                            'style'  => 'display: block; min-width: 200px; margin: 0 0 4px 0;'
                        )
                    );
                    if ($i > 1) {
                        $fields3['rm_model_' . $i] = array(
                            'type'       => 'checkbox',
                            'value'      => array(
                                $f2m->field_id . '_' . $f2m->model . '_' . $f2m->type_id => 'Remove?'
                            ),
                        );
                    }
                    $i++;
                }
            }
        } else {
            $fields3['model_1'] = array(
                'type'       => 'select',
                'label'      => $f2mLabel,
                'value'      => $models,
                'attributes' => array(
                    'style'    => 'display: block; margin: 0 0 4px 0;',
                    'onchange' => 'changeModelTypes(this);'
                )
            );
            $fields3['type_id_1'] = array(
                'type'       => 'select',
                'value'      => \Phire\Project::getModelTypes($models),
                'attributes' => array(
                    'style' => 'display: block; min-width: 200px; margin: 0 0 4px 0;'
                )
            );
        }
        $fields4 = array();

        $fields4['submit'] = array(
            'type'  => 'submit',
            'label' => '&nbsp;',
            'value' => 'SAVE',
            'attributes' => array(
                'class' => 'save-btn'
            )
        );
        $fields4['update'] = array(
            'type'       => 'button',
            'value'      => 'UPDATE',
            'attributes' => array(
                'onclick' => "return updateForm('#field-form', true);",
                'class' => 'update-btn'
            )
        );

        $fields4['required'] = array(
            'type'   => 'radio',
            'label'  => 'Required:',
            'value'  => array(
                '0' => 'No',
                '1' => 'Yes'
            ),
            'marked' => 0
        );
        $fields4['group_id'] = array(
            'type'   => 'select',
            'label'  => 'Field Group:',
            'value'  => $groups,
            'attributes' => array(
                'style' => 'display: block; min-width: 150px;'
            )
        );
        $fields4['encryption'] = array(
             'type'       => 'select',
             'label'  => 'Encryption:',
             'value' => array(
                 '0' => 'None',
                 '1' => 'MD5',
                 '2' => 'SHA1',
                 '3' => 'Crypt',
                 '4' => 'Bcrypt',
                 '5' => 'Mcrypt (2-Way)',
                 '6' => 'Crypt_MD5',
                 '7' => 'Crypt_SHA256',
                 '8' => 'Crypt_SHA512',
             ),
             'marked'     => 0,
             'attributes' => array(
                 'style' => 'display: block; min-width: 150px;'
             )
         );
         $fields4['order'] = array(
             'type'       => 'text',
             'label'      => 'Order:',
             'value'      => 0,
             'attributes' => array('size' => 3)
         );

        $fields4['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields4['update_value'] = array(
            'type'  => 'hidden',
            'value' => 0
        );

        return array($fields4, $fields1, $fields2, $fields3);
    }

}
