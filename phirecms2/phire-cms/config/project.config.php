<?php

$config = array(
    'base'    => realpath($_SERVER['DOCUMENT_ROOT'] . APP_PATH),
    'docroot' => realpath($_SERVER['DOCUMENT_ROOT'])
);

if ((DB_INTERFACE != '') && (DB_NAME != '')) {
    $config['databases'] = array(
        DB_NAME => \Pop\Db\Db::factory(DB_INTERFACE, array (
            'type'     => DB_TYPE,
            'database' => DB_NAME,
            'host'     => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS
        ))
    );
    $config['defaultDb'] = DB_NAME;
}

return new \Pop\Config($config);
