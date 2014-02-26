<?php
/**
 * Phire CMS 2.0 Module Config File
 */
return array(
    'Phire' => new \Pop\Config(array(
        'base'   => realpath(__DIR__ . '/../'),
        'config' => realpath(__DIR__ . '/../config'),
        'data'   => realpath(__DIR__ . '/../data'),
        'src'    => realpath(__DIR__ . '/../src'),
        //'view'   => realpath(__DIR__ . '/../view'),
        'dev'    => true,
        // Main Phire Routes
        'routes' => array(
            '/' => 'Phire\Controller\IndexController',
            APP_URI => array(
                '/'         => 'Phire\Controller\Phire\IndexController',
                '/install'  => 'Phire\Controller\Phire\Install\IndexController',
                '/content'  => array(
                    '/'           => 'Phire\Controller\Phire\Content\IndexController',
                    '/types'      => 'Phire\Controller\Phire\Content\TypesController'
                ),
                '/structure'  => array(
                    '/'           => 'Phire\Controller\Phire\Structure\IndexController',
                    '/fields'     => 'Phire\Controller\Phire\Structure\FieldsController',
                    '/groups'     => 'Phire\Controller\Phire\Structure\GroupsController',
                    '/templates'  => 'Phire\Controller\Phire\Structure\TemplatesController',
                    '/categories' => 'Phire\Controller\Phire\Structure\CategoriesController',
                    '/navigation' => 'Phire\Controller\Phire\Structure\NavigationController'
                ),
                '/extensions' => 'Phire\Controller\Phire\Extensions\IndexController',
                '/users' => array(
                    '/'         => 'Phire\Controller\Phire\User\IndexController',
                    '/roles'    => 'Phire\Controller\Phire\User\RolesController',
                    '/types'    => 'Phire\Controller\Phire\User\TypesController',
                    '/sessions' => 'Phire\Controller\Phire\User\SessionsController'
                ),
                '/config' => array(
                    '/'      => 'Phire\Controller\Phire\Config\IndexController',
                    '/sites' => 'Phire\Controller\Phire\Config\SitesController'
                )
            )
        ),
        // Main Phire Navigation
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
                    )
                )
            ),
            array(
                'name' => 'Structure',
                'href' => BASE_PATH . APP_URI . '/structure',
                'acl' => array(
                    'resource'   => 'Phire\Controller\Phire\Structure\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Fields',
                        'href' => 'fields',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\FieldsController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Field Groups',
                        'href' => 'groups',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\GroupsController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Templates',
                        'href' => 'templates',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\TemplatesController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Navigation',
                        'href' => 'navigation',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\NavigationController',
                            'permission' => 'index'
                        )
                    ),
                    array(
                        'name' => 'Categories',
                        'href' => 'categories',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Structure\CategoriesController',
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
                        'name' => 'User Roles',
                        'href' => 'roles',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\User\RolesController',
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
                    'resource'   => 'Phire\Controller\Phire\Config\IndexController',
                    'permission' => 'index'
                ),
                'children' => array(
                    array(
                        'name' => 'Sites',
                        'href' => 'sites',
                        'acl' => array(
                            'resource'   => 'Phire\Controller\Phire\Config\SitesController',
                            'permission' => 'index'
                        )
                    )
                )
            )
        ),
        // Exclude parameter for excluding user-specific resources (controllers) and permissions (actions)
        'exclude_controllers' => array(
            'Phire\Controller\IndexController',
            'Phire\Controller\Phire\IndexController',
            'Phire\Controller\Phire\Install\IndexController'
        ),
        // Exclude parameter for excluding model objects from field assignment
        'exclude_models' => array(
            'Phire\Model\Extension',
            'Phire\Model\Field',
            'Phire\Model\FieldGroup',
            'Phire\Model\FieldValue',
            'Phire\Model\Install',
            'Phire\Model\Phire',
            'Phire\Model\UserSession'
        ),
        // Encryption options for whichever encryption method you choose
        'encryptionOptions' => array(),
        // Amount of revision history to store
        'history' => 10,
        // CAPTCHA settings
        'captcha' => array(
            'expire'      => 300,
            'length'      => 4,
            'width'       => 71,
            'height'      => 26,
            'lineSpacing' => 5,
            'lineColor'   => array(175, 175, 175),
            'textColor'   => array(0, 0, 0),
            'font'        => null,
            'size'        => 0,
            'rotate'      => 0
        )
    ))
);

