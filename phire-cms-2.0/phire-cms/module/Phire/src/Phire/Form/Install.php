<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Form\Form,
    Pop\Form\Element,
    Pop\Locale\Locale,
    Pop\Project\Install\Dbs,
    Pop\Validator\Validator,
    Pop\Version;

class Install extends Form
{

    /**
	 * Available database adapters
	 * @var array
	 */
    protected $dbAdapters = array();

    /**
     * Constructer method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return void
     */
    public function __construct($action, $method, array $fields = null, $indent = null)
    {
        $check = Version::check(Version::DATA);

        foreach ($check as $key => $value) {
            if (strpos($key, 'db') !== false) {
                if ($value == 'Yes') {
                    $db = str_replace('db', '', $key);
                    if ((strpos($db, 'Pdo') !== false) && ($db != 'Pdo')) {
                        $db = 'Pdo_' . ucfirst(strtolower(str_replace('Pdo', '', $db)));
                        $this->dbAdapters[$db] = $db;
                    } else if ($db != 'Pdo') {
                        $db = ucfirst(strtolower($db));
                        $this->dbAdapters[$db] = $db;
                    }
                }
            }
        }

        $this->initFieldsValues = array (
            array (
                'type' => 'select',
                'name' => 'language',
                'label' => 'Language:',
                'required' => true,
                'value' => Locale::factory()->getLanguages(__DIR__ . '/../../../data/locale'),
                'marked' => 'en'
            ),
            array (
                'type' => 'select',
                'name' => 'db_adapter',
                'label' => 'DB Adapter:',
                'required' => true,
                'value' => $this->dbAdapters
            ),
            array (
                'type' => 'text',
                'name' => 'db_name',
                'label' => 'DB Name:',
                'attributes' => array('size', 25)
            ),
            array (
                'type' => 'text',
                'name' => 'db_username',
                'label' => 'DB Username:',
                'attributes' => array('size', 25)
            ),
            array (
                'type' => 'text',
                'name' => 'db_password',
                'label' => 'DB Password:',
                'attributes' => array('size', 25)
            ),
            array (
                'type' => 'text',
                'name' => 'db_host',
                'label' => 'DB Host:',
                'attributes' => array('size', 25),
                'value' => 'localhost'
            ),
            array (
                'type' => 'text',
                'name' => 'db_prefix',
                'label' => 'DB Table Prefix:',
                'attributes' => array('size', 25),
                'value' => 'ph_'
            ),
            array (
                'type' => 'text',
                'name' => 'system_uri',
                'label' => 'System URI:<br /><em style="font-size: 0.75em; color: #666; font-weight: normal;">(How you will access the CMS)</em>',
                'required' => true,
                'attributes' => array('size', 25),
                'value' => SYSTEM_URI
            ),
            array (
                'type' => 'text',
                'name' => 'system_dir',
                'label' => 'System Directory:<br /><em style="font-size: 0.75em; color: #666; font-weight: normal;">(Where the system files are located)</em>',
                'required' => true,
                'attributes' => array('size', 25),
                'value' => SYSTEM_DIR
            ),
            array (
                'type' => 'text',
                'name' => 'content_dir',
                'label' => 'Content Directory:<br /><em style="font-size: 0.75em; color: #666; font-weight: normal;">(Where the content files are located)</em>',
                'required' => true,
                'attributes' => array('size', 25),
                'value' => CONTENT_DIR
            ),
            array (
                'type' => 'submit',
                'name' => 'submit',
                'label' => '&nbsp;',
                'value' => 'NEXT'
            )
        );

        parent::__construct($action, $method, $fields, $indent);
    }

	/**
	 * Set the field values
	 *
	 * @param array $values
	 * @param mixed $filters
	 * @return Pop\Form\Form
	 */
    public function setFieldValues(array $values = null, $filters = null)
    {
        parent::setFieldValues($values, $filters);

        if ($_POST) {
            // Check the system directory
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_URI . $this->system_dir)) {
                $this->getElement('system_dir')->addValidator(new Validator\NotEqual($this->system_dir), 'The system directory does not exist.');
            }

            // Check the content directory
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_URI . $this->content_dir)) {
                $this->getElement('content_dir')->addValidator(new Validator\NotEqual($this->content_dir), 'The content directory does not exist.');
            } else if (!is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_URI . $this->content_dir)) {
                $this->getElement('content_dir')->addValidator(new Validator\NotEqual($this->content_dir), 'The content directory is not writable.');
            }

            if (strpos($this->db_adapter, 'Sqlite') === false) {
                $this->getElement('db_name')->addValidator(new Validator\NotEmpty(), 'The database name is required.');
                $this->getElement('db_username')->addValidator(new Validator\NotEmpty(), 'The database username is required.');
                $this->getElement('db_password')->addValidator(new Validator\NotEmpty(), 'The database password is required.');
                $this->getElement('db_host')->addValidator(new Validator\NotEmpty(), 'The database host is required.');
            }

            // Check the database credentials
            if ($this->isValid()) {
                $oldError = ini_get('error_reporting');
                error_reporting(E_ERROR);

                $dbCheck = Dbs::check(array(
                    'database' => $this->db_name,
                	'username' => $this->db_username,
                    'password' => $this->db_password,
                    'host'     => $this->db_host,
                    'type'     => $this->db_adapter,
                ));

                if (null != $dbCheck) {
                    $this->getElement('db_adapter')->addValidator(new Validator\NotEqual($this->db_adapter), $dbCheck);
                }

                error_reporting($oldError);
            }
        }
    }

}

