<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Table;

class Template extends Form
{

    /**
     * Content types
     *
     * @var array
     */
    protected static $contentTypes = array(
        'text/html'           => 'text/html',
        'text/plain'          => 'text/plain',
        'text/css'            => 'text/css',
        'text/javascript'     => 'text/javascript',
        'text/xml'            => 'text/xml',
        'application/xml'     => 'application/xml',
        'application/rss+xml' => 'application/rss+xml',
        'application/json'    => 'application/json'
    );

    /**
     * Mobile templates
     *
     * @var array
     */
    protected static $mobileTemplates = array(
        'desktop'        => 'Desktop',
        'mobile'         => 'Any Mobile Device',
        'phone'          => 'Any Mobile Phone',
        'tablet'         => 'Any Mobile Tablet',
        'iphone'         => 'iPhone',
        'ipad'           => 'iPad',
        'android-phone'  => 'Android Phone',
        'android-tablet' => 'Android Tablet',
        'windows-phone'  => 'Windows Phone',
        'windows-tablet' => 'Windows Tablet',
        'blackberry'     => 'Blackberry',
        'palm'           => 'Palm'
    );

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
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'template-form');
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

        // Add validators for checking dupe names and devices
        if (($_POST) && isset($_POST['id'])) {
            $tmpl = Table\Templates::findBy(array('name' => $this->name));
            if (isset($tmpl->id) && ($this->id != $tmpl->id)) {
                $this->getElement('name')
                     ->addValidator(new Validator\NotEqual($this->name, 'That template name already exists. The name must be unique.'));
            }

            if ($this->parent_id != '0') {
                $tmpl = Table\Templates::findBy(array('device' => $this->device, 'parent_id' => $this->parent_id));
                if (isset($tmpl->id) && ($this->id != $tmpl->id)) {
                    $this->getElement('device')
                         ->addValidator(new Validator\NotEqual($this->device, 'That device is already to that template set.'));
                }
                $tmpl = Table\Templates::findBy(array('device' => $this->device, 'id' => $this->parent_id));
                if (isset($tmpl->id) && ($this->id != $tmpl->id)) {
                    $this->getElement('device')
                         ->addValidator(new Validator\NotEqual($this->device, 'That device is already to that template set.'));
                }
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if ($value['size'] > $config->media_max_filesize) {
                    $this->getElement($key)
                         ->addValidator(new Validator\LessThanEqual($config->media_max_filesize, 'The file must be less than ' . $config->media_max_filesize_formatted . '.'));
                }
                if (preg_match($regex, $value['name']) == 0) {
                    $type = strtoupper(substr($value['name'], (strrpos($value['name'], '.') + 1)));
                    $this->getElement($key)
                         ->addValidator(new Validator\NotEqual($value['name'], 'The ' . $type . ' file type is not allowed.'));
                }
            }
        }

        return $this;
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
        $parents = array(0 => '----');

        // Get parent templates
        $tmpls = Table\Templates::findAll('id ASC');
        foreach ($tmpls->rows as $tmpl) {
            if (($tmpl->id != $tid) && (null === $tmpl->parent_id)) {
                $parents[$tmpl->id] = $tmpl->name;
            }
        }

        // Create initial fields
        $fields1 = array(
            'parent_id' => array(
                'type'       => 'select',
                'label'      => 'Parent:',
                'value'      => $parents
            ),
            'name' => array(
                'type'       => 'text',
                'label'      => 'Name:',
                'required'   => true,
                'attributes' => array(
                    'size'    => 80
                )
            ),
            'content_type' => array(
                'type'  => 'select',
                'label' => 'Content Type:',
                'value' => self::$contentTypes
            ),
            'device' => array(
                'type'  => 'select',
                'label' => 'Device:',
                'value' => self::$mobileTemplates
            )
        );

        $fields2 = array();
        $dynamicFields = false;

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, 0, $tid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                    if ($value['type'] == 'file') {
                        $this->hasFile = true;
                    }
                    if (strpos($key, 'new_') !== false) {
                        $dynamicFields = true;
                    }
                }
            }
        }

        // Create remaining fields
        $fields3 = array(
            'template' => array(
                'type'       => 'textarea',
                'label'      => 'Template:',
                'required'   => true,
                'attributes' => array(
                    'rows'    => 20,
                    'cols'    => 100
                )
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'SAVE',
                'attributes' => array(
                    'class'   => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return updateForm('#template-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class'   => 'update-btn'
                )
            )
        );

        return array($fields1, $fields2, $fields3);
    }

    /**
     * Get the content types
     *
     * @return array
     */
    public static function getContentTypes()
    {
        return self::$contentTypes;
    }

    /**
     * Get the mobile templates
     *
     * @return array
     */
    public static function getMobileTemplates()
    {
        return self::$mobileTemplates;
    }

}

