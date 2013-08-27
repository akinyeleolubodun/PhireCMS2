<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Archive\Archive;
use Pop\File\Dir;
use Pop\Nav\Nav;
use Pop\Project\Install\Dbs;
use Phire\Table;

class Extension extends AbstractModel
{

    /**
     * Get all themes method
     *
     * @return void
     */
    public function getThemes()
    {
        $themes = Table\Extensions::findAll('id ASC', array('type' => 0));
        $themeRows = $themes->rows;

        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes', false, false, false);
        $themeFiles = array();
        foreach ($dir->getFiles() as $file) {
            if ($file != 'index.html') {
                $themeFiles[substr($file, 0, strpos($file, '.'))] = $file;
            }
        }

        foreach ($themeRows as $theme) {
            if (isset($themeFiles[$theme->name])) {
                unset($themeFiles[$theme->name]);
            }
        }

        $this->data['themes'] = $themeRows;
        $this->data['new'] = $themeFiles;
    }

    /**
     * Get all modules method
     *
     * @param  \Phire\Project $project
     * @return void
     */
    public function getModules(\Phire\Project $project)
    {
        $modules = Table\Extensions::findAll('id ASC', array('type' => 1));
        $moduleRows = $modules->rows;

        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules', false, false, false);
        $moduleFiles = array();
        foreach ($dir->getFiles() as $file) {
            if ($file != 'index.html') {
                $moduleFiles[substr($file, 0, strpos($file, '.'))] = $file;
            }
        }

        foreach ($moduleRows as $key => $module) {
            $cfg = $project->module($module->name);
            if ((null !== $cfg) && (null !== $cfg->module_nav)) {
                $moduleRows[$key]->module_nav = new Nav($cfg->module_nav->asArray());
            }
            if (isset($moduleFiles[$module->name])) {
                unset($moduleFiles[$module->name]);
            }
        }

        $this->data['modules'] = $moduleRows;
        $this->data['new'] = $moduleFiles;
    }

    /**
     * Install themes method
     *
     * @return void
     */
    public function installThemes()
    {

    }

    /**
     * Install modules method
     *
     * @return void
     */
    public function installModules()
    {
        foreach ($this->data['new'] as $name => $module) {
            $archive = new Archive($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $module);
            $archive->extract($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/');
            if ((stripos($module, 'gz') || stripos($module, 'bz')) && (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $name . '.tar'))) {
                unlink($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $name . '.tar');
            }
            echo 'Extracting...' . $module . '<br />' . PHP_EOL;
            $dbType =  Table\Extensions::getSql()->getDbType();
            if ($dbType == \Pop\Db\Sql::SQLITE) {
                $type = 'sqlite';
            } else if ($dbType == \Pop\Db\Sql::PGSQL) {
                $type = 'pgsql';
            } else {
                $type = 'mysql';
            }

            $sqlFile = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' .
                $name . '/data/' . strtolower($name) . '.' . $type . '.sql';

            $tables = array();
            if (file_exists($sqlFile)) {
                // Get any tables required and created by this module
                $sql = file_get_contents($sqlFile);
                $tables = array();
                $matches = array();
                preg_match_all('/^CREATE TABLE(.*)$/mi', $sql, $matches);

                if (isset($matches[0]) && isset($matches[0][0])) {
                    foreach ($matches[0] as $table) {
                        if (strpos($table, '`') !== false) {
                            $table = substr($table, (strpos($table, '`') + 1));
                            $table = substr($table, 0, strpos($table, '`'));
                        } else if (strpos($table, '"') !== false) {
                            $table = substr($table, (strpos($table, '"') + 1));
                            $table = substr($table, 0, strpos($table, '"'));
                        } else if (strpos($table, "'") !== false) {
                            $table = substr($table, (strpos($table, "'") + 1));
                            $table = substr($table, 0, strpos($table, "'"));
                        } else {
                            if (stripos($table, 'EXISTS') !== false) {
                                $table = substr($table, (stripos($table, 'EXISTS') + 6));
                            } else {
                                $table = substr($table, (stripos($table, 'TABLE') + 5));
                            }
                            if (strpos($table, '(') !== false) {
                                $table = substr($table, 0, strpos($table, '('));
                            }
                            $table = trim($table);
                        }
                        $tables[] = str_replace('[{prefix}]', DB_PREFIX, $table);
                    }
                }

                // If DB is SQLite
                if (strpos($type, 'Sqlite') !== false) {
                    $dbName = realpath($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/.htphire.sqlite');
                    $dbUser = null;
                    $dbPassword = null;
                    $dbHost = null;
                    $installFile = $dbName;
                } else {
                    $dbName = DB_NAME;
                    $dbUser = DB_USER;
                    $dbPassword = DB_PASS;
                    $dbHost = DB_HOST;
                    $installFile = null;
                }

                $db = array(
                    'database' => $dbName,
                    'username' => $dbUser,
                    'password' => $dbPassword,
                    'host'     => $dbHost,
                    'prefix'   => DB_PREFIX,
                    'type'     => (DB_INTERFACE == 'Pdo') ? 'Pdo_' . ucfirst(DB_TYPE) : DB_INTERFACE
                );

                Dbs::install($dbName, $db, $sqlFile, $installFile, true, false);
            }

            $ext = new Table\Extensions(array(
                'name'   => $name,
                'type'   => 1,
                'active' => 1,
                'assets' => serialize(array('tables' => $tables))
            ));
            $ext->save();
        }
    }

    /**
     * Process themes method
     *
     * @param  array $post
     * @return void
     */
    public function processThemes($post)
    {

    }

    /**
     * Process themes method
     *
     * @param  array $post
     * @return void
     */
    public function processModules($post)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'module_active_') !== false) {
                $id = substr($key, (strrpos($key, '_') + 1));
                $ext = Table\Extensions::findById($id);
                if (isset($ext->id)) {
                    $ext->active = (int)$value;
                    $ext->save();
                }
            }
        }

        if (isset($post['remove_modules'])) {
            foreach ($post['remove_modules'] as $id) {
                $ext = Table\Extensions::findById($id);
                if (isset($ext->id)) {
                    $assets = unserialize($ext->assets);
                    if (count($assets['tables']) > 0) {
                        $db = Table\Extensions::getDb();
                        if ((DB_INTERFACE == 'Mysqli') || (DB_TYPE == 'mysql')) {
                            $db->adapter()->query('SET foreign_key_checks = 0;');
                        }
                        foreach ($assets['tables'] as $table) {
                            $db->adapter()->query('DROP TABLE ' . $table);
                        }
                        if ((DB_INTERFACE == 'Mysqli') || (DB_TYPE == 'mysql')) {
                            $db->adapter()->query('SET foreign_key_checks = 1;');
                        }
                    }
                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $ext->name)) {
                        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules/' . $ext->name);
                        $dir->emptyDir(null, true);
                    }
                    $ext->delete();
                }
            }
        }
    }

}

