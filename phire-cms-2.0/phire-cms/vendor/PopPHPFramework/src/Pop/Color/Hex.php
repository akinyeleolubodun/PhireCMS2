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
 * @package    Pop_Color
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Color;

/**
 * This is the Hex class for the Color component.
 *
 * @category   Pop
 * @package    Pop_Color
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0.2
 */
class Hex implements ColorInterface
{

    /**
     * Red value
     * @var string
     */
    protected $red = null;

    /**
     * Green value
     * @var string
     */
    protected $green = null;

    /**
     * Blue value
     * @var string
     */
    protected $blue = null;

    /**
     * Hex value
     * @var string
     */
    protected $hex = null;

    /**
     * Shorthand hex value
     * @var string
     */
    protected $shorthand = null;

    /**
     * Constructor
     *
     * Instantiate the hex color object
     *
     * @param string $hex
     * @return void
     */
    public function __construct($hex)
    {
        $hex = (substr($hex, 0, 1) == '#') ? substr($hex, 1) : $hex;

        if (strlen($hex) == 3) {
            $this->hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
            $this->shorthand = $hex;
        } else {
            $this->hex = $hex;
        }
        $this->red = substr($this->hex, 0, 2);
        $this->green = substr($this->hex, 2, 2);
        $this->blue = substr($this->hex, 4, 2);

        $dR = base_convert($this->red, 16, 10);
        $dG = base_convert($this->green, 16, 10);
        $dB = base_convert($this->blue, 16, 10);

        $max = max($dR, $dG, $dB);
        $min = min($dR, $dG, $dB);

        if (!$this->isValid()) {
            throw new Exception('One or more of the color values is out of range.');
        }

        $r = null;
        $g = null;
        $b = null;

        if (substr($this->hex, 0, 1) == substr($this->hex, 1, 1)) {
            $r = substr($this->hex, 0, 1);
        }
        if (substr($this->hex, 2, 1) == substr($this->hex, 3, 1)) {
            $g = substr($this->hex, 2, 1);
        }
        if (substr($this->hex, 4, 1) == substr($this->hex, 5, 1)) {
            $b = substr($this->hex, 4, 1);
        }

        if ((null !== $r) && (null !== $g) && (null !== $b)) {
            $this->shorthand = $r . $g . $b;
        } else {
            $this->shorthand = null;
        }
    }

    /**
     * Method to get the full RGB hex value
     *
     * @param  boolean $hash
     * @param  boolean $short
     * @return string
     */
    public function getHex($hash = false, $short = false)
    {

        $hex = null;

        if (($short) && (null !== $this->shorthand)) {
            $hex = ($hash) ? '#' . $this->shorthand : $this->shorthand;
        } else {
            $hex = ($hash) ? '#' . $this->hex : $this->hex;
        }

        return $hex;

    }

    /**
     * Method to get the red hex value
     *
     * @return int
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Method to get the green hex value
     *
     * @return int
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Method to get the blue hex value
     *
     * @return int
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Method to return the string value for printing output.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getHex(true);
    }

    /**
     * Method to determine if the hex value is valid.
     *
     * @return boolean
     */
    protected function isValid()
    {
        $valid = true;

        $hexValues = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        $hex = str_split($this->hex);

        foreach ($hex as $h) {
            if (!in_array($h, $hexValues)) {
                $valid = false;
            }
        }

        return $valid;
    }

}
