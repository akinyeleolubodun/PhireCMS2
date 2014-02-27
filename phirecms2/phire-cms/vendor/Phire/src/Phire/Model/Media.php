<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\File\Dir;
use Phire\Table;

class Media extends AbstractModel
{

    /**
     * Image types regex
     *
     * @var   string
     */
    protected static $imageRegex = '/^.*\.(ai|eps|gif|jpe|jpg|jpeg|pdf|png|psd)$/i';

    /**
     * Method to get image regex
     *
     * @return string
     */
    public static function getImageRegex()
    {
        return self::$imageRegex;
    }

    /**
     * Static method to process uploaded media
     *
     * @param string       $fileName
     * @param \ArrayObject $config
     * @param string       $docRoot
     * @return void
     */
    public static function process($fileName, $config, $docRoot = null)
    {
        $cfg = $config->media_actions;
        $adapter = '\Pop\Image\\' . $config->media_image_adapter;
        $formats = $adapter::formats();
        $ext = strtolower(substr($fileName, (strrpos($fileName, '.') + 1)));

        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH;
        }

        if (in_array($ext, $formats)) {
            $mediaDir = $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
            foreach ($cfg as $size => $action) {
                if (in_array($action['action'], Config::getMediaActions())) {
                    // If 'size' directory does not exist, create it
                    if (!file_exists($mediaDir . DIRECTORY_SEPARATOR . $size)) {
                        mkdir($mediaDir . DIRECTORY_SEPARATOR . $size);
                        chmod($mediaDir . DIRECTORY_SEPARATOR . $size, 0777);
                        copy($mediaDir . DIRECTORY_SEPARATOR . 'index.html',
                             $mediaDir . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . 'index.html');
                        chmod($mediaDir . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . 'index.html', 0777);
                    }

                    if (!is_array($action['params'])) {
                        if (strpos(',', $action['params']) !== false) {
                            $pAry = explode(',', $action['params']);
                            $params = array();
                            foreach ($pAry as $p) {
                                $params[] = trim($p);
                            }
                        } else {
                            $params = array($action['params']);
                        }
                    } else {
                        $params = $action['params'];
                    }
                    $quality = (isset($action['quality'])) ? (int)$action['quality'] : 80;

                    // Save original image, and then save the resized image
                    $img = new $adapter($mediaDir . DIRECTORY_SEPARATOR . $fileName);
                    $img->setQuality($quality);
                    $ext = strtolower($img->getExt());
                    if (($ext == 'ai') || ($ext == 'eps') || ($ext == 'pdf') || ($ext == 'psd')) {
                        $img->flatten()->convert('jpg');
                        $newFileName = $fileName . '.jpg';
                    } else {
                        $newFileName = $fileName;
                    }
                    $img = call_user_func_array(array($img, $action['action']), $params);
                    $img->save($mediaDir . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newFileName);
                    chmod($mediaDir . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newFileName, 0777);
                }
            }
        }
    }

    /**
     * Static method to remove uploaded media
     *
     * @param string $fileName
     * @param string $docRoot
     * @return void
     */
    public static function remove($fileName, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH;
        }

        $dir = $docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
        if (file_exists($dir . $fileName) && !is_dir($dir . $fileName)) {
            unlink($dir . $fileName);
        }

        $dirs = new Dir($docRoot . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');
        foreach ($dirs->getFiles() as $size) {
            if (is_dir($dir . $size)) {
                $newFileName = $fileName . '.jpg';
                if (file_exists($dir . $size . DIRECTORY_SEPARATOR . $fileName) && !is_dir($dir . $size . DIRECTORY_SEPARATOR . $fileName)) {
                    unlink($dir . $size . DIRECTORY_SEPARATOR . $fileName);
                } else if (file_exists($dir . $size . DIRECTORY_SEPARATOR . $newFileName) && !is_dir($dir . $size . DIRECTORY_SEPARATOR . $newFileName)) {
                    unlink($dir . $size . DIRECTORY_SEPARATOR . $newFileName);
                }
            }
        }
    }

    /**
     * Static method to get a file icon
     *
     * @param string $fileName
     * @param string $docRoot
     * @return array
     */
    public static function getFileIcon($fileName, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH;
        }

        $mediaDir = $docRoot . CONTENT_PATH . '/media/';
        $iconDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/assets/img/';
        $ext = strtolower(substr($fileName, (strrpos($fileName, '.') + 1)));
        if (($ext == 'docx') || ($ext == 'pptx') || ($ext == 'xlsx')) {
            $ext = substr($ext, 0, 3);
        }

        // If the file type is an image file type
        if (preg_match(self::$imageRegex, $fileName)) {
            $ext = strtolower(substr($fileName, (strrpos($fileName, '.') + 1)));
            if (($ext == 'ai') || ($ext == 'eps') || ($ext == 'pdf') || ($ext == 'psd')) {
                $newFileName = $fileName . '.jpg';
            } else {
                $newFileName = $fileName;
            }

            // Get the icon or the image file, searching for the smallest image file
            $dirs = new Dir($docRoot . CONTENT_PATH . '/media', true);
            $fileSizes = array();
            foreach ($dirs->getFiles() as $dir) {
                if (is_dir($dir)) {
                    $f = $dir . $newFileName;
                    if (file_exists($f)) {
                        $f = str_replace('\\', '//', $f);
                        $fileSizes[filesize($f)] = substr($f, (strpos($f, '/media') + 6));
                    }
                }
            }

            // If image files are found, get smallest image file
            if (count($fileSizes) > 0) {
                ksort($fileSizes);
                $vals = array_values($fileSizes);
                $smallest = array_shift($vals);
                $fileIcon = '/media' . $smallest;
            // Else, use filetype icon
            } else if (file_exists($iconDir . 'icons/50x50/' . $ext . '.png')) {
                $fileIcon = '/assets/img/icons/50x50/' . $ext . '.png';
            // Else, use generic file icon
            } else {
                $fileIcon = '/assets/img/icons/50x50/img.png';
            }
        // Else, if file type is a file type with an available icon
        } else if (file_exists($iconDir . 'icons/50x50/' . $ext . '.png')) {
            $fileIcon = '/assets/img/icons/50x50/' . $ext . '.png';
        // Else, if file type is an audio file type with an available icon
        } else if (($ext == 'wav') || ($ext == 'aif') || ($ext == 'aiff') ||
            ($ext == 'mp3') || ($ext == 'mp2') || ($ext == 'flac') ||
            ($ext == 'wma') || ($ext == 'aac') || ($ext == 'swa')) {
            $fileIcon = '/assets/img/icons/50x50/aud.png';
        // Else, if file type is an video file type with an available icon
        } else if (($ext == '3gp') || ($ext == 'asf') || ($ext == 'avi') ||
            ($ext == 'mpg') || ($ext == 'm4v') || ($ext == 'mov') ||
            ($ext == 'mpeg') || ($ext == 'wmv')) {
            $fileIcon = '/assets/img/icons/50x50/vid.png';
        // Else, if file type is a generic image file type with an available icon
        } else if (($ext == 'bmp') || ($ext == 'ico') || ($ext == 'tiff') || ($ext == 'tif')) {
            $fileIcon = '/assets/img/icons/50x50/img.png';
        // Else, use the generic file icon
        } else {
            $fileIcon = '/assets/img/icons/50x50/file.png';
        }

        // Get file size
        if (file_exists($mediaDir . $fileName)) {
            $fileSize = filesize($mediaDir . $fileName);
            if ($fileSize > 999999) {
                $fileSize = round(($fileSize / 1000000), 2) . ' MB';
            } else if ($fileSize > 999) {
                $fileSize = round(($fileSize / 1000), 2) . ' KB';
            } else {
                $fileSize = ' &lt; 1 KB';
            }
        } else {
            $fileSize = '0 B';
        }

        return array('fileIcon' => $fileIcon, 'fileSize' => $fileSize);
    }

}

