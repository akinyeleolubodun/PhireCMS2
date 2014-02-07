<?php
/**
 * @namespace
 */
namespace Phire;

class Cli
{

    /**
     * CLI arguments
     * @var array
     */
    protected $args = null;

    /**
     * Constructor method to instantiate the CLI object
     *
     * @param  array $args
     * @return self
     */
    public function __construct($args = array())
    {
        $this->args = $args;
        //print_r($this->args);

        if (!in_array('install', $this->args) && !Project::isInstalled(true)) {
            echo 'Phire CMS 2 does not appear to be installed. Please check the config file or install the application.' . PHP_EOL . PHP_EOL;
        } else {
            if (isset($this->args[1])) {
                switch ($this->args[1]) {
                    case 'help':
                        $this->showHelp();
                        break;

                    case 'config':
                        $this->showConfig();
                        break;

                    case 'version':
                        $this->showVersion();
                        break;

                    case 'user':
                        $this->user();
                        break;

                    case 'ext':
                        $this->extensions();
                        break;

                    case 'install':
                        $this->install();
                        break;
                }
            }
        }
    }

    /**
     * Show help
     *
     * @return void
     */
    protected function showHelp()
    {
        echo 'Help' . PHP_EOL;
        echo '----' . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Show config
     *
     * @return void
     */
    protected function showConfig()
    {
        echo 'Config' . PHP_EOL;
        echo '------' . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Show version
     *
     * @return void
     */
    protected function showVersion()
    {
        echo 'Version' . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * User commands
     *
     * @return void
     */
    protected function user()
    {
        echo 'Users' . PHP_EOL;
        echo '-----' . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Extension commands
     *
     * @return void
     */
    protected function extensions()
    {
        echo 'Extensions' . PHP_EOL;
        echo '----------' . PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Install command
     *
     * @return void
     */
    protected function install()
    {
        if (!is_writable(__DIR__ . '/../../../../../config.php')) {
            echo '  The configuration file is not writable. Please make it writable before continuing.' . PHP_EOL . PHP_EOL;
        } else {
            // Install config file and database
            $input = array(
                'language'            => null,
                'db_adapter'          => null,
                'db_name'             => null,
                'db_username'         => null,
                'db_password'         => null,
                'db_host'             => 'localhost',
                'db_prefix'           => 'ph_',
                'app_uri'             => '/phire',
                'content_path'        => '/phire-content',
                'password_encryption' => 4
            );

            $langs     = \Pop\I18n\I18n::getLanguages();
            $langKeys  = array_keys($langs);
            $langsList = null;

            $i = 0;
            foreach ($langs as $key => $value) {
                $num = ($i < 10) ? ' ' . $i : $i;
                $langsList .= '  ' . $num . ' : [' . $key . '] ' . $value . PHP_EOL;
                $i++;
            }

            $db = array(
                'Mysqli',
                'Pdo\Mysql',
                'Pdo\Pgsql',
                'Pdo\Sqlite',
                'Pgsql',
                'Sqlite'
            );

            echo 'Installation' . PHP_EOL;
            echo '------------' . PHP_EOL;
            echo PHP_EOL;

            echo '  Select Language:' . PHP_EOL . PHP_EOL;
            echo $langsList . PHP_EOL;
            echo PHP_EOL;

            $inputLang = -1;

            while (!isset($langKeys[$inputLang])) {
                $inputLang = self::cliInput('  Enter Language # (Enter for English): ');
                if (empty($inputLang)) {
                    $inputLang = 3;
                }
            }

            $input['language'] = $langKeys[$inputLang];

            echo PHP_EOL;

            echo '  Select DB Adapter:' . PHP_EOL . PHP_EOL;
            foreach ($db as $key => $value) {
                echo  '  ' . $key . ' : ' . $value . PHP_EOL;
            }
            echo PHP_EOL;

            $inputDb = -1;

            while (!isset($db[$inputDb])) {
                $inputDb = self::cliInput('  Enter DB Adapter #: ');
            }

            $input['db_adapter'] = $db[$inputDb];

            if (stripos($input['db_adapter'], 'sqlite') === false) {
                $input['db_name'] = self::cliInput('  DB Name: ');
                $input['db_username'] = self::cliInput('  DB Username: ');
                $input['db_password'] = self::cliInput('  DB Password: ');

                $inputHost = self::cliInput('  DB Host (Enter for \'localhost\'): ');
                $input['db_host'] = (empty($inputHost)) ? 'localhost' : $inputHost;
            }

            $inputPrefix = self::cliInput('  DB Prefix (Enter for \'ph_\'): ');
            $input['db_prefix'] = (empty($inputPrefix)) ? 'ph_' : $inputPrefix;

            $inputAppUri = self::cliInput('  Application URI (Enter for \'/phire\'): ');
            $input['app_uri'] = (empty($inputAppUri)) ? '/phire' : $inputAppUri;

            $inputContentPath = self::cliInput('  Content Path (Enter for \'/phire-content\'): ');
            $input['content_path'] = (empty($inputContentPath)) ? '/phire-content' : $inputContentPath;

            echo PHP_EOL . '  ...Checking Database...' . PHP_EOL;
            if (stripos($input['db_adapter'], 'sqlite') === false) {
                $oldError = ini_get('error_reporting');
                error_reporting(E_ERROR);

                $dbCheck = \Pop\Project\Install\Dbs::check(array(
                    'database' => $input['db_name'],
                    'username' => $input['db_username'],
                    'password' => $input['db_password'],
                    'host'     => $input['db_host'],
                    'type'     => str_replace('\\', '_', $input['db_adapter']),
                ));
                error_reporting($oldError);

                if (null != $dbCheck) {
                    echo PHP_EOL . '  ' . wordwrap($dbCheck, 70, PHP_EOL . '  ') . PHP_EOL . PHP_EOL;
                    echo '  Please try again.' . PHP_EOL . PHP_EOL;
                    exit();
                }
            }

            echo PHP_EOL . '  ...Installing Database...' . PHP_EOL;

            $install = $install = new Model\Install();
            $install->config(new \ArrayObject($input, \ArrayObject::ARRAY_AS_PROPS), realpath(__DIR__ . '/../../../../../'));

            echo '  ...Database Complete.' . PHP_EOL . PHP_EOL;

            // Install initial user
            $user = array(
                'email'    => null,
                'username' => null,
                'password' => null,
            );

            $user['email']    = self::cliInput('  User Email: ');
            $user['username']  = self::cliInput('  Username: ');
            $user['password'] = self::cliInput('  Password: ');

            echo PHP_EOL . '  ...Saving Initial User...' . PHP_EOL . PHP_EOL;

            if (stripos($input['db_adapter'], 'Pdo') !== false) {
                $dbInterface = 'Pdo';
                $dbType = substr($input['db_adapter'], strpos($input['db_adapter'], '\\') + 1);
            } else {
                $dbInterface = $input['db_adapter'];
                $dbType = null;
            }

            if (stripos($input['db_adapter'], 'sqlite') !== false) {
                $input['db_name'] = __DIR__ . '/../../../../../' . $input['content_path'] . '/.htphire.sqlite';
            }

            $db = \Pop\Db\Db::factory($dbInterface, array(
                'type'     => $dbType,
                'database' => $input['db_name'],
                'host'     => $input['db_host'],
                'username' => $input['db_username'],
                'password' => $input['db_password']
            ));

            $db->adapter()->query("INSERT INTO " . $input['db_prefix'] . "users (type_id, role_id, username, password, email, verified, failed_attempts, site_ids) VALUES (2001, 3001, '" . $user['username'] . "', '" . Model\User::encryptPassword($user['password'], 4) . "', '" . $user['email'] ."', 1, 0, '" . serialize(array(0)) . "')");
            $db->adapter()->query('UPDATE ' . $input['db_prefix'] .'content SET created_by = 1001');
            echo '  Done!' . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * Return the input from STDIN
     *
     * @param  string $msg
     * @return string
     */
    protected static function cliInput($msg = null)
    {
        echo ((null === $msg) ? I18n::factory()->__('Continue?') . ' (Y/N) ' : $msg);
        $input = null;

        while (null === $input) {
            $prompt = fopen("php://stdin", "r");
            $input = fgets($prompt);
            $input = rtrim($input);
            fclose ($prompt);
        }

        return $input;
    }

}

