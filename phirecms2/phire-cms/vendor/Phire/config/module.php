<?php

return array(
    'Phire' => new \Pop\Config(array(
        'base'   => realpath(__DIR__ . '/../'),
        'config' => realpath(__DIR__ . '/../config'),
        'data'   => realpath(__DIR__ . '/../data'),
        'src'    => realpath(__DIR__ . '/../src'),
        //'view'   => realpath(__DIR__ . '/../view'),
        'dev'    => true,
        'routes' => array(
            '/' => 'Phire\Controller\IndexController',
            APP_URI => array(
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
                    'resource'   => 'Phire\Controller\Phire\Content\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Content',
                        'href' => '',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\IndexController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Content Types',
                        'href' => 'types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\TypesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Categories',
                        'href' => 'categories',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\CategoriesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Templates',
                        'href' => 'templates',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Content\TemplatesController',
                            'permission' => 'index'
                        )
                    )
                )
            ),
            array(
                'name' => 'Extensions',
                'href' => BASE_PATH . APP_URI . '/extensions',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Extensions\IndexController',
                    'permission' => 'index'
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
                    'resource'   => 'Phire\Controller\Phire\User\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Users',
                        'href' => '',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\IndexController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'User Types',
                        'href' => 'types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\TypesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'User Roles',
                        'href' => 'roles',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\RolesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'User Sessions',
                        'href' => 'sessions',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\SessionsController',
                            'permission' => 'index'
                        )
                    )
                )
            ),
            array(
                'name'     => 'Configuration',
                'href'     => BASE_PATH . APP_URI . '/config',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\ConfigController',
                    'permission' => 'index'
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
