<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Form\Element;
use Pop\I18n\I18n;
use Phire\Table;

class Config extends AbstractContentModel
{

    /**
     * Get configuration values
     *
     * @return void
     */
    public function getAll()
    {
        $cfg = Table\Config::getConfig();
        $config = array();
        $formattedConfig = array();

        foreach ($cfg->rows as $c) {
            if ($c->value == '0000-00-00 00:00:00') {
                $value = 'N/A';
            } else if (($c->setting == 'media_allowed_types') || ($c->setting == 'media_actions')) {
                $value = unserialize($c->value);
            } else {
                $value = htmlentities($c->value, ENT_QUOTES, 'UTF-8');
            }

            $config[$c->setting] = $value;
        }

        // Set server config settings
        $formattedConfig['server'] = array(
            'system_version'          => $config['system_version'],
            'system_document root'    => $config['system_document_root'],
            'server_operating_system' => $config['server_operating_system'],
            'server_software'         => $config['server_software'],
            'database_version'        => $config['database_version'],
            'php_version'             => $config['php_version'],
            'installed_on'            => $config['installed_on'],
            'updated_on'              => $config['updated_on']
        );

        // Set site title form element
        $siteTitle = new Element('text', 'site_title', html_entity_decode($config['site_title'], ENT_QUOTES, 'UTF-8'));
        $siteTitle->setAttributes('size', 40);

        // Set separator form element
        $separator = new Element('text', 'separator', html_entity_decode($config['separator'], ENT_QUOTES, 'UTF-8'));
        $separator->setAttributes('size', 3);

        // Set default language form element
        $langs = I18n::getLanguages();
        foreach ($langs as $key => $value) {
            $langs[$key] = substr($value, 0, strpos($value, ' ('));
        }

        $lang = new Element\Select('default_language', $langs, $config['default_language'], '                    ');

        // Set error message form element
        $error = new Element\Textarea('error_message', html_entity_decode($config['error_message'], ENT_QUOTES, 'UTF-8'));
        $error->setAttributes(array('rows' => 5, 'cols' => 100));

        // Set date and time format form element
        $datetime = $this->getDateTimeFormat($config['datetime_format']);

        // Set max media size form element
        $maxSize = new Element('text', 'media_max_filesize', $this->getMaxSize($config['media_max_filesize']));
        $maxSize->setAttributes('size', 3);

        // Set feed limit form element
        $feedLimit = new Element('text', 'feed_limit', $config['feed_limit']);
        $feedLimit->setAttributes('size', 3);

        // Set page limit form element
        $pageLimit = new Element('text', 'pagination_limit', $config['pagination_limit']);
        $pageLimit->setAttributes('size', 3);

        // Set page range form element
        $pageRange = new Element('text', 'pagination_range', $config['pagination_range']);
        $pageRange->setAttributes('size', 3);

        // Set media actions and media types form elements
        $mediaConfig = $this->getMediaConfig($config['media_actions']);
        $mediaTypes = $this->getMediaTypes($config['media_allowed_types']);

        $imageAdapters = array('Gd' => 'Gd');
        if (\Pop\Image\Imagick::isInstalled()) {
            $imageAdapters['Imagick'] = 'Imagick';
        }

        $formattedConfig['settings'] = array(
            'site_title'          => $siteTitle,
            'separator'           => $separator,
            'default_language'    => $lang,
            'error_message'       => '                    ' . $error,
            'datetime_format'     => $datetime,
            'media_allowed_types' => $mediaTypes,
            'media_max_filesize'  => '                    ' . $maxSize,
            'media_actions'       => $mediaConfig,
            'media_image_adapter' => new Element\Select('media_image_adapter', $imageAdapters, $config['media_image_adapter'], '                    '),
            'feed_type'           => new Element\Select('feed_type', array('10' => 'Atom','9' => 'RSS'), $config['feed_type'], '                    '),
            'feed_limit'          => '                    ' . $feedLimit,
            'pagination_limit'    => '                    ' . $pageLimit,
            'pagination_range'    => '                    ' . $pageRange,
            'category_totals'     => new Element\Radio('category_totals', array('1' => 'Yes', '0' => 'No'), $config['category_totals'], '                    '),
            'open_authoring'      => new Element\Radio('open_authoring', array('1' => 'Yes', '0' => 'No'), $config['open_authoring'], '                    '),
            'force_ssl'           => new Element\Radio('force_ssl', array('1' => 'Yes', '0' => 'No'), $config['force_ssl'], '                    '),
            'live'                => new Element\Radio('live', array('1' => 'Yes', '0' => 'No'), $config['live'], '                    ')
        );

        $this->data['config'] = new \ArrayObject($formattedConfig, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Update configuration values
     *
     * @param array $post
     * @return void
     */
    public function update($post)
    {
        unset($post['submit']);

        foreach ($post as $key => $value) {
            if ((strpos($key, 'media_') === false) && ($key != 'custom_datetime')) {
                $cfg = Table\Config::findById($key);

                if (($key == 'datetime_format') && ($value == 'custom')) {
                    $value = ($post['custom_datetime'] != '') ? $post['custom_datetime'] : 'F d, Y';
                }

                $cfg->value = (is_string($value)) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
                $cfg->update();
            }
        }

        $cfg = Table\Config::findById('media_allowed_types');
        $cfg->value = (isset($post['media_allowed_types'])) ? serialize($post['media_allowed_types']) : serialize(array());
        $cfg->update();

        $cfg = Table\Config::findById('media_max_filesize');
        if (stripos($post['media_max_filesize'], 'MB') !== false) {
            $value = trim(str_replace('MB', '', $post['media_max_filesize'])) . '000000';
        } else if (stripos($post['media_max_filesize'], 'KB') !== false) {
            $value = trim(str_replace('KB', '', $post['media_max_filesize'])) . '000';
        } else {
            $value = (int)trim($post['media_max_filesize']);
        }
        $cfg->value = $value;
        $cfg->update();

        $mediaActions = array();

        foreach ($post as $key => $value) {
            $size = '';
            $action = '0';
            $params = '';
            $quality = '';
            if ((strpos($key, 'media_size_') !== false) && (strpos($key, 'new_') === false)) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $size = $post['media_size_' . $id];
                $action = $post['media_action_' . $id];
                $params = $post['media_params_' . $id];
                $quality = $post['media_quality_' . $id];
            } else if ($key == 'media_size_new_1') {
                $size = $post['media_size_new_1'];
                $action = $post['media_action_new_1'];
                $params = $post['media_params_new_1'];
                $quality = $post['media_quality_new_1'];
            }
            if (($size != '') && ($action != '0') && ($params != '') && ($quality != '')) {
                $mediaActions[$size] = array(
                    'action'  => $action,
                    'params'  => $params,
                    'quality' => (int)$quality
                );
            }
        }

        if (isset($post['rm_media'])) {
            foreach ($post['rm_media'] as $rm) {
                if (isset($mediaActions[$rm])) {
                    unset($mediaActions[$rm]);
                }
            }
        }

        $cfg = Table\Config::findById('media_actions');
        $cfg->value = serialize($mediaActions);
        $cfg->update();

        $cfg = Table\Config::findById('media_image_adapter');
        $cfg->value = $post['media_image_adapter'];
        $cfg->update();
    }

    /**
     * Get date-time format radio element
     *
     * @param  string $datetime
     * @return string
     */
    protected function getDateTimeFormat($datetime)
    {
        $dateTimeOptions = array(
            'F d, Y'       => date('F d, Y'),
            'M j Y'        => date('M j Y'),
            'm/d/Y'        => date('m/d/Y'),
            'Y/m/d'        => date('Y/m/d'),
            'F d, Y g:i A' => date('F d, Y g:i A'),
            'M j Y g:i A'  => date('M j Y g:i A'),
            'm/d/Y g:i A'  => date('m/d/Y g:i A'),
            'Y/m/d g:i A'  => date('Y/m/d g:i A'),
        );

        if (array_key_exists($datetime, $dateTimeOptions)) {
            $dateTimeValue = $datetime;
            $dateTimeOptions['custom'] = '<input type="text" name="custom_datetime" onkeyup="customDatetime(this.value);" size="3" value="" /> <span id="custom-datetime"></span>';
        } else {
            $dateTimeValue = 'custom';
            $dateTimeOptions['custom'] = '<input type="text" name="custom_datetime" onkeyup="customDatetime(this.value);" size="3" value="' . $datetime . '" /> <span id="custom-datetime">(' . date($datetime) . ')</span>';
        }

        $datetime = new Element\Radio('datetime_format', $dateTimeOptions, $dateTimeValue, '                    ');
        return str_replace('class="radio-', 'class="radio-block-', (string)$datetime);
    }

    /**
     * Get formatted max file size
     *
     * @param  string $maxSize
     * @return string
     */
    protected function getMaxSize($maxSize)
    {
        if ($maxSize > 999999) {
            $size = round($maxSize / 1000000) . ' MB';
        } else if ($maxSize > 999) {
            $size = round($maxSize / 1000) . ' KB';
        } else {
            $size = $maxSize . ' B';
        }

        return $size;
    }

    /**
     * Get media config settings
     *
     * @param  array $actions
     * @return string
     */
    protected function getMediaConfig($actions)
    {
        $mediaSizes  = '                    <div id="media-sizes">' . PHP_EOL . '                        <strong>Size:</strong><br />' . PHP_EOL;
        $mediaActions = '                    <div id="media-actions">' . PHP_EOL . '                        <strong>Action:</strong><br />' . PHP_EOL;
        $mediaParams  = '                    <div id="media-params">' . PHP_EOL . '                        <strong>Parameters:</strong><br />' . PHP_EOL;
        $mediaQuality = '                    <div id="media-quality">' . PHP_EOL . '                        <strong>Quality:</strong><br />' . PHP_EOL;
        $mediaRemove = '                    <div id="media-remove">' . PHP_EOL . '                        <strong>Remove:</strong><br />' . PHP_EOL;

        $i = 1;
        $actionOptions = array_merge(array('0' => '----'), Content::getMediaActions());
        foreach ($actions as $size => $action) {
            $mediaSizes .= '                        <input type="text" name="media_size_' . $i . '" id="media_size_' . $i . '" value="' . $size . '" style="display: block;" size="3" />' . PHP_EOL;
            $actionSelect = new Element\Select('media_action_' . $i, $actionOptions, $action['action'], '                        ');
            $actionSelect->setAttributes('style', 'display: block;');
            $mediaActions .= (string)$actionSelect;
            $mediaParams .= '                        <input type="text" name="media_params_' . $i . '" id="media_params_' . $i . '" value="' . $action['params'] . '" style="display: block;" size="3" />' . PHP_EOL;
            $mediaQuality .= '                        <input type="text" name="media_quality_' . $i . '" id="media_quality_' . $i . '" value="' . $action['quality'] . '" style="display: block;" size="3" />' . PHP_EOL;
            $mediaRemove .= '                        <input type="checkbox" class="rm-media" name="rm_media[]" value="' . $size . '" style="display: block;" />' . PHP_EOL;
            $i++;
        }

        $mediaSizes .= '                        <input type="text" name="media_size_new_1" id="media_size_new_1" value="" style="display: block;" size="3" />' . PHP_EOL;
        $actionSelect = new Element\Select('media_action_new_1', $actionOptions, null, '                        ');
        $actionSelect->setAttributes('style', 'display: block;');
        $mediaActions .= (string)$actionSelect;
        $mediaParams .= '                        <input type="text" name="media_params_new_1" id="media_params_new_1" value="" style="display: block;" size="3" />' . PHP_EOL;
        $mediaQuality .= '                        <input type="text" name="media_quality_new_1" id="media_quality_new_1" value="" style="display: block;" size="3" />' . PHP_EOL;

        $mediaSizes  .= '                    </div>' . PHP_EOL;
        $mediaActions .= '                    </div>' . PHP_EOL;
        $mediaParams  .= '                    </div>' . PHP_EOL;
        $mediaQuality .= '                    </div>' . PHP_EOL;
        $mediaRemove .= '                    </div>' . PHP_EOL;

        return $mediaSizes . $mediaActions . $mediaParams . $mediaQuality . $mediaRemove;
    }

    /**
     * Get allowed media types
     *
     * @param  array $allowed
     * @return string
     */
    protected function getMediaTypes($allowed)
    {
        $mediaTypeValues = Content::getMediaTypes();
        $mediaTypes = '                    <div class="media-types-div">' . PHP_EOL;

        $i = 0;
        foreach ($mediaTypeValues as $key => $value) {
            if (($i > 0) && ($i % 6) == 0) {
                $mediaTypes .= '                    </div>' . PHP_EOL;
                $mediaTypes .= '                    <div class="media-types-div">' . PHP_EOL;
            }
            $mediaTypes .= '                        <input type="checkbox" class="check-box" name="media_allowed_types[]" value="' . $key . '"' . (in_array($key, $allowed) ? ' checked="checked"' : null) . ' /><span class="check-span">' . $key . '</span>' . PHP_EOL;
            $i++;
        }

        $mediaTypes .= '                    </div>' . PHP_EOL;
        $mediaTypes .= '                    <div style="clear: left;"><a href="#" onclick="$form(\'#config-form\').checkAll(\'media_allowed_types\'); return false;">Check All</a> | <a href="#" onclick="$form(\'#config-form\').uncheckAll(\'media_allowed_types\'); return false;">Uncheck All</a> | <a href="#" onclick="$form(\'#config-form\').checkInverse(\'media_allowed_types\'); return false;">Inverse</a> <em>(Uncheck all to allow any file type.)</em></div>' . PHP_EOL;

        return $mediaTypes;
    }

}
