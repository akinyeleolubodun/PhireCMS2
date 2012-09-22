<?php

$config = array(
    'base'    => $_SERVER['DOCUMENT_ROOT'] . SYSTEM_DIR,
    'docroot' => $_SERVER['DOCUMENT_ROOT']
);

if ((DB_INTERFACE != '') && (DB_NAME != '')) {
    $config['databases'] = array(
            DB_NAME => Pop\Db\Db::factory(DB_INTERFACE, array (
                'database' => DB_NAME,
                'host'     => DB_HOST,
                'username' => DB_USER,
                'password' => DB_PASSWORD
            ))
        );
    $config['defaultDb'] = DB_NAME;
}

return new Pop\Config($config);
