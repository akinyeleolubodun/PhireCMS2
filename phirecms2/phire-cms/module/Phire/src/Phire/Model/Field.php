<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Mvc\Model;

class Field extends Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return \Phire\Model\Field
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);
    }

}

