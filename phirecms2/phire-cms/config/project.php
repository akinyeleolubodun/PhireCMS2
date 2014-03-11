<?php
/**
 * Phire CMS 2.0 Project Config File
 */

$config = array(
    'base'    => realpath(__DIR__ . '/../'),
    'docroot' => realpath($_SERVER['DOCUMENT_ROOT']),
);

if ((DB_INTERFACE != '') && (DB_NAME != '')) {
    $config['databases'] = array(
        DB_NAME => \Pop\Db\Db::factory(DB_INTERFACE, array(
            'type'     => DB_TYPE,
            'database' => DB_NAME,
            'host'     => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS
        ))
    );
    $config['defaultDb'] = DB_NAME;
    //$config['log']       = __DIR__ . '/../../' . CONTENT_PATH . '/log/phire.log';
}

return new \Pop\Config($config);
