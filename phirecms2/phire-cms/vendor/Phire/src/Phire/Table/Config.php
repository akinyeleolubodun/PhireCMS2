<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;
use Pop\Web\Server;
use Phire\Model;

class Config extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'config';

    /**
     * @var   string
     */
    protected $primaryId = 'setting';

    /**
     * @var   boolean
     */
    protected $auto = false;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Static method to get all configuration values
     *
     * @return \ArrayObject
     */
    public static function getConfig()
    {
        $cfg = static::findById('system_document_root');
        if ($cfg->value == '') {
            static::setConfig();
        }

        return static::findAll();
    }

    /**
     * Static method to get base configuration values
     *
     * @return \ArrayObject
     */
    public static function getSystemConfig()
    {
        $settings = array(
            'system_title',
            'system_email',
            'site_title',
            'separator',
            'default_language',
            'error_message',
            'datetime_format',
            'media_allowed_types',
            'media_max_filesize',
            'media_actions',
            'media_image_adapter',
            'feed_type',
            'feed_limit',
            'open_authoring',
            'incontent_editing',
            'pagination_limit',
            'pagination_range',
            'force_ssl',
            'live'
        );

        $config = array();
        $cfg = static::findAll();

        foreach ($cfg->rows as $c) {
            if (in_array($c->setting, $settings)) {
                $config[$c->setting] = (($c->setting == 'media_allowed_types') || ($c->setting == 'media_actions')) ?
                    unserialize($c->value) : $c->value;
            }
        }

        $allowedTypes = Model\Config::getMediaTypes();
        foreach ($allowedTypes as $key => $value) {
            if (!in_array($key, $config['media_allowed_types'])) {
                unset($allowedTypes[$key]);
            }
        }

        if ($config['media_max_filesize'] > 999999) {
            $maxSize = round($config['media_max_filesize'] / 1000000) . ' MB';
        } else if ($config['media_max_filesize'] > 999) {
            $maxSize = round($config['media_max_filesize'] / 1000) . ' KB';
        } else {
            $maxSize = $config['media_max_filesize'] . ' B';
        }

        $config['media_max_filesize_formatted'] = $maxSize;
        $config['media_allowed_types'] = $allowedTypes;

        return new \ArrayObject($config, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Static method to get all configuration values
     *
     * @return array
     */
    public static function setConfig()
    {
        $server = new Server();

        $cfg = static::findById('system_document_root');
        $cfg->value = $_SERVER['DOCUMENT_ROOT'];
        $cfg->update();

        $cfg = static::findById('server_operating_system');
        $cfg->value = $server->getOs() . ' (' . $server->getDistro() . ')';
        $cfg->update();

        $cfg = static::findById('server_software');
        $cfg->value = $server->getServer() . ' ' . $server->getServerVersion();
        $cfg->update();

        $cfg = static::findById('database_version');
        $cfg->value = static::getDb()->adapter()->version();
        $cfg->update();

        $cfg = static::findById('php_version');
        $cfg->value = $server->getPhp();
        $cfg->update();
    }

}

