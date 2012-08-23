<?php

return new Pop\Config(array(
    'name'   => 'Phire',
    'base'   => realpath(__DIR__ . '/../../../module/Phire'),
    'config' => realpath(__DIR__ . '/../../../module/Phire/config'),
    'data'   => realpath(__DIR__ . '/../../../module/Phire/data'),
    'src'    => realpath(__DIR__ . '/../../../module/Phire/src'),
    'view'   => realpath(__DIR__ . '/../../../phire/module/Phire/view')
));

