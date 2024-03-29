<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Dom
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Dom;

/**
 * Dom abstract class
 *
 * @category   Pop
 * @package    Pop_Dom
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.7.0
 */
abstract class AbstractDom
{

    /**
     * Object child nodes
     * @var array
     */
    protected $childNodes = array();

    /**
     * Indentation for formatting purposes.
     * @var string
     */
    protected $indent = null;

    /**
     * Child output
     * @var string
     */
    protected $output = null;

    /**
     * Method to return the indent.
     *
     * @return void
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Method to set the indent.
     *
     * @param  string $indent
     * @return mixed
     */
    public function setIndent($indent)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Add a child to the object.
     *
     * @param  mixed $c
     * @throws Exception
     * @return mixed
     */
    public function addChild($c)
    {
        if ($c instanceof Child) {
            $this->childNodes[] = $c;
        } else if (is_array($c)) {
            $this->childNodes[] = Child::factory($c);
        } else {
            throw new Exception('The argument passed is not valid.');
        }

        return $this;
    }

    /**
     * Add children to the object.
     *
     * @param  array $c
     * @throws Exception
     * @return mixed
     */
    public function addChildren($c)
    {
        foreach ($c as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * Get whether or not the child object has children
     *
     * @return boolean
     */
    public function hasChildren()
    {
        return (count($this->childNodes) > 0) ? true : false;
    }

    /**
     * Get the child nodes of the object.
     *
     * @param int $i
     * @return \Pop\Dom\Child
     */
    public function getChild($i)
    {
        return (isset($this->childNodes[(int)$i])) ? $this->childNodes[(int)$i] : null;
    }

    /**
     * Get the child nodes of the object.
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->childNodes;
    }

    /**
     * Remove all child nodes from the object.
     *
     * @param  int  $i
     * @return void
     */
    public function removeChild($i)
    {
        if (isset($this->childNodes[$i])) {
            unset($this->childNodes[$i]);
        }
    }

    /**
     * Remove all child nodes from the object.
     *
     * @return void
     */
    public function removeChildren()
    {
        $this->childNodes = array();
    }

}
