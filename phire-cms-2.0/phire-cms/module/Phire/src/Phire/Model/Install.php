<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table,
    Pop\Db\Db,
    Pop\File\File,
    Pop\Mail\Mail,
    Pop\Mvc\Model,
    Pop\Project\Install\Dbs,
    Pop\Web\Session;

class Install extends Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return void
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);
    }

    /**
     * Install system
     *
     * @param Pop\Form\Form $form
     * @return void
     */
    public function install($form)
    {
        $cfgFile = new File($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config.php');
        $config = $cfgFile->read();

        $systemUri = html_entity_decode($form->system_uri, ENT_QUOTES, 'UTF-8');
        $contentDir = html_entity_decode($form->content_dir, ENT_QUOTES, 'UTF-8');

        if (strpos($form->db_adapter, 'Pdo') !== false) {
            $dbInterface = 'Pdo';
            $dbType = strtolower(substr($form->db_adapter, (strrpos($form->db_adapter, '_') + 1)));
        } else {
            $dbInterface = html_entity_decode($form->db_adapter, ENT_QUOTES, 'UTF-8');
            $dbType = null;
        }

        if (strpos($form->db_adapter, 'Sqlite') !== false) {
            touch($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $contentDir . '/.htphire.sqlite');
            $dbName = realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $contentDir . '/.htphire.sqlite');
            $dbUser = null;
            $dbPassword = null;
            $dbHost = null;
            $installFile = $dbName;
            chmod($dbName, 0777);
        } else {
            $dbName = html_entity_decode($form->db_name, ENT_QUOTES, 'UTF-8');
            $dbUser = html_entity_decode($form->db_username, ENT_QUOTES, 'UTF-8');
            $dbPassword = html_entity_decode($form->db_password, ENT_QUOTES, 'UTF-8');
            $dbHost = html_entity_decode($form->db_host, ENT_QUOTES, 'UTF-8');
            $installFile = null;
        }

        $dbPrefix = html_entity_decode($form->db_prefix, ENT_QUOTES, 'UTF-8');

        $config = str_replace("define('APP_URI', '/phire');", "define('APP_URI', '" . $systemUri . "');", $config);
        $config = str_replace("define('CONTENT_PATH', '/phire-content');", "define('CONTENT_PATH', '" . $contentDir . "');", $config);

        $config = str_replace("define('DB_INTERFACE', '');", "define('DB_INTERFACE', '" . $dbInterface . "');", $config);
        $config = str_replace("define('DB_TYPE', '');", "define('DB_TYPE', '" . $dbType . "');", $config);
        $config = str_replace("define('DB_NAME', '');", "define('DB_NAME', '" . $dbName . "');", $config);
        $config = str_replace("define('DB_USER', '');", "define('DB_USER', '" . $dbUser . "');", $config);
        $config = str_replace("define('DB_PASS', '');", "define('DB_PASS', '" . $dbPassword . "');", $config);
        $config = str_replace("define('DB_HOST', '');", "define('DB_HOST', '" . $dbHost . "');", $config);
        $config = str_replace("define('DB_PREFIX', '');", "define('DB_PREFIX', '" . $dbPrefix . "');", $config);

        $config = str_replace("define('POP_DEFAULT_LANG', 'en');", "define('POP_DEFAULT_LANG', '" . $form->language . "');", $config);

        $sess = Session::getInstance();
        $sess->config = serialize(htmlentities($config, ENT_QUOTES, 'UTF-8'));
        $sess->system_uri = $systemUri;

        $this->data['configWritable'] = is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config.php');

        if ($this->data['configWritable']) {
            $cfgFile->write($config)->save();
        }

        // Install the database
        $sqlFile = __DIR__ . '/../../../data/phire.' . str_replace('mysqli', 'mysql', strtolower(str_replace('Pdo_', '', $form->db_adapter))) . '.sql';

        $db = array(
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPassword,
            'host'     => $dbHost,
            'prefix'   => $dbPrefix,
            'type'     => $form->db_adapter
        );

        Dbs::install($dbName, $db, $sqlFile, $installFile, true);

        if (stripos($form->db_adapter, 'Pdo_') !== false) {
            $adapter = 'Pdo';
            $type = strtolower(substr($form->db_adapter, (strpos($form->db_adapter, '_') + 1)));
        } else {
            $adapter = $form->db_adapter;
            $type = null;
        }

        // Set the default site's domain and docroot
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
            "' WHERE id = 2001"
        );

        // Set the system configuration
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '2.0' WHERE setting = 'system_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . $db->adapter()->escape($_SERVER['DOCUMENT_ROOT']) . "' WHERE setting = 'system_docroot'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . PHP_OS . "' WHERE setting = 'server_os'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . $db->adapter()->escape($_SERVER['SERVER_SOFTWARE']) . "' WHERE setting = 'server_software'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . $db->adapter()->version() . "' WHERE setting = 'db_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . PHP_VERSION . "' WHERE setting = 'php_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . date('Y-m-d H:i:s') . "' WHERE setting = 'installed_on'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "sys_config SET value = '" . $db->adapter()->escape($form->password_encryption) . "' WHERE setting = 'password_encryption'");
    }

    /**
     * Install initial user
     *
     * @param Pop\Form\Form $form
     * @return void
     */
    public function installUser($form)
    {
        $password = html_entity_decode($form->password1, ENT_QUOTES, 'UTF-8');
        $origPassword = $password;

        switch (Table\SysConfig::findById('password_encryption')->value) {
            case 1:
                $password = md5($password);
                break;
            case 2:
                $password = sha1($password);
        }

        $user = new Table\Users(array(
            'username'        => html_entity_decode($form->username, ENT_QUOTES, 'UTF-8'),
            'password'        => $password,
            'fname'           => html_entity_decode($form->fname, ENT_QUOTES, 'UTF-8'),
            'lname'           => html_entity_decode($form->lname, ENT_QUOTES, 'UTF-8'),
            'email'           => html_entity_decode($form->email1, ENT_QUOTES, 'UTF-8'),
            'allowed_sites'   => implode('|', $form->allowed_sites),
            'access_id'       => $form->access_id,
            'last_login'      => '',
            'last_ua'         => '',
            'last_ip'         => '',
            'failed_attempts' => 0
        ));

        $user->save();

        if (null !== $form->send_creds) {
            $rcpts = array(
                'name'       => $user->fname . ' ' . $user->lname,
                'email'      => $user->email,
                'username'   => $user->username,
                'password'   => $origPassword,
                'login_link' => 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . APP_URI . '/login'
            );

            $mail = new Mail($rcpts, 'Phire CMS: New User for ' . $_SERVER['HTTP_HOST']);
            $mail->setHeaders(array(
                'From'     => array(
                	'name'  => 'No Reply',
                	'email' => 'noreply@' . str_replace('www.', '', $_SERVER['HTTP_HOST'])
                ),
                'Reply-To' => array(
                	'name' => 'No Reply',
                	'email' => 'noreply@' . str_replace('www.', '', $_SERVER['HTTP_HOST'])
                )
            ));

            $mail->setText(file_get_contents(__DIR__ . '/../../../view/phire/mail/adduser.phtml'));
            $mail->send();
        }
    }

}

