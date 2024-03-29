<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Code\Generator;

/**
 * Function generator code class
 *
 * @category   Pop
 * @package    Pop_Code
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    1.7.0
 */
class FunctionGenerator
{

    /**
     * Docblock generator object
     * @var \Pop\Code\Generator\DocblockGenerator
     */
    protected $docblock = null;

    /**
     * Function arguments
     * @var array
     */
    protected $arguments = array();

    /**
     * Function name
     * @var string
     */
    protected $name = null;

    /**
     * Function interface flag
     * @var boolean
     */
    protected $closure = false;

    /**
     * Function body
     * @var string
     */
    protected $body = null;

    /**
     * Function indent
     * @var string
     */
    protected $indent = null;

    /**
     * Function output
     * @var string
     */
    protected $output = null;

    /**
     * Constructor
     *
     * Instantiate the function generator object
     *
     * @param  string $name
     * @param  mixed  $func
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function __construct($name, $func = null)
    {
        $this->name = $name;
        if (null !== $func) {
            $this->parseFunction($func);
        }
    }

    /**
     * Static method to instantiate the function generator object and return itself
     * to facilitate chaining methods together.
     *
     * @param  string  $name
     * @param  mixed  $func
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public static function factory($name, $func = null)
    {
        return new self($name, $func);
    }

    /**
     * Set the function closure flag
     *
     * @param  boolean $closure
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function setClosure($closure = false)
    {
        $this->closure = (boolean)$closure;
        return $this;
    }

    /**
     * Get the function closure flag
     *
     * @return boolean
     */
    public function isClosure()
    {
        return $this->closure;
    }

    /**
     * Set the function description
     *
     * @param  string $desc
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function setDesc($desc = null)
    {
        if (null !== $this->docblock) {
            $this->docblock->setDesc($desc);
        } else {
            $this->docblock = new DocblockGenerator($desc, $this->indent);
        }
        return $this;
    }

    /**
     * Get the function description
     *
     * @return string
     */
    public function getDesc()
    {
        $desc = null;
        if (null !== $this->docblock) {
            $desc = $this->docblock->getDesc();
        }
        return $desc;
    }

    /**
     * Set the function indent
     *
     * @param  string $indent
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function setIndent($indent = null)
    {
        $this->indent = $indent;
        return $this;
    }

    /**
     * Get the function indent
     *
     * @return string
     */
    public function getIndent()
    {
        return $this->indent;
    }

    /**
     * Set the function name
     *
     * @param  string $name
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the function name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the function body
     *
     * @param  string $body
     * @param  boolean $newline
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function setBody($body, $newline = true)
    {
        $this->body = $this->indent . '    ' .  str_replace(PHP_EOL, PHP_EOL . $this->indent . '    ', $body);
        if ($newline) {
            $this->body .= PHP_EOL;
        }
        return $this;
    }

    /**
     * Append to the function body
     *
     * @param  string  $body
     * @param  boolean $newline
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function appendToBody($body, $newline = true)
    {
        $body = str_replace(PHP_EOL, PHP_EOL . $this->indent . '    ', $body);
        $this->body .= $this->indent . '    ' . $body;
        if ($newline) {
            $this->body .= PHP_EOL;
        }
        return $this;
    }

    /**
     * Get the function body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the docblock generator object
     *
     * @param  DocblockGenerator $docblock
     * @return \Pop\Code\Generator\ClassGenerator
     */
    public function setDocblock(DocblockGenerator $docblock)
    {
        $this->docblock = $docblock;
        return $this;
    }

    /**
     * Access the docblock generator object
     *
     * @return \Pop\Code\Generator\DocblockGenerator
     */
    public function getDocblock()
    {
        return $this->docblock;
    }

