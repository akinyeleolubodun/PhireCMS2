<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Auth\Auth;
use Pop\Crypt;
use Pop\File\File;
use Phire\Table;

class FieldValue extends \Phire\Model\AbstractModel
{

    /**
     * Static method to get field values
     *
     * @param  int     $modelId
     * @param  boolean $byName
     * @return array
     */
    public static function getAll($modelId, $byName = false)
    {
        $encOptions = array();

        // Check for an overriding config settings
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php')) {
            $fldCfg = include $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php';
            $encOptions = $fldCfg['Phire']->encryptionOptions->asArray();
        } else {
            $fldCfg = include __DIR__ . '/../../../config/module.php';
            $encOptions = $fldCfg['Phire']->encryptionOptions->asArray();
        }

        $values = array();

        // Create SQL object
        $sql = Table\FieldValues::getSql();
        $sql->select(array(
            DB_PREFIX . 'field_values.field_id',
            DB_PREFIX . 'field_values.model_id',
            DB_PREFIX . 'field_values.value',
            DB_PREFIX . 'field_values.history',
            DB_PREFIX . 'fields.name',
            DB_PREFIX . 'fields.encryption'
        ))->join(DB_PREFIX . 'fields', array('field_id', 'id'), 'LEFT JOIN')
          ->where()->equalTo('model_id', ':model_id');

        // Execute SQL statement to get any field values for the model
        $fields = Table\FieldValues::execute($sql->render(true), array('model_id' => $modelId));

