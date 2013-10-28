<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\Dir;
use Phire\Table;

class Field extends \Phire\Model\AbstractModel
{

    /**
     * Static method to get field definitions by model and
     * return them for consumption by a Pop\Form\Form object
     *
     * @param string $model
     * @param int    $tid
     * @param int    $mid
     * @return array
     */
    public static function getByModel($model, $tid = 0, $mid = 0)
    {
        $fieldsAry = array();
        $curFields = array();
        $groups = array();
        $dynamic = false;
        $hasFile = false;

        // Get fields
        $sql = Table\FieldsToModels::getSql();
        $sql->select(array(
            DB_PREFIX . 'fields_to_models.field_id',
            DB_PREFIX . 'fields_to_models.model',
            DB_PREFIX . 'fields_to_models.type_id',
            'field_order' => DB_PREFIX . 'fields.order',
            DB_PREFIX . 'fields_to_groups.group_id',
            'group_order' => DB_PREFIX . 'field_groups.order',
        ))->join(DB_PREFIX . 'fields', array('field_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'fields_to_groups', array('field_id', 'field_id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'field_groups', array(DB_PREFIX . 'fields_to_groups.group_id', 'id'), 'LEFT JOIN');

        $sql->select()->where()->equalTo('model', ':model');
        $sql->select()->orderBy('group_order', 'ASC')->orderBy('field_order', 'ASC');;

        $fieldsToModel = Table\FieldsToModels::execute($sql->render(true), array('model' => $model));
        $fields = array();
        foreach ($fieldsToModel->rows as $f2m) {
            if (($f2m->type_id == 0) || ($tid == $f2m->type_id)) {
                $field = Table\Fields::findById($f2m->field_id);
                if (isset($field->id)) {
                    $fields[] = $field;
                }
            }
        }

        // If fields exist
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                // Get field group, if applicable
                $groupAryResults = Table\FieldsToGroups::getFieldGroup($field->id);
                $groupAry = $groupAryResults['fields'];
                $isDynamic = $groupAryResults['dynamic'];
                if ($isDynamic) {
                    $dynamic = true;
                }
                if ((count($groupAry) > 0) && (!in_array($groupAry, $groups))) {
                    $groups[$groupAryResults['group_id']] = $groupAry;
                }
                $rmFile = null;
                $fld = array(
                    'type' => ((strpos($field->type, '-') !== false) ?
                        substr($field->type, 0, strpos($field->type, '-')) : $field->type)
                );

                // Get field label
                if ($field->label != '') {
                    if (isset($groupAry[0]) && ($groupAry[0] == $field->id) && ($isDynamic)) {
                        $fld['label'] = '<a href="#" onclick="addFields([' . implode(', ', $groupAry) . ']); return false;">[+]</a> ' . $field->label;
                    } else {
                        $fld['label'] = $field->label;
                    }
                }

                $fld['required'] = (bool)$field->required;

                // Get field values and default values
                if (($field->type == 'select') || ($field->type == 'checkbox') || ($field->type == 'radio')) {
                    if ($field->values != '') {
                        // Get fields values of a multiple value field
                        if (strpos($field->values, '|') !== false) {
                            $vals = explode('|', $field->values);
                            $valAry = array();
                            foreach ($vals as $v) {
                                // If the values are a name/value pair
                                if (strpos($v, ',') !== false) {
                                    $vAry = explode(',', $v);
                                    if (count($vAry) >= 2) {
                                        // If the values are to be pulled from a database table
                                        if (strpos($vAry[0], 'Table') !== false) {
                                            $class = $vAry[0];
                                            $order = $vAry[1] . (isset($vAry[2]) ? ', ' . $vAry[2] : null);
                                            $order .= ' ' . ((isset($vAry[3])) ? $vAry[3] : 'ASC');
                                            $id = $vAry[1];
                                            $name = (isset($vAry[2]) ? $vAry[2] : $vAry[1]);
                                            $valRows = $class::findAll($order);
                                            $valAry['----'] = '----';
                                            if (isset($valRows->rows[0])) {
                                                foreach ($valRows->rows as $vRow) {
                                                    $valAry[$vRow->{$id}] = $vRow->{$name};
                                                }
                                            }
                                        // Else, if the value is a simple name/value pair
                                        } else {
                                            $valAry[$vAry[0]] = $vAry[1];
                                        }
                                    }
                                } else {
                                    $valAry[$v] = $v;
                                }
                            }
                            $fld['value'] = $valAry;
                        // If the values are to be pulled from a database table
                        } else if (strpos($field->values, 'Table') !== false) {
                            $valAry = array('----' => '----');
                            if (strpos($field->values, ',') !== false) {
                                $vAry = explode(',', $field->values);
                                $class = $vAry[0];
                                $order = $vAry[1] . (isset($vAry[2]) ? ', ' . $vAry[2] : null);
                                $order .= ' ' . ((isset($vAry[3])) ? $vAry[3] : 'ASC');
                                $id = $vAry[1];
                                $name = (isset($vAry[2]) ? $vAry[2] : $vAry[1]);
                            } else {
                                $class = $field->values;
                                $order = null;
                                $id = 'id';
                                $name = 'id';
                            }
                            $valRows = $class::findAll($order);
                            if (isset($valRows->rows[0])) {
                                foreach ($valRows->rows as $vRow) {
                                    $valAry[$vRow->{$id}] = $vRow->{$name};
                                }
                            }
                            $fld['value'] = $valAry;
                        // Else, if the value is Select constant
                        } else if (strpos($field->values, 'Select::') !== false) {
                            $fld['value'] = str_replace('Select::', '', $field->values);
                        // Else, the value is a simple value
                        } else {
                            $aryValues = array();
                            if (strpos($field->values, ',') !== false) {
                                $vls = explode(',', $field->values);
                                $aryValues[$vls[0]] = $vls[1];
                            } else {
                                $aryValues[$field->values] = $field->values;
                            }
                            $fld['value'] = $aryValues;
                        }
                    }
                    // Set default values
                    if ($field->default_values != '') {
                        $fld['marked'] = (strpos($field->default_values, '|') !== false) ? explode('|', $field->default_values) : $field->default_values;
                    }
                // If field is a file field
                } else if (($field->type == 'file') && (count($groupAry) == 0)) {
                    $dynamic = true;
                    $hasFile = true;
                    if ($mid != 0) {
                        $fileValue = Table\FieldValues::findById(array($field->id, $mid));
                        if (isset($fileValue->field_id)) {
                            $fileName = unserialize($fileValue->value);
                            $fileInfo = \Phire\Model\Content::getFileIcon($fileName);
                            $fld['label'] .= ' <em>(Replace?)</em><br /><a href="' .
                                BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank"><img style="padding-top: 3px;" src="' .
                                BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank">' .
                                $fileName . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span>';

                            $fld['required'] = false;

                            $rmFile = array(
                                'rm_file_' . $field->id => array(
                                    'type'=> 'checkbox',
                                    'value' => array($fileName => 'Remove?')
                                )
                            );
                        }
                    }
                // Else, if the field is a normal field
                } else {
                    if ($field->default_values != '') {
                        $fld['value'] = $field->default_values;
                    }
                }

                // Get field attributes
                if ($field->attributes != '') {
                    $attAry = array();
                    $attributes = explode('" ', $field->attributes);
                    foreach ($attributes as $attrib) {
                        $att = explode('=', $attrib);
                        $attAry[$att[0]] = str_replace('"', '', $att[1]);
                    }
                    $fld['attributes'] = $attAry;
                }

                // Get field validators
                if ($field->validators != '') {
                    $valAry = array();
                    $validators = unserialize($field->validators);
                    foreach ($validators as $key => $value) {
                        $valClass = '\Pop\Validator\\' . $key;
                        if ($value['value'] != '') {
                            $v = new $valClass($value['value']);
                        } else {
                            $v = new $valClass();
                        }
                        if ($value['message'] != '') {
                            $v->setMessage($value['message']);
                        }
                        $valAry[] = $v;
                    }
                    $fld['validators'] = $valAry;
                }

                // Detect any dynamic field group values
                $values = Table\FieldValues::findAll(null, array('field_id' => $field->id));
                if (isset($values->rows[0])) {
                    foreach ($values->rows as $value) {
                        $val = unserialize($value->value);
                        if ((count($groupAry) > 0) && ($value->model_id == $mid)) {
                            if (is_array($val)) {
                                foreach ($val as $k => $v) {
                                    $curFld = $fld;
                                    if (($field->type == 'select') || ($field->type == 'checkbox') || ($field->type == 'radio')) {
                                        $curFld['marked'] = $v;
                                    } else {
                                        $curFld['value'] = $v;
                                    }
                                    if (isset($curFld['label'])) {
                                        $curFld['label'] = '&nbsp;';
                                    }
                                    if (!isset($curFields[$field->id])) {
                                        $curFields[$field->id] = array('field_' . $field->id . '_cur_' . ($k + 1) => $curFld);
                                    } else {
                                        $curFields[$field->id]['field_' . $field->id . '_cur_' . ($k + 1)] = $curFld;
                                    }
                                }
                            } else {
                                $curFld = $fld;
                                if (($field->type == 'select') || ($field->type == 'checkbox') || ($field->type == 'radio')) {
                                    $curFld['marked'] = $val;
                                } else {
                                    $curFld['value'] = $val;
                                }
                                if (isset($curFld['label'])) {
                                    $curFld['label'] = '&nbsp;';
                                }
                                if (!isset($curFields[$field->id])) {
                                    $curFields[$field->id] = array('field_' . $field->id => $curFld);
                                } else {
                                    $curFields[$field->id]['field_' . $field->id] = $curFld;
                                }
                            }
                        }
                    }
                }

                // If field is assigned to a dynamic field group, set field name accordingly
                if ((count($groupAry) > 0) && ($isDynamic)) {
                    $fieldName = 'field_' . $field->id . '_new_1';
                } else {
                    $fieldName = 'field_' . $field->id;
                }

                // Add field to the field array

                $fieldsAry[$fieldName] = $fld;

                // If in the system back end, and the field is a textarea, add history select field
                if (($mid != 0) &&
                    (strpos($field->type, '-history') !== false) &&
                    (count($groupAry) == 0) && (strpos($_SERVER['REQUEST_URI'], APP_URI) !== false)) {
                    $fv = Table\FieldValues::findById(array($field->id, $mid));
                    if (isset($fv->field_id) && (null !== $fv->history)) {
                        $history = array(0 => '(Current)');
                        $historyAry = unserialize($fv->history);
                        krsort($historyAry);
                        foreach ($historyAry as $time => $fieldValue) {
                            $history[$time] = date('M j, Y H:i:s', $time);
                        }
                        $fieldsAry['history_' . $mid . '_' . $field->id] = array(
                            'type'       => 'select',
                            'label'      => 'Select Revision:',
                            'value'      => $history,
                            'marked'     => 0,
                            'attributes' => array(
                                'onchange' => "changeHistory(this, '" . BASE_PATH . APP_URI . "');"
                            )
                        );
                    }
                }

                if (strpos($field->type, 'textarea') !== false) {
                    if ((null !== $field->editor) && ($field->editor != 'source')) {
                        $editors = array('source' => 'Source');
                        if (($field->editor == 'ckeditor') &&
                            file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/js/ckeditor')) {
                            $editors['ckeditor'] = 'CKEditor';
                        } else if (($field->editor == 'tinymce') &&
                            file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/js/tinymce')) {
                            $editors['tinymce'] = 'TinyMCE';
                        }
                        $fieldsAry['editor_' . $field->id] = array(
                            'type'       => 'select',
                            'label'      => 'Change Editor:',
                            'value'      => $editors,
                            'marked'     => $field->editor,
                            'attributes' => array(
                                'onchange' => "changeEditor(this);"
                            )
                        );
                    }
                }

                // Add a remove field
                if (null !== $rmFile) {
                    foreach ($rmFile as $rmKey => $rmValue) {
                        $fieldsAry[$rmKey] = $rmValue;
                    }
                }

                if (isset($group) && (count($group) > 0)) {
                    if (isset($group[count($group) - 1]) && ($field->id == $group[count($group) - 1])) {
                        $fieldsAry[implode('_', $group)] = null;
                        $group = array();
                    }
                }
            }
        }

