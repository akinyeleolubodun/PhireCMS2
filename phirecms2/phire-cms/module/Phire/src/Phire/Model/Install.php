<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Filter\String;
use Phire\Table\Users;

class Install extends \Pop\Mvc\Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return self
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);
    }

    /**
     * Install config method
     *
     * @return void
     */
    public function config()
    {

    }

    /**
     * Install user method
     *
     * @return void
     */
    public function user()
    {

    }

}

