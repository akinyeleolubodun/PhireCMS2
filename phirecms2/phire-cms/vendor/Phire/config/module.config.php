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
                '/' => 'Phire\Controller\User\IndexController',
                '/content'  => array(
                    '/'           => 'Phire\Controller\Content\IndexController',
                    '/categories' => 'Phire\Controller\Content\CategoriesController',
                    '/templates'  => 'Phire\Controller\Content\TemplatesController',
                    '/types'      => 'Phire\Controller\Content\TypesController',
                    '/config'     => 'Phire\Controller\Content\ConfigController',
                ),
                '/users' => array(
                    '/'         => 'Phire\Controller\User\UsersController',
                    '/roles'    => 'Phire\Controller\User\RolesController',
                    '/sessions' => 'Phire\Controller\User\SessionsController',
                    '/types'    => 'Phire\Controller\User\TypesController'
                )
            )
        ),
        'nav'    => array(
            array(
                'name'     => 'Phire',
                'href'     => BASE_PATH . APP_URI,
                'children' => array(
                    array(
                        'name' => 'Content',
                        'href' => BASE_PATH . APP_URI . '/content',
                        'children' => array(
                            array(
                                'name' => 'Categories',
                                'href' => BASE_PATH . APP_URI . '/content/categories'
                            ),
                            array(
                                'name' => 'Templates',
                                'href' => BASE_PATH . APP_URI . '/content/templates'
                            ),
                            array(
                                'name' => 'Content Types',
                                'href' => BASE_PATH . APP_URI . '/content/types'
                            ),
                            array(
                                'name'     => 'Configuration',
                                'href'     => BASE_PATH . APP_URI . '/content/config'
                            )
                        )
                    ),
                    array(
                        'name' => 'Modules',
                        'href' => BASE_PATH . APP_URI . '/modules'
                    ),
                    array(
                        'name' => 'Users',
                        'href' => BASE_PATH . APP_URI . '/users',
                        'children' => array(
                            array(
                                'name' => 'User Roles',
                                'href' => BASE_PATH . APP_URI . '/users/roles'
                            ),
                            array(
                                'name' => 'User Sessions',
                                'href' => BASE_PATH . APP_URI . '/users/sessions'
                            ),
                            array(
                                'name' => 'User Types',
                                'href' => BASE_PATH . APP_URI . '/users/types'
                            )
                        )
                    )
                )
            )
        ),
        // Exclude parameter for excluding user-specific resources (controllers) and permissions (actions)
        'exclude' => array(
            'Phire\Controller\IndexController'
        )
    ))
);

