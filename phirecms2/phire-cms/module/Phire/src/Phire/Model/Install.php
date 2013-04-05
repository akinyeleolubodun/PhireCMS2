<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;
use Pop\Db\Db;
use Pop\File\File;
use Pop\Project\Install\Dbs;
use Pop\Web\Session;

class Install extends \Pop\Mvc\Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return self
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);
    }

    /**
     * Install config method
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function config($form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));

        $cfgFile = new File($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config.php');
        $config = $cfgFile->read();

        $appUri = html_entity_decode($form->app_uri, ENT_QUOTES, 'UTF-8');
        $contentPath = html_entity_decode($form->content_path, ENT_QUOTES, 'UTF-8');

        if (strpos($form->db_adapter, 'Pdo') !== false) {
            $dbInterface = 'Pdo';
            $dbType = strtolower(substr($form->db_adapter, (strrpos($form->db_adapter, '\\') + 1)));
        } else {
            $dbInterface = html_entity_decode($form->db_adapter, ENT_QUOTES, 'UTF-8');
            $dbType = null;
        }

        if (strpos($form->db_adapter, 'Sqlite') !== false) {
            touch($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $contentPath . '/.htphire.sqlite');
            $relativeDbName = "__DIR__ . '" . $contentPath . '/.htphire.sqlite';
            $dbName = realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $contentPath . '/.htphire.sqlite');
            $dbUser = null;
            $dbPassword = null;
            $dbHost = null;
            $installFile = $dbName;
            chmod($dbName, 0777);
        } else {
            $relativeDbName = null;
            $dbName = $form->db_name;
            $dbUser = $form->db_username;
            $dbPassword = $form->db_password;
            $dbHost = $form->db_host;
            $installFile = null;
        }

        $dbPrefix = $form->db_prefix;

        $config = str_replace("define('APP_URI', '/phire');", "define('APP_URI', '" . $appUri . "');", $config);
        $config = str_replace("define('CONTENT_PATH', '/phire-content');", "define('CONTENT_PATH', '" . $contentPath . "');", $config);

        $config = str_replace("define('DB_INTERFACE', '');", "define('DB_INTERFACE', '" . $dbInterface . "');", $config);
        $config = str_replace("define('DB_TYPE', '');", "define('DB_TYPE', '" . $dbType . "');", $config);
        $config = str_replace("define('DB_NAME', '');", "define('DB_NAME', " . ((null !== $relativeDbName) ? $relativeDbName : "'" . $dbName) . "');", $config);
        $config = str_replace("define('DB_USER', '');", "define('DB_USER', '" . $dbUser . "');", $config);
        $config = str_replace("define('DB_PASS', '');", "define('DB_PASS', '" . $dbPassword . "');", $config);
        $config = str_replace("define('DB_HOST', '');", "define('DB_HOST', '" . $dbHost . "');", $config);
        $config = str_replace("define('DB_PREFIX', '');", "define('DB_PREFIX', '" . $dbPrefix . "');", $config);

        $config = str_replace("define('POP_LANG', 'en_US');", "define('POP_LANG', '" . $form->language . "');", $config);

        $sess = Session::getInstance();
        $sess->config = serialize(htmlentities($config, ENT_QUOTES, 'UTF-8'));
        $sess->app_uri = $appUri;

        $this->data['configWritable'] = is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config.php');

        if ($this->data['configWritable']) {
            $cfgFile->write($config)->save();
        }

        // Install the database
        $sqlFile = __DIR__ . '/../../../data/phire.' . str_replace(array('pdo\\', 'mysqli' ), array('', 'mysql'), strtolower($form->db_adapter)) . '.sql';

        $db = array(
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPassword,
            'host'     => $dbHost,
            'prefix'   => $dbPrefix,
            'type'     => str_replace('\\', '_', $form->db_adapter)
        );

        Dbs::install($dbName, $db, $sqlFile, $installFile, true);

        if (stripos($form->db_adapter, 'Pdo\\') !== false) {
            $adapter = 'Pdo';
            $type = strtolower(substr($form->db_adapter, (strpos($form->db_adapter, '\\') + 1)));
        } else {
            $adapter = $form->db_adapter;
            $type = null;
        }

        // Set the default system config
        $db = Db::factory($adapter, array(
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPassword,
            'host'     => $dbHost,
            'type'     => $type
        ));

        $db->adapter()->query(
            "UPDATE " . $db->adapter()->escape($dbPrefix) .
                "sites SET domain = '" . $db->adapter()->escape($_SERVER['HTTP_HOST']) .
                "', docroot = '" . $db->adapter()->escape($_SERVER['DOCUMENT_ROOT']) .
                "' WHERE id = 6001"
        );

        // Set the system configuration
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '2.0.0' WHERE setting = 'system_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($_SERVER['DOCUMENT_ROOT']) . "' WHERE setting = 'system_docroot'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . PHP_OS . "' WHERE setting = 'server_os'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($_SERVER['SERVER_SOFTWARE']) . "' WHERE setting = 'server_software'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->version() . "' WHERE setting = 'db_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . PHP_VERSION . "' WHERE setting = 'php_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . date('Y-m-d H:i:s') . "' WHERE setting = 'installed_on'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "user_types SET password_encryption = '" . $db->adapter()->escape($form->password_encryption) . "' WHERE id = 2001");

        if ($form->password_salt != '') {
            $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "user_types SET password_salt = '" . $db->adapter()->escape($form->password_salt) . "' WHERE id = 2001");
        }
    }

}

