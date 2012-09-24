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
        parent::run();

        if ($this->router()->controller()->getRequest()->getRequestUri() == '/') {
            $this->router()->controller()->dispatch();
        } else if (method_exists($this->router()->controller(), $this->router()->getAction())) {
            $this->router()->controller()->dispatch($this->router()->getAction());
        } else if (method_exists($this->router()->controller(), 'error')) {
            $this->router()->controller()->dispatch('error');
        }
    }

    /**
     * Method to check if the system is installed
     *
     * @throws Exception
     * @return boolean
     */
    public static function isInstalled()
    {
        if ((DB_INTERFACE == '') || (DB_NAME == '')) {
            throw new \Exception('The config file is not properly configured. Please check the config file or install the system.');
        }

        return true;
    }

}

