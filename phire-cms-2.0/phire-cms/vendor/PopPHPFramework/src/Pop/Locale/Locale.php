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
 * @package    Pop_Locale
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Locale;

use Pop\File\Dir;

/**
 * This is the Locale class for the Locale component. *
 *
 * @category   Pop
 * @package    Pop_Locale
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0.2
 */
class Locale
{

    /**
     * Default system language
     * @var string
     */
    protected $language = null;

    /**
     * Language content
     * @var array
     */
    protected $content = array('source' => array(), 'output' => array());

    /**
     * Constructor
     *
     * Instantiate the locale object.
     *
     * @param  string $lng
     * @return void
     */
    public function __construct($lng = null)
    {
        if (null !== $lng) {
            $this->language = $lng;
        } else if (defined('POP_DEFAULT_LANG')) {
            $this->language = POP_DEFAULT_LANG;
        } else {
            $this->language = 'en';
        }
        $this->loadCurrentLanguage();
    }

    /**
     * Static method to load the locale object.
     *
     * @param  string $lng
     * @return void
     */
    public static function factory($lng = null)
    {
        return new self($lng);
    }

    /**
     * Get current language setting.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Load language content from an XML file.
     *
     * @param  string $langFile
     * @throws Exception
     * @return void
     */
    public function loadFile($langFile)
    {
        if (file_exists($langFile)) {
            if (($xml =@ new \SimpleXMLElement($langFile, LIBXML_NOWARNING, true)) !== false) {
                foreach ($xml->text as $text) {
                    if (isset($text->source) && isset($text->output)) {
                        $this->content['source'][] = (string)$text->source;
                        $this->content['output'][] = (string)$text->output;
                    }
                }
            } else {
                throw new Exception('Error: There was an error processing that XML file.');
            }
        } else {
            throw new Exception('Error: The language file ' . $langFile . ' does not exist.');
        }
    }

    /**
     * Return the translated string
     *
     * @param  string $str
     * @param  string|array $params
     * @return $str
     */
    public function __($str, $params = null)
    {
        return $this->translate($str, $params);
    }

    /**
     * Echo the translated string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return void
     */
    public function _e($str, $params = null)
    {
        echo $this->translate($str, $params);
    }

    /**
     * Get languages from the XML files.
     *
     * @param  string $dir
     * @return array
     */
    public function getLanguages($dir = null)
    {
        $langsAry = array();
        $langDirectory = (null !== $dir) ? $dir : __DIR__ . '/Data';

        if (file_exists($langDirectory)) {
            $langDir = new Dir($langDirectory);
            $files = $langDir->getFiles();
            foreach ($files as $file) {
                if ($file != '__.xml') {
                    if (($xml =@ new \SimpleXMLElement($langDirectory . '/' . $file, LIBXML_NOWARNING, true)) !== false) {
                        if ((string)$xml->attributes()->name == (string)$xml->attributes()->native) {
                            $langsAry[str_replace('.xml', '', $file)] = (string)$xml->attributes()->native;
                        } else {
                            $langsAry[str_replace('.xml', '', $file)] = $xml->attributes()->native . ' (' . $xml->attributes()->name . ")";
                        }
                    }
                }
            }
        }

        ksort($langsAry);
        return $langsAry;
    }

    /**
     * Translate and return the string.
     *
     * @param  string $str
     * @param  string|array $params
     * @return $str
     */
    protected function translate($str, $params = null)
    {
        $key = array_search($str, $this->content['source']);
        $trans = ($key !== false) ? $this->content['output'][$key] : $str;

        if (null !== $params) {
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $trans = str_replace('%' . ($key + 1), $value, $trans);
                }
            } else {
                $trans = str_replace('%1', $params, $trans);
            }
        }

        return $trans;
    }

    /**
     * Get language content from the XML file.
     *
     * @return void
     */
    protected function loadCurrentLanguage()
    {
        $this->loadFile(__DIR__ . '/Data/' . $this->language . '.xml');
    }

}
