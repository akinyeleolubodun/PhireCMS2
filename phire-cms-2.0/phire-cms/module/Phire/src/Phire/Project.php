<?php
/**
 * @namespace
 */
namespace Phire;

use Pop\Dir\Dir,
    Pop\Project\Project as P;

class Project extends P
{

    /**
     * Add any project specific code to this method for run-time use here.
     *
     * @return void
     */
    public function run()
    {
        parent::run();

        if ($this->router()->controller()->getRequest()->getRequestUri() == '/') {
            $this->router()->controller()->dispatch();
        } else if (method_exists($this->router()->controller(), $this->router()->getAction())) {
            $this->router()->controller()->dispatch($this->router()->getAction());
        } else if (method_exists($this->router()->controller(), 'error')) {
            $this->router()->controller()->dispatch('error');
        }
    }

    /**
     * Method to check if the system is installed
     *
     * @throws Exception
     * @return boolean
     */
    public static function isInstalled()
    {
        if (($_SERVER['REQUEST_URI'] != BASE_URI . SYSTEM_URI . '/install') &&
           ((DB_INTERFACE == '') || (DB_NAME == ''))) {
            throw new \Exception('The config file is not properly configured. Please check the config file or install the system.');
        }

        return true;
    }
    
    /**
     * Determine whether or not the necessary system directories are writable or not.
     *
     * @param  boolean $msgs
     * @return boolean|array
     */
    public static function checkDirs($contentDir, $msgs = false)
    {
        $dir = new Dir($contentDir, true, true);
        $errorMsgs = array();

        // Check if the necessary directories are writable for Windows.
        if (stripos(PHP_OS, 'win') !== false) {
            touch($contentDir . '/writetest.txt');
            clearstatcache();
            if (!file_exists($contentDir . '/writetest.txt')) {
                $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $contentDir) . " is not writable.";
            } else {
                unlink($contentDir . '/writetest.txt');
            }
            foreach ($dir->files as $value) {
                if (is_dir($value)) {
                    touch($value . '/writetest.txt');
                    clearstatcache();
                    if (!file_exists($value . '/writetest.txt')) {
                        $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $value) . " is not writable.";
                    } else {
                        unlink($value . '/writetest.txt');
                    }
                }
            }
        // Check if the necessary directories are writable for Unix/Linux.
        } else {
            clearstatcache();
            if (!is_writable($contentDir)) {
                $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $contentDir) . " is not writable.";
            }
            foreach ($dir->files as $value) {
                if (is_dir($value)) {
                    clearstatcache();
                    if (!is_writable($value)) {
                        $errorMsgs[] = "The directory " . str_replace($_SERVER['DOCUMENT_ROOT'], '', $value) . " is not writable.";
                    }
                }
            }
        }

        // If the messaging flag was passed, return any
        // error messages, else return true/false.
        if ($msgs) {
            return $errorMsgs;
        } else {
            return (count($errorMsgs) == 0) ? true : false;
        }

    }
}

