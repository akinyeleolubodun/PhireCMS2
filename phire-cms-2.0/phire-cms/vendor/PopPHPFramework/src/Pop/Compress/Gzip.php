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
 * @package    Pop_Compress
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Compress;

/**
 * This is the Gzip class for the Compress component.
 *
 * @category   Pop
 * @package    Pop_Compress
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0.2
 */
class Gzip implements CompressInterface
{

    /**
     * Static method to compress data
     *
     * @param  string $data
     * @param  int    $level
     * @param  int    $mode
     * @return mixed
     */
    public static function compress($data, $level = 9, $mode = FORCE_GZIP)
    {
        // Compress the file
        if (file_exists($data)) {
            $fullpath = realpath($data);
            $data = file_get_contents($data);

            // Create the new Gzip file resource, write data and close it
            $gzResource = fopen($fullpath . '.gz', 'w');
            fwrite($gzResource, gzencode($data, $level, $mode));
            fclose($gzResource);

            return $fullpath . '.gz';
        // Else, compress the string
        } else {
            return gzencode($data, $level, $mode);
        }
    }

    /**
     * Static method to decompress data
     *
     * @param  string $data
     * @return mixed
     */
    public static function decompress($data)
    {
        // Decompress the file
        if (file_exists($data)) {
            $gz = gzopen($data, 'r');
            $uncompressed = '';

            // Read the uncompressed data
            while (!feof($gz)) {
                $uncompressed .= gzread($gz, 4096);
            }

            // Close the Gzip compressed file and write
            // the data to the uncompressed file
            gzclose($gz);
            $newFile = (stripos($data, '.tgz') !== false)
                ? str_replace('.tgz', '.tar', $data) : str_replace('.gz', '', $data);

            file_put_contents($newFile, $uncompressed);

            return $newFile;
        // Else, decompress the string
        } else {
            return gzinflate(substr($data, 10));
        }
    }

}
