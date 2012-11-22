<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Loader
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Loader;

/**
 * This is the Autoloader class for the Loader component.
 *
 * @category   Pop
 * @package    Pop_Loader
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0.2
 */
class Autoloader
{

    /**
     * Array of available namespaces prefixes.
     * @var array
     */
    protected $prefixes = array();

    /**
     * Class map array.
     * @var array
     */
    protected $classmap = array();

    /**
     * Constructor
     *
     * Instantiate the archive object
     *
     * @param  boolean $self
     * @return void
     */
    public function __construct($self = true)
    {
        if ($self) {
            $this->register('Pop', __DIR__ . '/../../');
        }
    }

    /**
     * Static method to instantiate the autoloader object
     *
     * @param  boolean $self
     * @return Pop\Loader\Autoloader
     */
    public static function factory($self = true)
    {
        return new self($self);
    }

    /**
     * Load a class map file
     *
     * @param  string $classmap
     * @throws Exception
     * @return Pop\Loader\Autoloader
     */
    public function loadClassMap($classmap)
    {
        if (!file_exists($classmap)) {
            throw new Exception('That class map file does not exist.');
        }

        $newClassMap = include $classmap;

        if (count($this->classmap) > 0) {
            $ary = array_merge($this->classmap, $newClassMap);
        } else {
            $this->classmap = $newClassMap;
        }

        return $this;
    }

    /**
     * Register a namespace and directory location with the autoloader
     *
     * @param  string $namespace
     * @param  string $directory
     * @return Pop\Loader\Autoloader
     */
    public function register($namespace, $directory)
    {
        $this->prefixes[$namespace] = realpath($directory);
        return $this;
    }

    /**
     * Register the autoloader instance with the SPL
     *
     * @return Pop\Loader\Autoloader
     */
    public function splAutoloadRegister()
    {
        spl_autoload_register($this);
        return $this;
    }

    /**
     * Invoke the class
     *
     * @param  string $class
     * @return void
     */
    public function __invoke($class)
    {
        if (array_key_exists($class, $this->classmap)) {
            $classPath = $this->classmap[$class];
        } else {
            $sep = (strpos($class, '\\') !== false) ? '\\' : '_';
            $prefix = substr($class, 0, strpos($class, $sep));
            $classFile = str_replace($sep, DIRECTORY_SEPARATOR, $class) . '.php';
            $classPath = (isset($this->prefixes[$prefix])) ? $this->prefixes[$prefix] . DIRECTORY_SEPARATOR . $classFile : $classFile;
        }

        require_once $classPath;
    }

}
