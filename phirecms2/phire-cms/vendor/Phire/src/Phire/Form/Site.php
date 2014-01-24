<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form as F;
use Pop\Validator;
use Phire\Table;

class Site extends F
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  int    $sid
     * @return self
     */
    public function __construct($action = null, $method = 'post', $sid = 0)
    {
        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, 0, $sid);
        if ($newFields['dynamic']) {
            $dynamicFields = true;
        }
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        $fields1 = array(
            'title' => array(
                'type'       => 'text',
                'label'      => 'Title:',
                'required'   => true,
                'attributes' => array('size' => 60)
            ),
            'domain' => array(
                'type'       => 'text',
                'label'      => 'Domain:',
                'required'   => true,
                'attributes' => array('size' => 60)
            ),
            'document_root' => array(
                'type'       => 'text',
                'label'      => 'Document Root:',
                'required'   => true,
                'attributes' => array('size' => 60)
            ),
            'base_path' => array(
                'type'       => 'text',
                'label'      => 'Base Path:',
                'attributes' => array('size' => 60),
                'value'      => BASE_PATH
            )
        );
        $fields2 = array(
            'force_ssl' => array(
                'type'     => 'radio',
                'label'    => 'Force SSL:',
                'value' => array(
                    '0' => 'No',
                    '1' => 'Yes'
                ),
                'marked' => '0'
            ),
            'live' => array(
                'type'     => 'radio',
                'label'    => 'Live:',
                'value'    => array(
                    '0' => 'No',
                    '1' => 'Yes'
                ),
                'marked' => '1'
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'SAVE',
                'attributes' => array(
                    'class' => 'save-btn',
                    'style' => 'width: 216px;'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#site-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class'   => 'update-btn',
                    'style'   => 'width: 216px;'
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
        );

        $allFields = array($fields1);
        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }
        $allFields[] = $fields2;

        $this->initFieldsValues = $allFields;

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'site-form');
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

            $site = Table\Sites::findBy(array('domain' => $this->domain));
            if ((isset($site->id) && ($this->id != $site->id)) || ($this->domain == $_SERVER['HTTP_HOST'])) {
                $this->getElement('domain')
                     ->addValidator(new Validator\NotEqual($this->domain, 'That site domain already exists.'));
            }

            $docRoot = ((substr($this->document_root, -1) == '/') || (substr($this->document_root, -1) == "\\")) ?
                substr($this->document_root, 0, -1) : $this->document_root;

            if ($this->base_path != '') {
                $basePath = ((substr($this->base_path, 0, 1) != '/') || (substr($this->base_path, 0, 1) != "\\")) ?
                    '/' . $this->base_path : $this->base_path;

                if ((substr($basePath, -1) == '/') || (substr($basePath, -1) == "\\")) {
                    $basePath = substr($basePath, 0, -1);
                }
            } else {
                $basePath = '';
            }

            if (!file_exists($docRoot)) {
                $this->getElement('document_root')
                     ->addValidator(new Validator\NotEqual($this->document_root, 'That site document root does not exists.'));
            } else if (!file_exists($docRoot . $basePath)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, 'The base path does not exist under that document root.'));
            } else if (!file_exists($docRoot . $basePath . DIRECTORY_SEPARATOR . 'index.php')) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, 'The index controller does not exist under that document root and base path.'));
            } else if (!file_exists($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, 'The content path does not exist under that document root and base path.'));
            } else if (!is_writable($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('base_path')
                    ->addValidator(new Validator\NotEqual($this->base_path, 'The content path is not writable under that document root and base path.'));
            }
        }

        return $this;
    }

}

