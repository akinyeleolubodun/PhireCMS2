<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Form\Element;

/**
 * Checkbox form element class
 *
 * @category   Pop
 * @package    Pop_Form
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.7.0
 */
class Checkbox extends \Pop\Form\Element
{

    /**
     * Constructor
     *
     * Instantiate the checkbox form element object.
     *
     * @param  string $name
     * @param  string|array $value
     * @param  string|array $marked
     * @param  string $indent
     * @return \Pop\Form\Element\Checkbox
     */
    public function __construct($name, $value = null, $marked = null, $indent = null)
    {
        $this->value = $value;
        $this->setMarked($marked);

        parent::__construct('checkbox', $name, $value, $marked, $indent);
    }

}
