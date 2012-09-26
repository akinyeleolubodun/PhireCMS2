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
 * @package    Pop_Ftp
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Ftp;

/**
 * This is the Ftp class for the Ftp component.
 *
 * @category   Pop
 * @package    Pop_Ftp
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    1.0
 */
class Ftp
{

    /**
     * FTP resource
     * @var FTP resource
     */
    protected $conn = null;

    /**
     * Constructor
     *
     * Instantiate the FTP object.
     *
     * @param  string  $ftp
     * @param  string  $user
     * @param  string  $pass
     * @param  boolean $ssl
     * @return void
     * @throws Exception
     */
    public function __construct($ftp, $user, $pass, $ssl = false)
    {
        if (!function_exists('ftp_connect')) {
            throw new Exception('Error: The FTP extension is not available.');
        } else if ($ssl) {
            if (!($this->conn = ftp_ssl_connect($ftp))) {
                throw new Exception('Error: There was an error connecting to the FTP server ' . $ftp);
            }
        } else {
            if (!($this->conn = ftp_connect($ftp))) {
                throw new Exception('Error: There was an error connecting to the FTP server ' . $ftp);
            }
        }

        if (!ftp_login($this->conn, $user, $pass)) {
            throw new Exception('Error: There was an error connecting to the FTP server ' . $ftp . ' with those credentials.');
        }
    }

    /**
     * Return current working directory.
     *
     * @return string
     */
    public function pwd()
    {
        return ftp_pwd($this->conn);
    }

    /**
     * Change directories.
     *
     * @param  string $dir
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function chdir($dir)
    {
        if (!ftp_chdir($this->conn, $dir)) {
            throw new Exception('Error: There was an error changing to the directory ' . $dir);
        }
        return $this;
    }

    /**
     * Make directory.
     *
     * @param  string $dir
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function mkdir($dir)
    {
        if (!ftp_mkdir($this->conn, $dir)) {
            throw new Exception('Error: There was an error making the directory ' . $dir);
        }
        return $this;
    }

    /**
     * Remove directory.
     *
     * @param  string $dir
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function rmdir($dir)
    {
        if (!ftp_mkdir($this->conn, $dir)) {
            throw new Exception('Error: There was an error removing the directory ' . $dir);
        }
        return $this;
    }

    /**
     * Get file.
     *
     * @param  string $local
     * @param  string $remote
     * @param  string $mode
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function get($local, $remote, $mode = FTP_BINARY)
    {
        if (!ftp_get($this->conn, $local, $remote, $mode)) {
            throw new Exception('Error: There was an error getting the file ' . $remote);
        }
        return $this;
    }

    /**
     * Put file.
     *
     * @param  string $remote
     * @param  string $local
     * @param  string $mode
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function put($remote, $local, $mode = FTP_BINARY)
    {
        if (!ftp_put($this->conn, $remote, $local, $mode)) {
            throw new Exception('Error: There was an error putting the file ' . $local);
        }
    }

    /**
     * Rename file.
     *
     * @param  string $old
     * @param  string $new
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function rename($old, $new)
    {
        if (!ftp_rename($this->conn, $old, $new)) {
            throw new Exception('Error: There was an error renaming the file ' . $old);
        }
        return $this;
    }

    /**
     * Change permissions.
     *
     * @param  string $file
     * @param  string $mode
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function chmod($file, $mode)
    {
        if (!ftp_chmod($this->conn, $mode, $file)) {
            throw new Exception('Error: There was an error changing the permission of ' . $file);
        }
        return $this;
    }

    /**
     * Delete file.
     *
     * @param  string $file
     * @throws Exception
     * @return Pop\Ftp\Ftp
     */
    public function delete($file)
    {
        if (!ftp_delete($this->conn, $file)) {
            throw new Exception('Error: There was an error removing the file ' . $file);
        }
        return $this;
    }

    /**
     * Switch the passive mode.
     *
     * @param  boolean $flag
     * @return Pop\Ftp\Ftp
     */
    public function pasv($flag = true)
    {
        ftp_pasv($this->conn, $flag);
        return $this;
    }

    /**
     * Close the FTP connection.
     *
     * @return void
     */
    public function __destruct()
    {
        ftp_close($this->conn);
    }

}
