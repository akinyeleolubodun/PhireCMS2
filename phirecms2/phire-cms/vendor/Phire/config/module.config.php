<?php

return array(
    'Phire' => new \Pop\Config(array(
        'base'   => realpath(__DIR__ . '/../'),
        'config' => realpath(__DIR__ . '/../config'),
        'data'   => realpath(__DIR__ . '/../data'),
        'src'    => realpath(__DIR__ . '/../src'),
        //'view'   => realpath(__DIR__ . '/../view'),
        'routes' => array(
            '/' => 'Phire\Controller\IndexController',
            APP_URI  => array(
                '/'         => 'Phire\Controller\Phire\IndexController',
                '/install'  => 'Phire\Controller\Phire\Install\IndexController',
                '/content'  => array(
                    '/'           => 'Phire\Controller\Phire\Content\IndexController',
                    '/categories' => 'Phire\Controller\Phire\Content\CategoriesController',
                    '/templates'  => 'Phire\Controller\Phire\Content\TemplatesController',
                    '/types'      => 'Phire\Controller\Phire\Content\TypesController'
                ),
                '/extensions' => 'Phire\Controller\Phire\Extensions\IndexController',
                '/users' => array(
                    '/'         => 'Phire\Controller\Phire\User\IndexController',
                    '/roles'    => 'Phire\Controller\Phire\User\RolesController',
                    '/sessions' => 'Phire\Controller\Phire\User\SessionsController',
                    '/types'    => 'Phire\Controller\Phire\User\TypesController'
                ),
                '/config'   => 'Phire\Controller\Phire\ConfigController'
            )
        ),
        'nav'    => array(
            array(
                'name' => 'Content',
                'href' => BASE_PATH . APP_URI . '/content',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Content\IndexController'
                ),
                'children' => array(
                    array(
                        'name' => 'Content',
                        'href' => '',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\IndexController'
                        )
                    ),
                    array(
                        'name' => 'Content Types',
                        'href' => 'types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\TypesController'
                        )
                    ),
                    array(
                        'name' => 'Categories',
                        'href' => 'categories',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\CategoriesController'
                        )
                    ),
                    array(
                        'name' => 'Templates',
                        'href' => 'templates',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\TemplatesController'
                        )
                    )
                )
            ),
            array(
                'name' => 'Extensions',
                'href' => BASE_PATH . APP_URI . '/extensions',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Extensions\IndexController'
                ),
                'children' => array(
                    array(
                        'name' => 'Themes',
                        'href' => 'themes',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Extensions\IndexController',
                            'permission' => 'themes'
                        )
                    ),
                    array(
                        'name' => 'Modules',
                        'href' => 'modules',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Extensions\IndexController',
                            'permission' => 'modules'
                        )
                    )
                )
            ),
            array(
                'name' => 'Users',
                'href' => BASE_PATH . APP_URI . '/users',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\User\IndexController'
                ),
                'children' => array(
                    array(
                        'name' => 'Users',
                        'href' => '',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\IndexController'
                        )
                    ),
                    array(
                        'name' => 'User Types',
                        'href' => 'types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\TypesController'
                        )
                    ),
                    array(
                        'name' => 'User Roles',
                        'href' => 'roles',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\RolesController'
                        )
                    ),
                    array(
                        'name' => 'User Sessions',
                        'href' => 'sessions',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\SessionsController'
                        )
                    )
                )
            ),
            array(
                'name'     => 'Configuration',
                'href'     => BASE_PATH . APP_URI . '/config',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\ConfigController'
                )
            )
        ),
        // Exclude parameter for excluding user-specific resources (controllers) and permissions (actions)
        'exclude' => array(
            'Phire\Controller\IndexController',
            'Phire\Controller\Phire\IndexController',
            'Phire\Controller\Phire\Install\IndexController'
        )
    ))
);

