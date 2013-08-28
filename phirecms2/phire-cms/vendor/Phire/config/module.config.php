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
                '/'         => 'Phire\Controller\User\IndexController',
                '/install'  => 'Phire\Controller\Install\IndexController',
                '/content'  => array(
                    '/'           => 'Phire\Controller\Content\IndexController',
                    '/categories' => 'Phire\Controller\Content\CategoriesController',
                    '/templates'  => 'Phire\Controller\Content\TemplatesController',
                    '/types'      => 'Phire\Controller\Content\TypesController'
                ),
                '/extensions' => 'Phire\Controller\Extensions\IndexController',
                '/users' => array(
                    '/'         => 'Phire\Controller\User\UsersController',
                    '/roles'    => 'Phire\Controller\User\RolesController',
                    '/sessions' => 'Phire\Controller\User\SessionsController',
                    '/types'    => 'Phire\Controller\User\TypesController'
                ),
                '/config'   => 'Phire\Controller\Content\ConfigController'
            )
        ),
        'nav'    => array(
            array(
                'name' => 'Content',
                'href' => BASE_PATH . APP_URI . '/content',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Content\IndexController'
                ),
                'children' => array(
                    array(
                        'name' => 'Categories',
                        'href' => BASE_PATH . APP_URI . '/content/categories',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Content\CategoriesController'
                        )
                    ),
                    array(
                        'name' => 'Templates',
                        'href' => BASE_PATH . APP_URI . '/content/templates',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Content\TemplatesController'
                        )
                    ),
                    array(
                        'name' => 'Content Types',
                        'href' => BASE_PATH . APP_URI . '/content/types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Content\TypesController'
                        )
                    )
                )
            ),
            array(
                'name' => 'Extensions',
                'href' => BASE_PATH . APP_URI . '/extensions',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Extensions\IndexController'
                ),
                'children' => array(
                    array(
                        'name' => 'Themes',
                        'href' => BASE_PATH . APP_URI . '/extensions/themes',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Extensions\IndexController',
                            'permission' => 'themes'
                        )
                    ),
                    array(
                        'name' => 'Modules',
                        'href' => BASE_PATH . APP_URI . '/extensions/modules',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Extensions\IndexController',
                            'permission' => 'modules'
                        )
                    )
                )
            ),
            array(
                'name' => 'Users',
                'href' => BASE_PATH . APP_URI . '/users',
                'acl' => array(
                    'resource'   => 'Phire\Controller\User\UsersController'
                ),
                'children' => array(
                    array(
                        'name' => 'User Roles',
                        'href' => BASE_PATH . APP_URI . '/users/roles',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\User\RolesController'
                        )
                    ),
                    array(
                        'name' => 'User Types',
                        'href' => BASE_PATH . APP_URI . '/users/types',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\User\TypesController'
                        )
                    ),
                    array(
                        'name' => 'User Sessions',
                        'href' => BASE_PATH . APP_URI . '/users/sessions',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\User\SessionsController'
                        )
                    )
                )
            ),
            array(
                'name'     => 'Configuration',
                'href'     => BASE_PATH . APP_URI . '/config',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Content\ConfigController'
                )
            )
        ),
        // Exclude parameter for excluding user-specific resources (controllers) and permissions (actions)
        'exclude' => array(
            'Phire\Controller\IndexController',
            'Phire\Controller\User\IndexController',
            'Phire\Controller\Install\IndexController'
        )
    ))
);

