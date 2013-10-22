<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Db\Db;
use Pop\Form\Form;
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
        'Pgsql'  => '9.0'
    );

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @return self
     */
    public function __construct($action = null, $method = 'post')
    {
        $this->initFieldsValues = $this->getInitFields();
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'install-form');
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
            // Check the content directory
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path)) {
                $this->getElement('content_path')->addValidator(new Validator\NotEqual($this->content_path, 'The content directory does not exist.'));
            } else {
                $checkDirs = \Phire\Project::checkDirs($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path, true);
                if (count($checkDirs) > 0) {
                    $this->getElement('content_path')->addValidator(new Validator\NotEqual($this->content_path, 'The content directory (or subdirectories) are not writable.'));
                }
            }

            // Check the password encryption
            if (($this->password_encryption == 3) && ($this->password_salt == '')) {
                $this->getElement('password_salt')->addValidator(new Validator\NotEmpty(null, 'A password salt is required for the \'Crypt\' encryption.'));
            }

            // If not SQLite, check the DB parameters
            if (strpos($this->db_adapter, 'Sqlite') === false) {
                $this->getElement('db_name')->addValidator(new Validator\NotEmpty(null, 'The database name is required.'));
                $this->getElement('db_username')->addValidator(new Validator\NotEmpty(null, 'The database username is required.'));
                $this->getElement('db_password')->addValidator(new Validator\NotEmpty(null, 'The database password is required.'));
                $this->getElement('db_host')->addValidator(new Validator\NotEmpty(null, 'The database host is required.'));
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
                    'type'     => str_replace('\\', '_', $this->db_adapter),
                ));

                // If there is a DB error
                if (null != $dbCheck) {
                    $this->getElement('db_adapter')->addValidator(new Validator\NotEqual($this->db_adapter, wordwrap($dbCheck, 50, '<br />')));
                } else {
                    // Check the database version
                    if (strpos($this->db_adapter, 'Sqlite') === false) {
                        $adapter = (stripos($this->db_adapter, 'Pdo\\') !== false) ? str_replace('Pdo\\', '', $this->db_adapter) : $this->db_adapter;
                        $db = Db::factory($adapter, array(
                            'database' => $this->db_name,
                            'username' => $this->db_username,
                            'password' => $this->db_password,
                            'host'     => $this->db_host,
                            'type'     => strtolower(str_replace('Pdo\\', '', $this->db_adapter))
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
                        } else {
                            $dbVerKey = null;
                        }

                        if ((null !== $dbVerKey) && (version_compare($version, self::$dbVersions[$dbVerKey]) < 0)) {
                            $this->getElement('db_adapter')->addValidator(new Validator\NotEqual($this->db_adapter, 'The ' . $dbVerKey . ' database version must be ' . self::$dbVersions[$dbVerKey] . ' or greater. (' . $version . ' detected.)'));
                        }
                    }
                }

                error_reporting($oldError);
            }
        }

        return $this;
    }

    /**
     * Get the init field values
     *
     * @return array
     */
    protected function getInitFields()
    {
        $check = Version::check(Version::DATA);

        foreach ($check as $key => $value) {
            if (strpos($key, 'db') !== false) {
                if (($value == 'Yes') && (stripos($key, 'sqlsrv') === false) && (stripos($key, 'oracle') === false)) {
                    $db = str_replace('db', '', $key);
                    if ((strpos($db, 'Pdo') !== false) && ($db != 'Pdo')) {
                        $db = 'Pdo\\' . ucfirst(strtolower(str_replace('Pdo', '', $db)));
                        $this->dbAdapters[$db] = $db;
                    } else if ($db != 'Pdo') {
                        $db = ucfirst(strtolower($db));
                        if ($db != 'Mysql') {
                            $this->dbAdapters[$db] = $db;
                        }
                    }
                }
            }
        }

        $langs = I18n::getLanguages(__DIR__ . '/../../../data/i18n');
        foreach ($langs as $key => $value) {
            $langs[$key] = substr($value, 0, strpos($value, ' ('));
        }

        $fields = array(
            'language' => array (
                'type' => 'select',
                'label' => 'Language:',
                'value' => $langs,
                'marked' => 'en_US'
            ),
            'db_adapter' => array (
                'type' => 'select',
                'label' => 'DB Adapter:',
                'required' => true,
                'value' => $this->dbAdapters
            ),
            'db_name' => array (
                'type' => 'text',
                'label' => 'DB Name:',
                'attributes' => array('size' => 30)
            ),
            'db_username' => array (
                'type' => 'text',
                'label' => 'DB Username:',
                'attributes' => array('size' => 30)
            ),
            'db_password' => array (
                'type' => 'text',
                'label' => 'DB Password:',
                'attributes' => array('size' => 30)
            ),
            'db_host' => array (
                'type' => 'text',
                'label' => 'DB Host:',
                'attributes' => array('size' => 30),
                'value' => 'localhost'
            ),
            'db_prefix' => array (
                'type' => 'text',
                'name' => 'db_prefix',
                'label' => 'DB Table Prefix:',
                'attributes' => array('size' => 30),
                'value' => 'ph_'
            ),
            'app_uri' => array (
                'type' => 'text',
                'label' => 'Application URI:<br /><em style="font-size: 0.9em; color: #666; font-weight: normal;">(How you will access the system)</em>',
                'attributes' => array('size' => 30),
                'value' => APP_URI
            ),
            'content_path' => array (
                'type' => 'text',
                'label' => 'Content Path:<br /><em style="font-size: 0.9em; color: #666; font-weight: normal;">(Where assets will be located)</em>',
                'required' => true,
                'attributes' => array('size' => 30),
                'value' => CONTENT_PATH
            ),
            'password_encryption' => array (
                'type' => 'select',
                'label' => 'Password Encryption:',
                'value' => array(
                    '1' => 'MD5',
                    '2' => 'SHA1',
                    '3' => 'Crypt',
                    '4' => 'Bcrypt',
                    '5' => 'Mcrypt',
                    '6' => 'Crypt_MD5',
                    '7' => 'Crypt_SHA256',
                    '8' => 'Crypt_SHA512',
                    '0' => 'None'
                ),
                'marked' => 4
            ),
            'submit' => array (
                'type' => 'submit',
                'label' => '&nbsp;',
                'value' => 'NEXT',
                'attributes' => array(
                    'class' => 'install-btn'
                )
            )
        );

        return $fields;
    }

}

