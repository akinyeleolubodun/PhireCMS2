<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class Update extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @return self
     */
    public function __construct($action = null, $method = 'post')
    {
        parent::__construct($action, $method, null, '        ');

        $site      = Table\Sites::getSite();
        $domain    = 'ftp.' . str_replace('www.', '', $site->domain);
        $rootValue = (($_POST) && isset($_POST['change_ftp_root'])) ? $_POST['change_ftp_root'] : null;

        $fields1 = array(
            'ftp_address' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('FTP Address'),
                'required'   => true,
                'attributes' => array('size' => 40),
                'value'      => $domain
            ),
            'username' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Username'),
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'password' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Password'),
                'required'   => true,
                'attributes' => array('size' => 40)
            ),
            'ftp_root' => array(
                'type'       => 'radio',
                'label'      => $this->i18n->__('FTP Root'),
                'value' => array(
                    '0' => $this->i18n->__('Log directly into the document root.<br /><br />'),
                    '1' => $this->i18n->__('No, change the directory to <input style="margin-left: 5px; width: 150px; height: 15px; font-size: 0.9em;" type="text" size="18" name="change_ftp_root" value="' . $rootValue . '" />')
                ),
                'marked' => '0'
            )
        );

        $fields2 = array(
            'submit' => array(
                'type'  => 'submit',
                'value' => $this->i18n->__('UPDATE'),
                'attributes' => array(
                    'class' => 'save-btn'
                )
            ),
            'use_pasv' => array(
                'type'     => 'radio',
                'label'    => $this->i18n->__('Use PASV'),
                'value' => array(
                    '1' => $this->i18n->__('Yes'),
                    '0' => $this->i18n->__('No')
                ),
                'marked' => '1'
            ),
            'protocol' => array(
                'type'     => 'radio',
                'label'    => $this->i18n->__('Protocol'),
                'value'    => array(
                    '0' => $this->i18n->__('FTP'),
                    '1' => $this->i18n->__('FTPS')
                ),
                'marked' => '0'
            ),
            'type' => array(
                'type'  => 'hidden',
                'value' => (isset($_GET['type']) ? $_GET['type'] : 'system')
            ),
            'base_path' => array(
                'type'  => 'hidden',
                'value' => BASE_PATH
            ),
            'content_path' => array(
                'type'  => 'hidden',
                'value' => CONTENT_PATH
            ),
            'app_path' => array(
                'type'  => 'hidden',
                'value' => APP_PATH
            )
        );

        $this->initFieldsValues = array($fields2, $fields1);
        $this->setAttributes('id', 'update-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null)
    {
        parent::setFieldValues($values, $filters);

        // Add validators for checking dupe names and devices
        if (($_POST) && isset($_POST['id'])) {

            $site = Table\Sites::findBy(array('domain' => $this->domain));
            if ((isset($site->id) && ($this->id != $site->id)) || ($this->domain == $_SERVER['HTTP_HOST'])) {
                $this->getElement('domain')
                     ->addValidator(new Validator\NotEqual($this->domain, $this->i18n->__('That site domain already exists.')));
            }

            $site = Table\Sites::findBy(array('document_root' => $this->document_root));
            if ((isset($site->id) && ($this->id != $site->id))) {
                $this->getElement('document_root')
                    ->addValidator(new Validator\NotEqual($this->document_root, $this->i18n->__('That site document root already exists.')));
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
                     ->addValidator(new Validator\NotEqual($this->document_root, $this->i18n->__('That site document root does not exists.')));
            } else if (!file_exists($docRoot . $basePath)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The base path does not exist under that document root.')));
            } else if (!file_exists($docRoot . $basePath . DIRECTORY_SEPARATOR . 'index.php')) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The index controller does not exist under that document root and base path.')));
            } else if (!file_exists($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The content path does not exist under that document root and base path.')));
            } else if (!is_writable($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH)) {
                $this->getElement('base_path')
                     ->addValidator(new Validator\NotEqual($this->base_path, $this->i18n->__('The content path is not writable under that document root and base path.')));
            }
        }

        $this->checkFiles();

        return $this;
    }

}