        // Add fields from dynamic field group in the correct order
        $realCurFields = array();
        $groupRmAry = array();
        if (count($curFields) > 0) {
            $fieldCount = count($curFields);
            $keys = array_keys($curFields);
            $valueCounts = array();
            foreach ($groups as $key => $value) {
                foreach ($curFields as $k => $v) {
                    if (in_array($k, $value)) {
                        $valueCounts[$key] = count($v);
                    }
                }
            }

            foreach ($valueCounts as $gKey => $valueCount) {
                for ($i = 0; $i < $valueCount; $i++) {
                    $fileName = null;
                    $gDynamic = false;
                    for ($j = 0; $j < $fieldCount; $j++) {
                        if (in_array($keys[$j], $groups[$gKey])) {
                            if (isset($curFields[$keys[$j]]['field_' . $keys[$j] . '_cur_' . ($i + 1)])) {
                                $gDynamic = true;
                                $f = $curFields[$keys[$j]]['field_' . $keys[$j] . '_cur_' . ($i + 1)];
                                if ($f['type'] == 'file') {
                                    $hasFile = true;
                                    $dynamic = true;
                                    $fileName = $f['value'];
                                    // Calculate file icon, set label
                                    if (!empty($fileName)) {
                                        $fileInfo = \Phire\Model\Content::getFileIcon($fileName);
                                        $f['label'] = '<em>Replace?</em><br /><a href="' .
                                        BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank"><img style="padding-top: 3px;" src="' .
                                        BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank">' .
                                        $fileName . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span>';
                                    } else {
                                        $f['label'] = 'Replace?';
                                    }
                                    $fld['required'] = false;
                                }
                                $realCurFields['field_' . $keys[$j] . '_cur_' . ($i + 1)] = $f;
                            } else if (isset($curFields[$keys[$j]]['field_' . $keys[$j]])) {
                                $gDynamic = false;
                                $f = $curFields[$keys[$j]]['field_' . $keys[$j]];
                                if ($f['type'] == 'file') {
                                    $hasFile = true;
                                    $dynamic = true;
                                    $fileName = $f['value'];
                                    // Calculate file icon, set label
                                    if (!empty($fileName)) {
                                        $fileInfo = \Phire\Model\Content::getFileIcon($fileName);
                                        $f['label'] = '<em >Replace?</em><br /><a href="' .
                                            BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank"><img style="padding-top: 3px;" src="' .
                                            BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $fileName . '" target="_blank">' .
                                            $fileName . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span>';
                                    } else {
                                        $f['label'] = 'Replace?';
                                    }
                                    $fld['required'] = false;
                                }
                                $fieldsAry['field_' . $keys[$j]] = $f;
                            }
                        }
                    }

                    // Add a remove field
                    if ($gDynamic) {
                        $fieldId = implode('_', $groups[$gKey]) . '_' . ($i + 1);
                        $realCurFields['rm_fields_' . $fieldId] = array(
                            'type'  => 'checkbox',
                            'value' => array($fieldId => 'Remove?')
                        );
                    } else {
                        $fieldId = implode('_', $groups[$gKey]);
                        $groupRmAry[$key] = array(
                            'type'  => 'checkbox',
                            'value' => array($fieldId => 'Remove?')
                        );
                    }
                }
            }
        }

