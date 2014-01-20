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
            'title' => array(
                'type'       => 'text',
                'label'      => 'Title:',
                'required'   => true,
                'attributes' => array('size' => 60)
            )
        );
        $fields2 = array(
            'force_ssl' => array(
                'type'     => 'radio',
                'label'    => 'Force SSL:',
                'required' => true,
                'value' => array(
                    '0' => 'No',
                    '1' => 'Yes'
                ),
                'marked' => '0'
            ),
            'live' => array(
                'type'     => 'radio',
                'label'    => 'Live:',
                'required' => true,
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
                    'onclick' => "return phire.updateForm('#content-type-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
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
            if (!file_exists($this->document_root)) {
                $this->getElement('document_root')
                     ->addValidator(new Validator\NotEqual($this->document_root, 'That site document root does not exists.'));
            } else if (!file_exists($this->document_root . DIRECTORY_SEPARATOR . BASE_PATH)) {
                $this->getElement('document_root')
                     ->addValidator(new Validator\NotEqual($this->document_root, 'The base path does not exist under that document root.'));
            } else if (!file_exists($this->document_root . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . 'index.php')) {
                $this->getElement('document_root')
                     ->addValidator(new Validator\NotEqual($this->document_root, 'The index controller does not exist under that document root and base path.'));
            } else if (!file_exists($this->document_root . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('document_root')
                     ->addValidator(new Validator\NotEqual($this->document_root, 'The content path does not exist under that document root and base path.'));
            } else if (!is_writable($this->document_root . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('document_root')
                    ->addValidator(new Validator\NotEqual($this->document_root, 'The content path is not writable under that document root and base path.'));
            }
        }

        return $this;
    }

}

