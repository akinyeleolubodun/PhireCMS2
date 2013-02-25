<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Db\Db;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\I18n\I18n;
use Pop\Project\Install\Dbs;
use Pop\Validator;
use Pop\Version;

class Install extends Form
{

    /**
     * Available database adapters
     * @var array
     */
    protected $dbAdapters = array();

    /**
     * DB versions
     * @var array
     */
    protected static $dbVersions = array(
        'Mysql'  => '5.0',
        'Pgsql'  => '9.0',
        'Sqlsrv' => '10.0'
    );

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @param  array  $fields
     * @param  string $indent
     * @return \Phire\Form\Install
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
                    } else if (($db != 'Pdo') && ($db != 'Oracle')) {
                        $db = ucfirst(strtolower($db));
                        $this->dbAdapters[$db] = $db;
                    }
                }
            }
        }

        $langs = I18n::getLanguages(__DIR__ . '/../../../data/i18n');
        foreach ($langs as $key => $value) {
            $langs[$key] = substr($value, 0, strpos($value, ' ('));
        }

        $this->initFieldsValues = array (
            array (
                'type' => 'select',
                'name' => 'language',
                'label' => 'Language:',
                'required' => true,
                'value' => $langs,
                'marked' => 'en_US'
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
                'value' => APP_URI
            ),
            array (
                'type' => 'text',
                'name' => 'content_dir',
                'label' => 'Content Directory:<br /><em style="font-size: 0.75em; color: #666; font-weight: normal;">(Where the content files are located)</em>',
                'required' => true,
                'attributes' => array('size', 25),
                'value' => CONTENT_PATH
            ),
            array (
                'type' => 'select',
                'name' => 'password_encryption',
                'label' => 'Password Encryption:',
                'value' => array(
                    '2' => 'SHA1',
                    '1' => 'MD5',
                    '0' => 'None'
                )
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
     * @param mixed $params
     * @return \Phire\Form\Install
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

        if ($_POST) {
            // Check the content directory
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_dir)) {
                $this->getElement('content_dir')->addValidator(new Validator\NotEqual($this->content_dir), 'The content directory does not exist.');
            } else {
                $checkDirs = \Phire\Project::checkDirs($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_dir, true);
                if (count($checkDirs) > 0) {
                    $this->getElement('content_dir')->addValidator(new Validator\NotEqual($this->content_dir), 'The content directory (or subdirectories) are not writable.');
                }
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
                } else {
                    // Check the database version
                    if (strpos($this->db_adapter, 'Sqlite') === false) {
                        $adapter = (stripos($this->db_adapter, 'Pdo_') !== false) ? str_replace('Pdo_', '', $this->db_adapter) : $this->db_adapter;
                        $db = Db::factory($adapter, array(
                            'database' => $this->db_name,
                            'username' => $this->db_username,
                            'password' => $this->db_password,
                            'host'     => $this->db_host,
                            'type'     => strtolower(str_replace('Pdo_', '', $this->db_adapter))
                        ));

                        $version = $db->adapter()->version();
                        $version = substr($version, (strrpos($version, ' ') + 1));
                        if (strpos($version, '-') !== false) {
                            $version = substr($version, 0, strpos($version, '-'));
                        }

                        if (stripos($this->db_adapter, 'Mysql') !== false) {
                            $dbVerKey = 'Mysql';
                        } else if (stripos($this->db_adapter, 'Pgsql') !== false) {
                            $dbVerKey = 'Pgsql';
                        } else if (stripos($this->db_adapter, 'Sqlsrv') !== false) {
                            $dbVerKey = 'Sqlsrv';
                        }

                        if (version_compare($version, self::$dbVersions[$dbVerKey]) < 0) {
                            $this->getElement('db_adapter')->addValidator(new Validator\NotEqual($this->db_adapter), 'The ' . $dbVerKey . ' database version must be ' . self::$dbVersions[$dbVerKey] . ' or greater. (' . $version . ' detected.)');
                        }
                    }
                }

                error_reporting($oldError);
            }
        }

        return $this;
    }

}