    /**
     * Add a function argument
     *
     * @param string  $name
     * @param mixed   $value
     * @param string  $type
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function addArgument($name, $value = null, $type = null)
    {
        $typeHintsNotAllowed = array(
            'int',
            'integer',
            'boolean',
            'float',
            'string',
            'mixed'
        );
        $argType = (!in_array($type, $typeHintsNotAllowed)) ? $type : null;
        $this->arguments[$name] = array('value' => $value, 'type' => $argType);
        if (null === $this->docblock) {
            $this->docblock = new DocblockGenerator(null, $this->indent);
        }
        if (null !== $type) {
            if (substr($name, 0, 1) != '$') {
                $name = '$' . $name;
            }
            $this->docblock->setParam($type, $name);
        }
        return $this;
    }

    /**
     * Add function arguments
     *
     * @param array $args
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function addArguments(array $args)
    {
        foreach ($args as $arg) {
            $value = (isset($arg['value'])) ? $arg['value'] : null;
            $type = (isset($arg['type'])) ? $arg['type'] : null;
            $this->addArgument($arg['name'], $value, $type);
        }
        return $this;
    }

    /**
     * Add a function argument (synonym method for convenience)
     *
     * @param string  $name
     * @param mixed   $value
     * @param string  $type
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function addParameter($name, $value = null, $type = null)
    {
        $this->addArgument($name, $value, $type);
        return $this;
    }

    /**
     * Add function arguments (synonym method for convenience)
     *
     * @param array $args
     * @return \Pop\Code\Generator\FunctionGenerator
     */
    public function addParameters(array $args)
    {
        $this->addArguments($args);
        return $this;
    }

    /**
     * Get a function argument
     *
     * @param  string $name
     * @return array
     */
    public function getArgument($name)
    {
        return (isset($this->arguments[$name])) ? $this->arguments[$name] : null;
    }

    /**
     * Get the function arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get the function arguments
     *
     * @return array
     */
    public function getArgumentNames()
    {
        $names = array();
        foreach ($this->arguments as $key => $value) {
            $names[] = $key;
        }
        return $names;
    }

    /**
     * Get a function argument (synonym method for convenience)
     *
     * @param  string $name
     * @return array
     */
    public function getParameter($name)
    {
        return (isset($this->arguments[$name])) ? $this->arguments[$name] : null;
    }

    /**
     * Get the function arguments (synonym method for convenience)
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->arguments;
    }

    /**
     * Get the function arguments (synonym method for convenience)
     *
     * @return array
     */
    public function getParameterNames()
    {
        return $this->getArgumentNames();
    }

    /**
     * Render method
     *
     * @param  boolean $ret
     * @return mixed
     */
    public function render($ret = false)
    {
        $args = $this->formatArguments();

        $this->output = PHP_EOL . ((null !== $this->docblock) ? $this->docblock->render(true) : null);
        if ($this->closure) {
            $this->output .= $this->indent . '$' . $this->name .' = function(' . $args . ')';
        } else {
            $this->output .= $this->indent . 'function ' . $this->name . '(' . $args . ')';
        }

        $this->output .= PHP_EOL . $this->indent . '{' . PHP_EOL;
        $this->output .= $this->body. PHP_EOL;
        $this->output .= $this->indent . '}';

        if ($this->closure) {
            $this->output .= ';';
        }

        $this->output .= PHP_EOL;

        if ($ret) {
            return $this->output;
        } else {
            echo $this->output;
        }
    }

    /**
     * Method to format the arguments
     *
     * @return string
     */
    protected function formatArguments()
    {
        $args = null;

        $i = 0;
        foreach ($this->arguments as $name => $arg) {
            $i++;
            $args .= (null !== $arg['type']) ? $arg['type'] . ' ' : null;
            $args .= (substr($name, 0, 1) != '$') ? "\$" . $name : $name;
            $args .= (null !== $arg['value']) ? " = " . $arg['value'] : null;
            if ($i < count($this->arguments)) {
                $args .= ', ';
            }
        }

        return $args;
    }


    /**
     * Method to format the arguments
     *
     * @param  mixed $func
     * @return void
     */
    protected function parseFunction($func)
    {
        $refFunc = new \ReflectionFunction($func);
        $this->closure = true;

        foreach ($refFunc->getParameters() as $key => $refParameter) {
            $this->addArgument($refParameter->getName());
        }

    }

    /**
     * Print method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
