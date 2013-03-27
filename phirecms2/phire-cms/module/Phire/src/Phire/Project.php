<?php
/**
 * @namespace
 */
namespace Phire;

use Pop\Project\Project as P;

class Project extends P
{

    /**
     * Add any project specific code to this method for run-time use here.
     *
     * @return void
     */
    public function run()
    {
        // Set the ACL service
        $this->setService('acl', '\Pop\Auth\Acl::factory');

        // Add main user routes
        $this->router->addControllers(array(
            APP_URI  => array(
                '/'         => 'Phire\Controller\User\IndexController',
                '/roles'    => 'Phire\Controller\User\RolesController',
                '/sessions' => 'Phire\Controller\User\SessionsController',
                '/types'    => 'Phire\Controller\User\TypesController',
                '/users'    => 'Phire\Controller\User\UsersController'
            )
        ));

        // Get any other user types and declare their URI / Controller mapping
        $types = \Phire\Table\Types::findAll();

        foreach ($types->rows as $type) {
            if (($type->type != 'User')) {
                $this->router->addControllers(array(
                    '/' . strtolower($type->type) => 'Phire\Controller\\' . $type->type . '\IndexController'
                ));
            }
        }

        parent::run();
    }

}

