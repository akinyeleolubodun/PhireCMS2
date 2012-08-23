<?php

return new Pop\Config(array(
    'base'      => $_SERVER['DOCUMENT_ROOT'] . SYSTEM_DIR,
    'docroot'   => $_SERVER['DOCUMENT_ROOT'],
    'databases' => array(
        DB_NAME => Pop\Db\Db::factory(DB_INTERFACE, array (
            'database' => DB_NAME,
            'host'     => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASSWORD
        ))
    ),
    'defaultDb' => DB_NAME
));