        // If field values are found, loop through, format and return
        if (isset($fields->rows[0])) {
            foreach ($fields->rows as $field) {
                $f = Table\Fields::findById($field->field_id);
                $value = unserialize($field->value);
                $groupAry = Table\FieldsToGroups::getFieldGroup($field->field_id);
                if (count($groupAry) > 0) {
                    if ($byName) {
                        if (is_array($value)) {
                            $values[$field->name] = array();
                            foreach ($value as $k => $v) {
                                $values[$field->name][] = self::decrypt($v, $f->encryption, $encOptions);
                            }
                        } else {
                            $values[$field->name] = self::decrypt($value, $f->encryption, $encOptions);
                        }
                    } else {
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                $values['field_' . $field->field_id . '_cur_' . ($k + 1)] = self::decrypt($v, $f->encryption, $encOptions);
                            }
                        } else {
                            $values['field_' . $field->field_id] = self::decrypt($value, $f->encryption, $encOptions);
                        }
                    }
                } else {
                    $key = ($byName) ? $field->name : 'field_' . $field->field_id;
                    $values[$key] = self::decrypt($value, $f->encryption, $encOptions);
                }
            }
        }

        return $values;
    }

    /**
     * Static method to get field types
     *
     * @param  int     $modelId
     * @param  boolean $byName
     * @return array
     */
    public static function getAllTypes($modelId, $byName = false)
    {
        $types = array();

        // Create SQL object
        $sql = Table\FieldValues::getSql();
        $sql->select(array(
            DB_PREFIX . 'field_values.field_id',
            DB_PREFIX . 'field_values.model_id',
            DB_PREFIX . 'field_values.value',
            DB_PREFIX . 'fields.name',
            DB_PREFIX . 'fields.type'
        ))->join(DB_PREFIX . 'fields', array('field_id', 'id'), 'LEFT JOIN')
          ->where()->equalTo('model_id', ':model_id');

        // Execute SQL statement to get any field values for the model
        $fields = Table\FieldValues::execute($sql->render(true), array('model_id' => $modelId));

        // If field values are found, loop through, format and return
        if (isset($fields->rows[0])) {
            foreach ($fields->rows as $field) {
                $key = ($byName) ? $field->name : 'field_' . $field->field_id;
                $types[$key] = $field->type;
            }
        }

        return $types;
    }

    /**
     * Static method to save field values
     *
     * @param array   $fields
     * @param int     $modelId
     * @return void
     */
    public static function save(array $fields, $modelId)
    {
        $encOptions = array();

        // Check for an overriding config settings
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php')) {
            $fldCfg = include $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php';
            $encOptions = $fldCfg['Phire']->encryptionOptions->asArray();
        } else {
            $fldCfg = include __DIR__ . '/../../../config/module.php';
            $encOptions = $fldCfg['Phire']->encryptionOptions->asArray();
        }

        $config = static::factory()->config();
        $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';

        $valueAry = array();
        $postKeys = ($_POST) ? self::getPostKeys() : array();
        $fileKeys = ($_FILES) ? self::getFileKeys() : array();
        $keys = array_merge($postKeys, $fileKeys);
        sort($keys);
        $groups = Table\FieldValues::getGroups($keys);

        // Save new dynamic fields (not files)
        foreach($fields as $key => $value) {
            if ((strpos($key, 'field_') !== false) && (!isset($_FILES[$key]))) {
                $id = self::getFieldId($key);
                if ($_POST) {
                    // If it's a dynamic field value, store in array
                    if (strpos($key, 'new_') !== false) {
                        foreach ($_POST as $k => $v) {
                            if (strpos($k, 'field_' . $id . '_new_') !== false) {
                                if (!isset($valueAry[$id])) {
                                    $valueAry[$id] = array($v);
                                } else {
                                    $valueAry[$id][] = $v;
                                }
                            }
                        }
                    // Else, save the non-dynamic field value
                    } else {
                        $f = Table\Fields::findById($id);
                        $value = self::encrypt($value, $f->encryption, $encOptions);
                        $f = new Table\FieldValues(array(
                            'field_id'  => $id,
                            'model_id'  => $modelId,
                            'value'     => serialize($value),
                            'timestamp' => time()
                        ));
                        $f->save();
                    }
                }
            }
        }

        // Check for files
        if ($_FILES) {
            foreach ($_FILES as $key => $value) {
                if (strpos($key, 'field_') !== false) {
                    $id = self::getFieldId($key);
                    $fileName = '';

                    if (($value['tmp_name'] != '')) {
                        $fileName = File::checkDupe($value['name'], $dir);
                        $upload = File::upload(
                            $value['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                            $config->media_max_filesize, $config->media_allowed_types
                        );
                        chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
                        if (($_FILES) && (preg_match(\Phire\Model\Content::getImageRegex(), $fileName))) {
                            \Phire\Model\Content::processMedia($fileName, $config);
                        }
                    }

                    // If it's a dynamic field value, store in array
                    if (strpos($key, 'new_') !== false) {
                        if (!isset($valueAry[$id])) {
                            $valueAry[$id] = array($fileName);
                        } else {
                            $valueAry[$id][] = $fileName;
                        }
                    // Else, save the non-dynamic field value
                    } else {
                        if ($fileName != '') {
                            $f = Table\Fields::findById($id);
                            $fileName = self::encrypt($fileName, $f->encryption, $encOptions);
                            $f = new Table\FieldValues(array(
                                'field_id'  => $id,
                                'model_id'  => $modelId,
                                'value'     => serialize($fileName),
                                'timestamp' => time()
                            ));
                            $f->save();
                        }
                    }
                }
            }
        }

        // Save the dynamic field values
        if (count($valueAry) > 0) {
            // Clean up, check for empties
            foreach ($groups as $group) {
                $keys = array_keys($valueAry[$group[0]]);
                foreach ($keys as $key) {
                    $i = 0;
                    foreach ($group as $id) {
                        if (($valueAry[$id][$key] == '----') || (empty($valueAry[$id][$key])) || (is_array($valueAry[$id][$key]) && (count($valueAry[$id][$key]) == 0))) {
                            $i++;
                        }
                    }
                    if ($i == count($group)) {
                        foreach ($valueAry as $k => $v) {
                            if (in_array($k, $group)) {
                                unset($valueAry[$k][$key]);
                            }
                        }
                    }
                }
            }

            foreach ($valueAry as $key => $value) {
                $valueAry[$key] = array_values($value);
            }

            foreach ($valueAry as $id => $value) {
                $f = Table\Fields::findById($id);
                $value = self::encrypt($value, $f->encryption, $encOptions);
                // If not empty, then save
                $f = new Table\FieldValues(array(
                    'field_id'  => $id,
                    'model_id'  => $modelId,
                    'value'     => serialize($value),
                    'timestamp' => time()
                ));
                $f->save();
            }
        }
    }

    /**
     * Static method to update field values
     *
     * @param array   $fields
     * @param int     $modelId
     * @return void
     */
    public static function update(array $fields, $modelId)
    {
        $config = static::factory()->config();
        $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';

        $valueAry = array();
        $postKeys = ($_POST) ? self::getPostKeys() : array();
        $fileKeys = ($_FILES) ? self::getFileKeys() : array();
        $keys = array_merge($postKeys, $fileKeys);
        sort($keys);
        $groups = Table\FieldValues::getGroups($keys);

        // Get salt and history count, if applicable
        $encOptions = array();
        $historyCount = null;

        // Check for an overriding config setting
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php')) {
            $fldCfg = include $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/config/phire.php';
            $encOptions = $fldCfg['Phire']->encryptionOptions->asArray();
            $historyCount = (isset($fldCfg['Phire']->history)) ? (int)$fldCfg['Phire']->history : null;
        } else {
            $fldCfg = include __DIR__ . '/../../../config/module.php';
            $encOptions = $fldCfg['Phire']->encryptionOptions->asArray();
            $historyCount = (isset($fldCfg['Phire']->history)) ? (int)$fldCfg['Phire']->history : null;
        }

        // Save new dynamic fields (not files)
        foreach($fields as $key => $value) {
            if ((strpos($key, 'field_') !== false) && (!isset($_FILES[$key]))) {
                $id = self::getFieldId($key);
                if ($_POST) {
                    // If it's a dynamic field value, store in array
                    if (strpos($key, 'field_' . $id . '_cur_') !== false) {
                        if (!isset($valueAry[$id])) {
                            $valueAry[$id] = array($value);
                        } else {
                            $valueAry[$id][] = $value;
                        }
                    // Else, save the non-dynamic field value
                    } else if (strpos($key, 'new_') === false) {
                        $f = Table\Fields::findById($id);
                        $field = Table\FieldValues::findById(array($id, $modelId));
                        $realValue = (isset($_POST['field_' . $id])) ? $value : null;
                        // If existing field, update
                        if (isset($field->field_id)) {
                            if (!empty($realValue) && ($realValue != '[Encrypted]')) {
                                $realValue = self::encrypt($realValue, $f->encryption, $encOptions);
                            }

                            // If history tracking is available for this field, update history
                            if ((null !== $historyCount) && ($historyCount > 0)) {
                                $f = Table\Fields::findById($field->field_id);
                                if (isset($f->id) && (strpos($f->type, '-history') !== false)) {
                                    $oldValue = unserialize($field->value);
                                    // If value is different that the last value
                                    if ($realValue != $oldValue) {
                                        if (null !== $field->history) {
                                            $history = unserialize($field->history);
                                            $history[$field->timestamp] = $oldValue;
                                            if (count($history) > $historyCount) {
                                                $history = array_slice($history, 1, $historyCount, true);
                                            }
                                            $field->history = serialize($history);
                                        } else {
                                            $field->history = serialize(array($field->timestamp => $oldValue));
                                        }
                                    }
                                }
                            }
                            $field->value = serialize($realValue);
                            $field->timestamp = time();
                            $field->update();
                        // Else, save new field
                        } else {
                            $realValue = self::encrypt($realValue, $f->encryption, $encOptions);
                            $f = new Table\FieldValues(array(
                                'field_id'  => $id,
                                'model_id'  => $modelId,
                                'value'     => serialize($realValue),
                                'timestamp' => time()
                            ));
                            $f->save();
                        }
                    }
                }
            }
        }

        // Check for current files
        if ($_FILES) {
            foreach ($_FILES as $key => $value) {
                if (strpos($key, 'field_') !== false) {
                    $id = self::getFieldId($key);

                    // If it's a dynamic field value, store in array
                    if (strpos($key, 'cur_') !== false) {
                        $fileName = '';
                        if (($value['tmp_name'] != '')) {
                            $num = substr($key, (strrpos($key, '_') + 1)) - 1;
                            $fv = Table\FieldValues::findById(array($id, $modelId));

                            // If file exists and is being replaced, remove it
                            if (isset($fv->field_id)) {
                                $fValue = unserialize($fv->value);
                                if (isset($fValue[$num])) {
                                    if ($_FILES) {
                                        \Phire\Model\Content::removeMedia($fValue[$num]);
                                    }
                                }
                            }

                            $fileName = File::checkDupe($value['name'], $dir);
                            $upload = File::upload(
                                $value['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                                $config->media_max_filesize, $config->media_allowed_types
                            );
                            chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
                            if (($_FILES) && (preg_match(\Phire\Model\Content::getImageRegex(), $fileName))) {
                                \Phire\Model\Content::processMedia($fileName, $config);
                            }
                        } else {
                            $num = substr($key, (strrpos($key, '_') + 1)) - 1;
                            $fv = Table\FieldValues::findById(array($id, $modelId));
                            if (isset($fv->field_id)) {
                                $fValue = unserialize($fv->value);
                                if (isset($fValue[$num])) {
                                    $fileName = $fValue[$num];
                                }
                            }
                        }

                        if (!isset($valueAry[$id])) {
                            $valueAry[$id] = array($fileName);
                        } else {
                            $valueAry[$id][] = $fileName;
                        }
                    // Else, save the non-dynamic field value
                    } else if (strpos($key, 'new_') === false) {
                        $fileName = '';
                        if (($value['tmp_name'] != '')) {
                            $fileName = File::checkDupe($value['name'], $dir);
                            $upload = File::upload(
                                $value['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                                $config->media_max_filesize, $config->media_allowed_types
                            );
                            chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
                            if (($_FILES) && (preg_match(\Phire\Model\Content::getImageRegex(), $fileName))) {
                                \Phire\Model\Content::processMedia($fileName, $config);
                            }
                        }

                        if ($fileName != '') {
                            $f = Table\Fields::findById($id);
                            $fileName = self::encrypt($fileName, $f->encryption, $encOptions);
                            $field = Table\FieldValues::findById(array($id, $modelId));
                            // If file field value exists, update
                            if (isset($field->field_id)) {
                                if ($_FILES) {
                                    \Phire\Model\Content::removeMedia(unserialize($field->value));
                                }

                                $field->value = serialize($fileName);
                                $field->timestamp = time();
                                $field->update();
                            // Else, save new
                            } else {
                                $f = new Table\FieldValues(array(
                                    'field_id'  => $id,
                                    'model_id'  => $modelId,
                                    'value'     => serialize($fileName),
                                    'timestamp' => time()
                                ));
                                $f->save();
                            }
                        }
                    }
                }
            }
        }

        // Remove any fields that have been marked for deletion
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'rm_fields_') !== false) {
                $ids = substr($key, 10);
                $ids = explode('_', $ids);
                $num = null;
                if (isset($ids[2])) {
                    $num = $ids[2] - 1;
                    unset($ids[2]);
                }
                foreach ($ids as $id) {
                    $f = Table\Fields::findById($id);
                    // If it's a file, remove the file too
                    if (isset($f->id) && ($f->type == 'file')) {
                        $fv = Table\FieldValues::findById(array($id, $modelId));
                        if (isset($fv->field_id)) {
                            $fValue = unserialize($fv->value);
                            if (is_array($fValue) && isset($fValue[$num])) {
                                if ($_FILES) {
                                    \Phire\Model\Content::removeMedia($fValue[$num]);
                                }
                            } else {
                                if ($_FILES) {
                                    \Phire\Model\Content::removeMedia($fValue);
                                }
                            }
                        }
                    }

                    if ((null !== $num) && isset($valueAry[$id]) && isset($valueAry[$id][$num])) {
                        unset($valueAry[$id][$num]);
                    } else {
                        $fv = Table\FieldValues::findById(array($id, $modelId));
                        if (isset($fv->field_id)) {
                            $fv->delete();
                        }
                    }
                }
            }
            // Remove any non-dynamic single files
            if (strpos($key, 'rm_file_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                if (isset($value[0])) {
                    if ($_FILES) {
                        \Phire\Model\Content::removeMedia($value[0]);
                    }
                }
                $fv = Table\FieldValues::findById(array($id, $modelId));
                if (isset($fv->field_id)) {
                    $fv->delete();
                }
            }
        }

        // Removal clean-up
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'rm_fields_') !== false) {
                $ids = substr($key, 10);
                $ids = substr($ids, 0, strrpos($ids, '_'));
                $ids = explode('_', $ids);
                foreach ($ids as $id) {
                    if (isset($valueAry[$id])) {
                        $valueAry[$id] = array_values($valueAry[$id]);
                    }
                }
            }
        }


        // Check for new dynamic field values (not files)
        if ($_POST) {
            foreach ($_POST as $k => $v) {
                if ((strpos($k, 'field_') !== false) && (!isset($_FILES[$k]))) {
                    $id = self::getFieldId($k);
                    // If it's a dynamic field value, store in array
                    if (strpos($k, 'field_' . $id . '_new_') !== false) {
                        if (!isset($valueAry[$id])) {
                            $valueAry[$id] = array($v);
                        } else {
                            $valueAry[$id][] = $v;
                        }
                    }
                }
            }
        }

        // Get new files
        if ($_FILES) {
            foreach ($_FILES as $key => $value) {
                if (strpos($key, 'field_') !== false) {
                    $id = self::getFieldId($key);

                    // If it's a dynamic field value, store in array
                    if (strpos($key, 'new_') !== false) {
                        $fileName = '';
                        if (($value['tmp_name'] != '')) {
                            $fileName = File::checkDupe($value['name'], $dir);
                            $upload = File::upload(
                                $value['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                                $config->media_max_filesize, $config->media_allowed_types
                            );
                            chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
                            if (($_FILES) && (preg_match(\Phire\Model\Content::getImageRegex(), $fileName))) {
                                \Phire\Model\Content::processMedia($fileName, $config);
                            }
                        }
                        if (!isset($valueAry[$id])) {
                            $valueAry[$id] = array($fileName);
                        } else {
                            $valueAry[$id][] = $fileName;
                        }
                    }
                }
            }
        }

        // Save the new dynamic field values
        if (count($valueAry) > 0) {
            // Clean up, check for empties
            foreach ($groups as $group) {
                if (isset($valueAry[$group['fields'][0]])) {
                    $keys = array_keys($valueAry[$group['fields'][0]]);
                    foreach ($keys as $key) {
                        $i = 0;
                        $removal = array();
                        foreach ($group['fields'] as $id) {
                            if (isset($valueAry[$id][$key])) {
                                if (($valueAry[$id][$key] == '----') || empty($valueAry[$id][$key]) ||
                                    (is_array($valueAry[$id][$key]) && (count($valueAry[$id][$key]) == 1) && empty($valueAry[$id][$key][0]))) {
                                    $f = Table\Fields::findById($id);
                                    if (isset($f->id) && ($f->type == 'file')) {
                                        $fv = Table\FieldValues::findById(array($id, $modelId));
                                        if (isset($fv->field_id)) {
                                            $fValue = unserialize($fv->value);
                                            if (isset($fValue[$key])) {
                                                $valueAry[$id][$key] = $fValue[$key];
                                            }
                                        }
                                    }
                                    $i++;
                                }
                            } else if (isset($valueAry[$id])) {
                                if (is_array($valueAry[$id]) && (count($valueAry[$id]) == 0)) {
                                    $i++;
                                } else if (is_array($valueAry[$id]) && (count($valueAry[$id]) == 1) && (empty($valueAry[$id][0]) || ($valueAry[$id][0] == '----'))) {
                                    $i++;
                                }
                            }
                        }
                        if ($i == count($group['fields'])) {
                            foreach ($valueAry as $k => $v) {
                                if (in_array($k, $group['fields'])) {
                                    unset($valueAry[$k][$key]);
                                    if (isset($valueAry[$k]) && (count($valueAry[$k]) == 0)) {
                                        $removal[] = $k;
                                        unset($valueAry[$k]);
                                    }
                                }
                            }
                        }

                        sort($removal);

                        // Final clean up of empties
                        if (count($removal) > 0) {
                            foreach ($groups as $grp) {
                                if (in_array($removal, $grp)) {
                                    foreach ($removal as $id) {
                                        $fv = Table\FieldValues::findById(array($id, $modelId));
                                        if (isset($fv->field_id)) {
                                            $fv->delete();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($valueAry as $key => $value) {
                $valueAry[$key] = array_values($value);
            }

            // Either update existing field values or save new ones
            foreach ($valueAry as $id => $value) {
                $f = Table\Fields::findById($id);
                $field = Table\FieldValues::findById(array($id, $modelId));
                if (isset($field->field_id)) {
                    if (!empty($value) && ($value != '[Encrypted]')) {
                        $value = self::encrypt($value, $f->encryption, $encOptions);
                    } else {
                        $value = unserialize($field->value);
                    }
                    $field->value = serialize($value);
                    $field->timestamp = time();
                    $field->update();
                } else {
                    $value = self::encrypt($value, $f->encryption, $encOptions);
                    $f = new Table\FieldValues(array(
                        'field_id'  => $id,
                        'model_id'  => $modelId,
                        'value'     => serialize($value),
                        'timestamp' => time()
                    ));
                    $f->save();
                }
            }
        }
    }

    /**
     * Static method to remove field values
     *
     * @param int     $modelId
     * @return void
     */
    public static function remove($modelId)
    {
        $fields = \Phire\Table\FieldValues::findAll(null, array('model_id' => $modelId));
        if (isset($fields->rows[0])) {
            foreach ($fields->rows as $field) {
                // Get the field values with the field type to check for any files to delete
                if (isset($field->field_id)) {
                    $sql = \Phire\Table\FieldValues::getSql();
                    $sql->select(array(
                        DB_PREFIX . 'field_values.field_id',
                        DB_PREFIX . 'field_values.model_id',
                        DB_PREFIX . 'field_values.value',
                        DB_PREFIX . 'fields.type'
                    ))->join(DB_PREFIX . 'fields', array('field_id', 'id'), 'LEFT JOIN')
                        ->where()
                        ->equalTo('field_id', ':field_id')
                        ->equalTo('model_id', ':model_id');

                    $fld = \Phire\Table\FieldValues::execute(
                        $sql->render(true),
                        array('field_id' => $field->field_id, 'model_id' => $modelId)
                    );

                    if (isset($fld->field_id)) {
                        // If field type is file, delete file(s)
                        if ($fld->type == 'file') {
                            $file = unserialize($fld->value);
                            if (is_array($file)) {
                                foreach ($file as $f) {
                                    \Phire\Model\Content::removeMedia($f);
                                }
                            } else {
                                \Phire\Model\Content::removeMedia($file);
                            }
                        }
                        $fld->delete();
                    }
                }
            }
        }
    }

    /**
     * Static method to get post keys
     *
     * @return array
     */
    public static function getPostKeys()
    {
        $postKeys = array();
        $keys = array_keys($_POST);

        foreach ($keys as $key) {
            if (strpos($key, 'field_') !== false) {
                $postKeys[] = $key;
            }
        }

        return $postKeys;
    }

    /**
     * Static method to get file keys
     *
     * @return array
     */
    public static function getFileKeys()
    {
        $fileKeys = array();
        $keys = array_keys($_FILES);

        foreach ($keys as $key) {
            if (strpos($key, 'field_') !== false) {
                $fileKeys[] = $key;
            }
        }

        return $fileKeys;
    }

    /**
     * Static method to get field id from key
     *
     * @param  string $key
     * @return string
     */
    public static function getFieldId($key)
    {
        $id = substr($key, 6);
        if (strpos($id, '_') !== false) {
            $id = substr($id, 0, strpos($id, '_'));
        }
        return $id;
    }

    /**
     * Static method encrypt a field value
     *
     * @param  string $value
     * @param  int    $encryption
     * @param  array  $options
     * @return string
     */
    public static function encrypt($value, $encryption, $options = array())
    {
        $encValue = $value;
        $salt = (!empty($options['salt'])) ? $options['salt'] : null;

        // Encrypt the value
        switch ($encryption) {
            case Auth::ENCRYPT_CRYPT_SHA_512:
                $crypt = new Crypt\Sha(512);
                $crypt->setSalt($salt);

                // Set rounds, if applicable
                if (!empty($options['rounds'])) {
                    $crypt->setRounds($options['rounds']);
                }

                $encValue = $crypt->create($value);
                break;

            case Auth::ENCRYPT_CRYPT_SHA_256:
                $crypt = new Crypt\Sha(256);
                $crypt->setSalt($salt);

                // Set rounds, if applicable
                if (!empty($options['rounds'])) {
                    $crypt->setRounds($options['rounds']);
                }

                $encValue = $crypt->create($value);
                break;

            case Auth::ENCRYPT_CRYPT_MD5:
                $crypt = new Crypt\Md5();
                $crypt->setSalt($salt);
                $encValue = $crypt->create($value);
                break;

            case Auth::ENCRYPT_MCRYPT:
                $crypt = new Crypt\Mcrypt();
                $crypt->setSalt($salt);

                // Set cipher, mode and source, if applicable
                if (!empty($options['cipher'])) {
                    $crypt->setCipher($options['cipher']);
                }
                if (!empty($options['mode'])) {
                    $crypt->setMode($options['mode']);
                }
                if (!empty($options['source'])) {
                    $crypt->setSource($options['source']);
                }

                $encValue = $crypt->create($value);
                break;

            case Auth::ENCRYPT_BCRYPT:
                $crypt = new Crypt\Bcrypt();
                $crypt->setSalt($salt);

                // Set cost and prefix, if applicable
                if (!empty($options['cost'])) {
                    $crypt->setCost($options['cost']);
                }
                if (!empty($options['prefix'])) {
                    $crypt->setPrefix($options['prefix']);
                }
                $encValue = $crypt->create($value);
                break;

            case Auth::ENCRYPT_CRYPT:
                $crypt = new Crypt\Crypt();
                $crypt->setSalt($salt);
                $encValue = $crypt->create($value);
                break;

            case Auth::ENCRYPT_SHA1:
                $encValue = sha1($value);
                break;

            case Auth::ENCRYPT_MD5:
                $encValue = md5($value);
                break;

            case Auth::ENCRYPT_NONE:
                $encValue = $value;
                break;
        }

        return $encValue;
    }

    /**
     * Static method decrypt a field value
     *
     * @param  string $value
     * @param  int    $encryption
     * @param  array  $options
     * @return string
     */
    public static function decrypt($value, $encryption, $options = array())
    {
        $decValue = $value;
        $salt = (!empty($options['salt'])) ? $options['salt'] : null;

        // Decrypt the value
        switch ($encryption) {
            case Auth::ENCRYPT_NONE:
                $decValue = $value;
                break;

            case Auth::ENCRYPT_MCRYPT:
                $crypt = new Crypt\Mcrypt();
                $crypt->setSalt($salt);

                // Set cipher, mode and source, if applicable
                if (!empty($options['cipher'])) {
                    $crypt->setCipher($options['cipher']);
                }
                if (!empty($options['mode'])) {
                    $crypt->setMode($options['mode']);
                }
                if (!empty($options['source'])) {
                    $crypt->setSource($options['source']);
                }

                $decValue = $crypt->decrypt($value);
                break;

            default:
                $decValue = '[Encrypted]';
        }

        return $decValue;
    }

}