        // Merge new fields and current fields together in the right order.
        $realFieldsAry = array(
            'dynamic' => $dynamic,
            'hasFile' => $hasFile,
            '0'       => array()
        );

        if (count($groups) > 0) {
            foreach ($groups as $id => $fields) {
                $realFieldsAry[$id] = array();
            }
        }

        $cnt = 0;
        foreach ($fieldsAry as $key => $value) {
            $id = substr($key, (strpos($key, '_') + 1));
            if (strpos($id, '_') !== false) {
                $id = substr($id, 0, strpos($id, '_'));
            }
            $curGroupId = 0;
            foreach ($groups as $gId => $gFields) {
                if (in_array($id, $gFields)) {
                    $curGroupId = $gId;
                }
            }

            if (strpos($key, 'new_') !== false) {
                $cnt = 0;
                $curGroup = null;
                foreach ($groups as $group) {
                    if (in_array($id, $group)) {
                        $curGroup = $group;
                    }
                }

                $realFieldsAry[$curGroupId][$key] = $value;

                if ((null !== $curGroup) && ($id == $curGroup[count($curGroup) - 1])) {
                    foreach ($realCurFields as $k => $v) {
                        if (strpos($k, 'rm_field') === false) {
                            $i = substr($k, (strpos($k, '_') + 1));
                            $i = substr($i, 0, strpos($i, '_'));
                            if (in_array($i, $curGroup)) {
                                $realFieldsAry[$curGroupId][$k] = $v;
                            }
                        } else {
                            $i = substr($k, (strpos($k, 'rm_fields_') + 10));
                            $i = substr($i, 0, strrpos($i, '_'));
                            $grp = explode('_', $i);
                            if ($grp == $curGroup) {
                                $realFieldsAry[$curGroupId][$k] = $v;
                            }
                        }
                    }
                }
            } else {
                $cnt++;
                $realFieldsAry[$curGroupId][$key] = $value;
                if (isset($groupRmAry[$curGroupId]) && ($cnt == count($groups[$curGroupId]))) {
                    $realFieldsAry[$curGroupId]['rm_fields_' . implode('_', $groups[$curGroupId])] = $groupRmAry[$curGroupId];
                }
            }
        }

