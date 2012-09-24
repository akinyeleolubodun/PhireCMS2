<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\File\File,
    Pop\Filter\String,
    Pop\Mvc\Model;

class SysConfig extends Model
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
	 * Install config file
	 *
	 * @param Pop\Form\Form $form
	 * @return void
	 */
    public function install($form)
    {
        $cfgFile = new File($_SERVER['DOCUMENT_ROOT'] . BASE_URI . '/config.php');
        $config = $cfgFile->read();

        $systemUri = (string)String::factory($form->system_uri)->dehtml();
        $systemDir = (string)String::factory($form->system_dir)->dehtml();
        $contentDir = (string)String::factory($form->content_dir)->dehtml();

        if (strpos($form->db_adapter, 'Pdo') !== false) {
            $dbInterface = 'Pdo';
            $dbType = strtolower(substr($form->db_adapter, (strrpos($form->db_adapter, '_') + 1)));
        } else {
            $dbInterface = (string)String::factory($form->db_adapter)->dehtml();
            $dbType = null;
        }

        if (strpos($form->db_adapter, 'Sqlite') !== false) {
            $dbName = null;
            $dbUser = null;
            $dbPassword = null;
            $dbHost = null;
        } else {
            $dbName = (string)String::factory($form->db_name)->dehtml();
            $dbUser = (string)String::factory($form->db_username)->dehtml();
            $dbPassword = (string)String::factory($form->db_password)->dehtml();
            $dbHost = (string)String::factory($form->db_host)->dehtml();
        }

        $dbPrefix = (string)String::factory($form->db_prefix)->dehtml();

        $config = str_replace("define('SYSTEM_URI', '/phire');", "define('SYSTEM_URI', '" . $systemUri . "');", $config);
        $config = str_replace("define('SYSTEM_DIR', '/phire-cms');", "define('SYSTEM_DIR', '" . $systemDir . "');", $config);
        $config = str_replace("define('CONTENT_DIR', '/phire-content');", "define('CONTENT_DIR', '" . $contentDir . "');", $config);

        $config = str_replace("define('DB_INTERFACE', '');", "define('DB_INTERFACE', '" . $dbInterface . "');", $config);
        $config = str_replace("define('DB_TYPE', '');", "define('DB_TYPE', '" . $dbType . "');", $config);
        $config = str_replace("define('DB_NAME', '');", "define('DB_NAME', '" . $dbName . "');", $config);
        $config = str_replace("define('DB_USER', '');", "define('DB_USER', '" . $dbUser . "');", $config);
        $config = str_replace("define('DB_PASSWORD', '');", "define('DB_PASSWORD', '" . $dbPassword . "');", $config);
        $config = str_replace("define('DB_HOST', '');", "define('DB_HOST', '" . $dbHost . "');", $config);
        $config = str_replace("define('DB_PREFIX', '');", "define('DB_PREFIX', '" . $dbPrefix . "');", $config);

        $config = str_replace("define('POP_DEFAULT_LANG', 'en');", "define('POP_DEFAULT_LANG', '" . $form->language . "');", $config);

        $this->data['config'] = (string)String::factory($config)->html();
        $this->data['configWritable'] = is_writable($_SERVER['DOCUMENT_ROOT'] . BASE_URI . '/config.php');

        if ($this->data['configWritable']) {
            $cfgFile->write($config)->save();
        }
    }

}