        return $realFieldsAry;
    }

    /**
     * Get available model objects
     *
     * @param  \Pop\Config $config
     * @return array
     */
    public static function getModels($config = null)
    {
        $models = array();
        $exclude = array();
        $override = null;

        // Get any exclude or override config values
        if (null !== $config) {
            $configAry = $config->asArray();
            if (isset($configAry['exclude'])) {
                $exclude = $configAry['exclude'];
            }
            if (isset($configAry['override'])) {
                $override = $configAry['override'];
            }
        }

        // If override, set overridden models
        if (null !== $override) {
            foreach ($override as $model) {
                $models[$model] = $model;
            }
            // Else, get all modules from the system and module directories
        } else {
            $systemDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor'), true);
            $sysModuleDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/module'), true);
            $moduleDirectory = new Dir(realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules'), true);
            $dirs = array_merge($systemDirectory->getFiles(), $sysModuleDirectory->getFiles(), $moduleDirectory->getFiles());
            sort($dirs);

            // Dir clean up
            foreach ($dirs as $key => $dir) {
                unset($dirs[$key]);
                if (!((strpos($dir, 'PopPHPFramework') !== false) || (strpos($dir, 'config') !== false) || (strpos($dir, 'index.html') !== false))) {
                    $k = $dir;
                    if (substr($dir, -1) == DIRECTORY_SEPARATOR) {
                        $k = substr($k, 0, -1);
                    }
                    $k = substr($k, (strrpos($k, DIRECTORY_SEPARATOR) + 1));
                    $dirs[$k] = $dir;
                }
            }

            // Loop through each directory, looking for model class files
            foreach ($dirs as $mod => $dir) {
                if (file_exists($dir . 'src/' . $mod . '/Model')) {
                    $d = new Dir($dir . 'src/' . $mod . '/Model');
                    $dFiles = $d->getFiles();
                    sort($dFiles);
                    foreach ($dFiles as $m) {
                        if (substr($m, 0, 8) !== 'Abstract') {
                            $model = str_replace('.php', '', $mod . '\Model\\' . $m);
                            if (!in_array($model, $exclude) && (strpos($model, 'index.html') === false)) {
                                $models[$model] = ((strpos($model, '\\') !== false) ?
                                    substr($model, (strrpos($model, '\\') + 1)) : $model);
                            }
                        }
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Get all fields method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $fields = Table\Fields::findAll($order['field'] . ' ' . $order['order']);

        if ($this->data['acl']->isAuth('Phire\Controller\Structure\FieldsController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_fields[]" id="remove_fields[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_fields" />';
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

        if ($this->data['acl']->isAuth('Phire\Controller\Structure\FieldsController', 'edit')) {
            $name = '<a href="' . BASE_PATH . APP_URI . '/structure/fields/edit/[{id}]">[{name}]</a>';
        } else {
            $name = '[{name}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'field-remove-form',
                'action'  => BASE_PATH . APP_URI . '/structure/fields/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'      => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=id">#</a>',
                    'type'    => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=type">Type</a>',
                    'name'    => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=name">Name</a>',
                    'order'    => '<a href="' . BASE_PATH . APP_URI . '/structure/fields?sort=order">Order</a>',
                    'process' => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array(
                'group_id', 'values', 'default_values', 'attributes', 'validators', 'encryption', 'editor'
            ),
            'name'   => $name,
            'indent' => '        '
        );

        $fieldsAry = array();
        foreach ($fields->rows as $field) {
            $field->required = ($field->required) ? 'Yes' : 'No';
            $fieldsAry[] = $field;
        }

        if (isset($fieldsAry[0])) {
            $this->data['table'] = Html::encode($fieldsAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get field by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $field = Table\Fields::findById($id);
        if (isset($field->id)) {
            $fieldValues = $field->getValues();
            $f2g = Table\FieldsToGroups::findBy(array('field_id' => $field->id));
            if (isset($f2g->field_id)) {
                $fieldValues['group_id'] = $f2g->group_id;
            }
            $this->data = array_merge($this->data, $fieldValues);
        }
    }

    /**
     * Get images for WYSIWYG editor
     *
     * @return void
     */
    public function getImages()
    {
        $sizes = array_keys($this->config->media_actions);

        // Get images
        $sql = \Phire\Table\Content::getSql();
        $sql->select(array(
            'content_id'      => DB_PREFIX . 'content.id',
            'content_type_id' => DB_PREFIX . 'content.type_id',
            'content_uri'     => DB_PREFIX . 'content.uri',
            'content_title'   => DB_PREFIX . 'content.title',
            'type_id'         => DB_PREFIX . 'content_types.id',
            'type_uri'        => DB_PREFIX . 'content_types.uri',
        ))->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN')
            ->where()
            ->equalTo(DB_PREFIX . 'content_types.uri', 0)
            ->like(DB_PREFIX . 'content.uri', '%.jpg', 'AND')
            ->like(DB_PREFIX . 'content.uri', '%.jpe', 'OR')
            ->like(DB_PREFIX . 'content.uri', '%.jpeg', 'OR')
            ->like(DB_PREFIX . 'content.uri', '%.png', 'OR')
            ->like(DB_PREFIX . 'content.uri', '%.gif', 'OR');

        $content = \Phire\Table\Content::execute($sql->render(true));
        $contentRows = $content->rows;

        // Set the onlick action based on the editor
        if ($_GET['editor'] == 'ckeditor') {
            $onclick = "window.opener.CKEDITOR.tools.callFunction(funcNum, this.href.replace(/^.*\\/\\/[^\\/]+/, '')); window.close(); return false;";
        } else if ($_GET['editor'] == 'tinymce') {
            $onclick = "top.tinymce.activeEditor.windowManager.getParams().oninsert(this.href.replace(/^.*\\/\\/[^\\/]+/, '')); top.tinymce.activeEditor.windowManager.close(); return false;";
        } else {
            $onclick = 'return false;';
        }

        // Format the select column
        foreach ($contentRows as $key => $value) {
            $fileInfo = \Phire\Model\Content::getFileIcon($value->content_uri);
            $value->file_icon = $fileInfo['fileIcon'];
            $value->file_size = $fileInfo['fileSize'];

            $select = '[ <a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $value->content_uri . '" onclick="' . $onclick . '">Original</a>';
            foreach ($sizes as $size) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media/' . $size . '/' . $value->content_uri)){
                    $select .= ' | <a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $size . '/' . $value->content_uri . '" onclick="' . $onclick . '">' . ucfirst($size) . '</a>';
                }
            }
            $select .= ' ]';
            $value->select = $select;

            $contentRows[$key] = $value;
        }

        $this->data['sizes'] = $sizes;
        $this->data['files'] = $contentRows;
    }

    /**
     * Get files and URIs for WYSIWYG editor
     *
     * @return void
     */
    public function getFiles()
    {
        $sizes = array_keys($this->config->media_actions);

        // Get files
        $sql = \Phire\Table\Content::getSql();
        $sql->select(array(
            'content_id'      => DB_PREFIX . 'content.id',
            'content_type_id' => DB_PREFIX . 'content.type_id',
            'content_uri'     => DB_PREFIX . 'content.uri',
            'content_title'   => DB_PREFIX . 'content.title',
            'type_id'         => DB_PREFIX . 'content_types.id',
            'type_uri'        => DB_PREFIX . 'content_types.uri',
        ))->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN')
            ->where()
            ->equalTo(DB_PREFIX . 'content_types.uri', 0);

        $content = \Phire\Table\Content::execute($sql->render(true));
        $contentRows = $content->rows;

        // Set the onlick action based on the editor
        if ($_GET['editor'] == 'ckeditor') {
            $onclick = "window.opener.CKEDITOR.tools.callFunction(funcNum, this.href.replace(/^.*\\/\\/[^\\/]+/, '')); window.close(); return false;";
        } else if ($_GET['editor'] == 'tinymce') {
            $onclick = "top.tinymce.activeEditor.windowManager.getParams().oninsert(this.href.replace(/^.*\\/\\/[^\\/]+/, '')); top.tinymce.activeEditor.windowManager.close(); return false;";
        } else {
            $onclick = 'return false;';
        }

        // Format the select column
        foreach ($contentRows as $key => $value) {
            $fileInfo = \Phire\Model\Content::getFileIcon($value->content_uri);
            $value->file_icon = $fileInfo['fileIcon'];
            $value->file_size = $fileInfo['fileSize'];

            $select = '[ <a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $value->content_uri . '" onclick="' . $onclick . '">Original</a>';
            foreach ($sizes as $size) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media/' . $size . '/' . $value->content_uri)){
                    $select .= ' | <a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $size . '/' . $value->content_uri . '" onclick="' . $onclick . '">' . ucfirst($size) . '</a>';
                }
            }
            $select .= ' ]';
            $value->select = $select;

            $contentRows[$key] = $value;
        }

        // Get URIs
        $sql = \Phire\Table\Content::getSql();
        $sql->select(array(
            'content_id'      => DB_PREFIX . 'content.id',
            'content_type_id' => DB_PREFIX . 'content.type_id',
            'content_uri'     => DB_PREFIX . 'content.uri',
            'content_title'   => DB_PREFIX . 'content.title',
            'type_id'         => DB_PREFIX . 'content_types.id',
            'type_uri'        => DB_PREFIX . 'content_types.uri',
        ))->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN')
            ->where()
            ->equalTo(DB_PREFIX . 'content_types.uri', 1);

        $content = \Phire\Table\Content::execute($sql->render(true));
        $uriRows = $content->rows;

        // Format the select column
        foreach ($uriRows as $key => $value) {
            $value->select = '[ <a href="' . BASE_PATH . $value->content_uri . '" onclick="' . $onclick . '">URI</a> ]';
            $uriRows[$key] = $value;
        }

        $this->data['sizes'] = $sizes;
        $this->data['files'] = $contentRows;
        $this->data['uris']  = $uriRows;
    }

    /**
     * Upload file
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function upload(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        // If content is a file
        if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['tmp_name'] != '')) {
            $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
            $fileName = \Pop\File\File::checkDupe($_FILES['uri']['name'], $dir);

            $upload = \Pop\File\File::upload(
                $_FILES['uri']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                $this->config->media_max_filesize, $this->config->media_allowed_types
            );
            chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
            if (preg_match(\Phire\Model\Content::getImageRegex(), $fileName)) {
                \Phire\Model\Content::processMedia($fileName, $this->config);
            }

            $title = ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($fileName, 0, strrpos($fileName, '.'))));
            $uri = $fileName;
            $slug = $fileName;

            $content = new \Phire\Table\Content(array(
                'type_id'    => $fields['type_id'],
                'title'      => $title,
                'uri'        => $uri,
                'slug'       => $slug,
                'feed'       => 1,
                'force_ssl'  => 0,
                'created'    => date('Y-m-d H:i:s'),
                'published'  => date('Y-m-d H:i:s'),
                'created_by' => ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null),
                'updated_by' => null
            ));

            $content->save();
            $this->data['id'] = $content->id;
        }
    }

    /**
     * Save field
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $validators = array();
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'validator_new_') !== false) && ($value != '') && ($value != '----')) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $validators[$value] = array(
                    'value'   => html_entity_decode(strip_tags($_POST['validator_value_new_' . $id]), ENT_QUOTES, 'UTF-8'),
                    'message' => html_entity_decode(strip_tags($_POST['validator_message_new_' . $id]), ENT_QUOTES, 'UTF-8')
                );
            }
        }

        $field = new Table\Fields(array(
            'type'           => $fields['type'],
            'name'           => $fields['name'],
            'label'          => $fields['label'],
            'values'         => $fields['values'],
            'default_values' => $fields['default_values'],
            'attributes'     => $fields['attributes'],
            'validators'     => ((count($validators) > 0) ? serialize($validators) : null),
            'encryption'     => (int)$fields['encryption'],
            'order'          => (int)$fields['order'],
            'required'       => (int)$fields['required'],
            'editor'         => (($fields['editor'] != '0') ? $fields['editor'] : null)
        ));

        $field->save();
        $this->data['id'] = $field->id;

        // Save field group
        if ((int)$_POST['group_id'] != 0) {
            $f2g = new Table\FieldsToGroups(array(
                'field_id' => $field->id,
                'group_id' => (int)$_POST['group_id']
            ));
            $f2g->save();
        }

        // Save field to model relationships
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'model_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $fieldToModel = new Table\FieldsToModels(array(
                    'field_id' => $field->id,
                    'model'    => $value,
                    'type_id'  => (int)$_POST['type_id_' . $id]
                ));
                $fieldToModel->save();
            }
        }
    }

    /**
     * Update field
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $curValidators = array();
        $newValidators = array();
        foreach ($_POST as $key => $value) {
            if ((strpos($key, 'validator_new_') !== false) && ($value != '') && ($value != '----')) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $newValidators[$value] = array(
                    'value'   => html_entity_decode(strip_tags($_POST['validator_value_new_' . $id]), ENT_QUOTES, 'UTF-8'),
                    'message' => html_entity_decode(strip_tags($_POST['validator_message_new_' . $id]), ENT_QUOTES, 'UTF-8')
                );
            } else if (strpos($key, 'validator_cur_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                if (!isset($_POST['validator_remove_cur_' . $id])) {
                    if (($value != '') && ($value != '----')) {
                        $curValidators[$value] = array(
                            'value'   => html_entity_decode(strip_tags($_POST['validator_value_cur_' . $id]), ENT_QUOTES, 'UTF-8'),
                            'message' => html_entity_decode(strip_tags($_POST['validator_message_cur_' . $id]), ENT_QUOTES, 'UTF-8')
                        );
                    }
                }
            }
        }

        $validators = array_merge($curValidators, $newValidators);

        $field = Table\Fields::findById($fields['id']);
        $field->type           = $fields['type'];
        $field->name           = $fields['name'];
        $field->label          = $fields['label'];
        $field->values         = $fields['values'];
        $field->default_values = $fields['default_values'];
        $field->attributes     = $fields['attributes'];
        $field->validators     = ((count($validators) > 0) ? serialize($validators) : null);
        $field->encryption     = (int)$fields['encryption'];
        $field->order          = (int)$fields['order'];
        $field->required       = (int)$fields['required'];
        $field->editor         = (($fields['editor'] != '0') ? $fields['editor'] : null);
        $field->update();
        $this->data['id'] = $field->id;

        $f2m = new Table\FieldsToModels();
        $f2m->delete(array('field_id' => $field->id));

        $removed = array();

        // Remove field to model relationships
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'rm_model_') !== false) {
                $values = explode('_', $value[0]);
                $removed[] = array(
                    'field_id' => $values[0],
                    'model'    => $values[1],
                    'type_id'  => $values[2]
                );
            }
        }

        // Save field to model relationships
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 6) == 'model_') {
                $id = substr($key, (strrpos($key, '_') + 1));
                $values = array(
                    'field_id' => $field->id,
                    'model'    => $value,
                    'type_id'  => (int)$_POST['type_id_' . $id]
                );

                if (!in_array($values, $removed)) {
                    $fieldToModel = new Table\FieldsToModels($values);
                    $fieldToModel->save();
                }
            }
        }

        // Update the field group
        if (isset($_POST['group_id'])) {
            $f2g = Table\FieldsToGroups::findBy(array('field_id' => $field->id));
            if (isset($f2g->field_id)){
                $f2g->delete();
            }
            if ((int)$_POST['group_id'] != 0) {
                $f2g = new Table\FieldsToGroups(array(
                    'field_id' => $field->id,
                    'group_id' => (int)$_POST['group_id']
                ));
                $f2g->save();
            }
        }
    }

    /**
     * Remove fields
     *
     * @param array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_fields'])) {
            foreach ($post['remove_fields'] as $id) {
                $field = Table\Fields::findById($id);
                if (isset($field->id)) {
                    $field->delete();
                }
            }
        }
    }

}

