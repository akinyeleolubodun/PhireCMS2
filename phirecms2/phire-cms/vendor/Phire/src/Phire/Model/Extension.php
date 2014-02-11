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

        $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes';

        $dir = new Dir($themePath, false, false, false);
        $themeFiles = array();

        $formats = Archive::formats();
        foreach ($dir->getFiles() as $file) {
            if (array_key_exists(substr($file, strrpos($file, '.') + 1), $formats)) {
                $themeFiles[substr($file, 0, strpos($file, '.'))] = $file;
            }
        }

        foreach ($themeRows as $key => $theme) {
            if (file_exists($themePath . '/' . $theme->name . '/screenshot.jpg')) {
                $themeRows[$key]->screenshot = '<img class="theme-screenshot" src="' . BASE_PATH . CONTENT_PATH . '/extensions/themes/' . $theme->name . '/screenshot.jpg" width="100" />';
            } else if (file_exists($themePath . '/' . $theme->name . '/screenshot.png')) {
                $themeRows[$key]->screenshot = '<img class="theme-screenshot" src="' . BASE_PATH . CONTENT_PATH . '/extensions/themes/' . $theme->name . '/screenshot.png" width="100" />';
            } else {
                $themeRows[$key]->screenshot = null;
            }

            if (isset($themeFiles[$theme->name])) {
                unset($themeFiles[$theme->name]);
            }

            // Get theme info
            $assets = unserialize($theme->assets);
            $themeRows[$key]->author  = '';
            $themeRows[$key]->desc    = '';
            $themeRows[$key]->version = '';

            foreach ($assets['info'] as $k => $v) {
                if (stripos($k, 'name') !== false) {
                    $themeRows[$key]->name = $v;
                } else if (stripos($k, 'author') !== false) {
                    $themeRows[$key]->author = $v;
                } else if (stripos($k, 'desc') !== false) {
                    $themeRows[$key]->desc = $v;
                } else if (stripos($k, 'version') !== false) {
                    $themeRows[$key]->version = $v;
                }
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

        $moduleDir1 = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules', false, false, false);
        $moduleDir2 = new Dir(__DIR__ . '/../../../../../module', false, false, false);

        $dirs = array_merge($moduleDir1->getFiles(), $moduleDir2->getFiles());
        $moduleFiles = array();

        $formats = Archive::formats();
        foreach ($dirs as $file) {
            if (array_key_exists(substr($file, strrpos($file, '.') + 1), $formats)) {
                $moduleFiles[substr($file, 0, strpos($file, '.'))] = $file;
            }
        }

        foreach ($moduleRows as $key => $module) {
            $cfg = $project->module($module->name);
            if ((null !== $cfg) && (null !== $cfg->module_nav)) {
                $n = (!is_array($cfg->module_nav)) ? $cfg->module_nav->asArray() : $cfg->module_nav;
                $modNav = new Nav($n, array(
                    'top' => array(
                        'id'    => strtolower($module->name) . '-nav',
                        'class' => 'module-nav'
                    ))
                );
                $modNav->setAcl($this->data['acl']);
                $modNav->setRole($this->data['role']);
                $moduleRows[$key]->module_nav = $modNav;
            }
            if (isset($moduleFiles[$module->name])) {
                unset($moduleFiles[$module->name]);
            }

            // Get module info
            $assets = unserialize($module->assets);
            $moduleRows[$key]->author  = '';
            $moduleRows[$key]->desc    = '';
            $moduleRows[$key]->version = '';

            foreach ($assets['info'] as $k => $v) {
                if (stripos($k, 'name') !== false) {
                    $moduleRows[$key]->name = $v;
                } else if (stripos($k, 'author') !== false) {
                    $moduleRows[$key]->author = $v;
                } else if (stripos($k, 'desc') !== false) {
                    $moduleRows[$key]->desc = $v;
                } else if (stripos($k, 'version') !== false) {
                    $moduleRows[$key]->version = $v;
                }
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
        $docRoots = array();

        $sites = Table\Sites::findAll();
        foreach ($sites->rows as $site) {
            $docRoots[] = $site->document_root;
        }

        try {
            $themePath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes';
            if (!is_writable($themePath)) {
                throw new \Phire\Exception($this->i18n->__('The themes folder is not writable.'));
            }

            $exts = Table\Extensions::findAll(null, array('active' => 1));
            foreach ($exts->rows as $ext) {
                $e = Table\Extensions::findById($ext->id);
                if (isset($e->id) && ($ext->type == 0)) {
                    $e->active = 0;
                    $e->update();
                }
            }

            $last = null;
            foreach ($this->data['new'] as $name => $theme) {
                $archive = new Archive($themePath . '/' . $theme);
                $archive->extract($themePath . '/');
                if ((stripos($theme, 'gz') || stripos($theme, 'bz')) && (file_exists($themePath . '/' . $name . '.tar'))) {
                    unlink($themePath . '/' . $name . '.tar');
                }

                $templates = array();

                $dir = new Dir($themePath . '/' . $name);
                foreach ($dir->getFiles() as $file) {
                    if (stripos($file, '.html') !== false) {
                        $tmpl = file_get_contents($themePath . '/' . $name . '/' . $file);
                        $tmplName = ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($file, 0, strrpos($file, '.'))));
                        $t = new Table\Templates(array(
                            'name'         => $tmplName,
                            'content_type' => 'text/html',
                            'device'       => 'desktop',
                            'template'     => $tmpl
                        ));
                        $t->save();
                        $templates['template_' . $t->id] = $tmplName;
                    } else if ((stripos($file, '.phtml') !== false) || (stripos($file, '.php') !== false) || (stripos($file, '.php3') !== false)) {
                        $templates[] = $file;
                    }
                }

                $style = null;
                $info = array();

                // Check for a style sheet
                if (file_exists($themePath . '/' . $name . '/style.css')) {
                    $style = $themePath . '/' . $name . '/style.css';
                } else if (file_exists($themePath . '/' . $name . '/styles.css')) {
                    $style = $themePath . '/' . $name . '/styles.css';
                } else if (file_exists($themePath . '/' . $name . '/css/style.css')) {
                    $style = $themePath . '/' . $name . '/css/style.css';
                } else if (file_exists($themePath . '/' . $name . '/css/styles.css')) {
                    $style = $themePath . '/' . $name . '/css/styles.css';
                }

                // Try and get theme info from style sheet
                if (null !== $style) {
                    $css = file_get_contents($style);
                    if (strpos($css, '*/') !== false) {
                        $cssHeader = substr($css, 0, strpos($css, '*/'));
                        $cssHeader = substr($cssHeader, (strpos($cssHeader, '/*') + 2));
                        $cssHeaderAry = explode("\n", $cssHeader);
                        foreach ($cssHeaderAry as $line) {
                            if (strpos($line, ':')) {
                                $ary = explode(':', $line);
                                if (isset($ary[0]) && isset($ary[1])) {
                                    $key = trim(str_replace('*', '', $ary[0]));
                                    $value = trim(str_replace('*', '', $ary[1]));
                                    $info[$key] = $value;
                                }
                            }
                        }
                    }
                }

                $ext = new Table\Extensions(array(
                    'name'   => $name,
                    'file'   => $theme,
                    'type'   => 0,
                    'active' => 0,
                    'assets' => serialize(array(
                        'templates' => $templates,
                        'info'      => $info
                    ))
                ));
                $ext->save();

                foreach ($docRoots as $docRoot) {
                    $altThemePath = $docRoot . BASE_PATH . CONTENT_PATH . '/extensions/themes';
                    copy($themePath . '/' . $theme, $altThemePath . '/' . $theme);
                    $archive = new Archive($altThemePath . '/' . $theme);
                    $archive->extract($altThemePath . '/');
                    if ((stripos($theme, 'gz') || stripos($theme, 'bz')) && (file_exists($altThemePath . '/' . $name . '.tar'))) {
                        unlink($altThemePath . '/' . $name . '.tar');
                    }
                }
            }
            if (isset($ext->id)) {
                $ext->active = 1;
                $ext->update();
            }
        } catch (\Exception $e) {
            $this->data['error'] = $e->getMessage();
        }
    }

    /**
     * Install modules method
     *
     * @throws \Phire\Exception
     * @return void
     */
    public function installModules()
    {
        try {
            $modulePath1 = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/modules';
            $modulePath2 = __DIR__ . '/../../../../../module';

            foreach ($this->data['new'] as $name => $module) {
                $modPath = (file_exists($modulePath1 . '/' . $module)) ? $modulePath1 : $modulePath2;

                if (!is_writable($modPath)) {
                    throw new \Phire\Exception($this->i18n->__('The modules folder is not writable.'));
                }

                $archive = new Archive($modPath . '/' . $module);
                $archive->extract($modPath . '/');
                if ((stripos($module, 'gz') || stripos($module, 'bz')) && (file_exists($modPath . '/' . $name . '.tar'))) {
                    unlink($modPath . '/' . $name . '.tar');
                }

                $dbType =  Table\Extensions::getSql()->getDbType();
                if ($dbType == \Pop\Db\Sql::SQLITE) {
                    $type = 'sqlite';
                } else if ($dbType == \Pop\Db\Sql::PGSQL) {
                    $type = 'pgsql';
                } else {
                    $type = 'mysql';
                }

                $sqlFile = $modPath . '/' .
                    $name . '/data/' . strtolower($name) . '.' . $type . '.sql';

                $tables = array();
                $info = array();

                // Check for a config and try to get info out of it
                if (file_exists($modPath . '/' . $name . '/config') && file_exists($modPath . '/' . $name . '/config/module.php')) {
                    $cfg = file_get_contents($modPath . '/' . $name . '/config/module.php');
                    if (strpos($cfg, '*/') !== false) {
                        $cfgHeader = substr($cfg, 0, strpos($cfg, '*/'));
                        $cfgHeader = substr($cfgHeader, (strpos($cfgHeader, '/*') + 2));
                        $cfgHeaderAry = explode("\n", $cfgHeader);
                        foreach ($cfgHeaderAry as $line) {
                            if (strpos($line, ':')) {
                                $ary = explode(':', $line);
                                if (isset($ary[0]) && isset($ary[1])) {
                                    $key = trim(str_replace('*', '', $ary[0]));
                                    $value = trim(str_replace('*', '', $ary[1]));
                                    $info[$key] = $value;
                                }
                            }
                        }
                    }
                }

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

                    $ext = new Table\Extensions(array(
                        'name'   => $name,
                        'file'   => $module,
                        'type'   => 1,
                        'active' => 1,
                        'assets' => serialize(array(
                            'tables' => $tables,
                            'info'   => $info
                        ))
                    ));
                    $ext->save();

                    // If DB is SQLite
                    if (stripos($type, 'Sqlite') !== false) {
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
                } else {
                    $ext = new Table\Extensions(array(
                        'name'   => $name,
                        'type'   => 1,
                        'active' => 1,
                        'assets' => serialize(array(
                            'tables' => $tables,
                            'info'   => $info
                        ))
                    ));
                    $ext->save();
                }
            }
        } catch (\Exception $e) {
            $this->data['error'] = $e->getMessage();
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
        $sql = Table\Extensions::getSql();

        $sql->update(array(
            'active' => 0
        ))->where()->equalTo('type', 0);

        Table\Extensions::execute($sql->render(true));

        $ext = Table\Extensions::findById($post['theme_active']);
        $ext->active = 1;
        $ext->save();

        $active = false;
        if (isset($post['remove_themes'])) {
            $docRoots = array($_SERVER['DOCUMENT_ROOT']);

            $sites = Table\Sites::findAll();
            foreach ($sites->rows as $site) {
                $docRoots[] = $site->document_root;
            }

            foreach ($post['remove_themes'] as $id) {
                $ext = Table\Extensions::findById($id);

                if (isset($ext->id)) {
                    if ($ext->active) {
                        $active = true;
                    }

                    $assets = unserialize($ext->assets);
                    $tmpls = array();

                    foreach ($assets['templates'] as $key => $value) {
                        if (strpos($key, 'template_') !== false) {
                            $tmpls[] = substr($key, (strpos($key, '_') + 1));
                        }
                    }

                    if (count($tmpls) > 0) {
                        foreach ($tmpls as $tId) {
                            $t = Table\Templates::findById($tId);
                            if (isset($t->id)) {
                                $t->delete();
                            }
                        }
                    }

                    foreach ($docRoots as $docRoot) {
                        $contentPath = $docRoot . BASE_PATH . CONTENT_PATH;
                        $exts = array('.zip', '.tar.gz', '.tar.bz2', '.tgz', '.tbz', '.tbz2');

                        if (file_exists($contentPath . '/extensions/themes/' . $ext->name)) {
                            $dir = new Dir($contentPath . '/extensions/themes/' . $ext->name);
                            $dir->emptyDir(null, true);
                        }

                        foreach ($exts as $e) {
                            if (file_exists($contentPath . '/extensions/themes/' . $ext->name . $e) &&
                                is_writable($contentPath . '/extensions/themes/' . $ext->name . $e)) {
                                unlink($contentPath . '/extensions/themes/' . $ext->name . $e);
                            }
                        }
                    }

                    $ext->delete();
                }
            }
        }

        if ($active) {
            $themes = Table\Extensions::findAll('id ASC', array('type' => 0));
            if (isset($themes->rows[0])) {
                $theme = Table\Extensions::findById($themes->rows[0]->id);
                $theme->active = 1;
                $theme->save();
            }
        }
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
                            foreach ($assets['tables'] as $table) {
                                $db->adapter()->query('DROP TABLE ' . $table);
                            }
                            $db->adapter()->query('SET foreign_key_checks = 1;');
                        } else if ((DB_INTERFACE == 'Pgsql') || (DB_TYPE == 'pgsql')) {
                            foreach ($assets['tables'] as $table) {
                                $db->adapter()->query('DROP TABLE ' . $table . ' CASCADE');
                            }
                        } else {
                            foreach ($assets['tables'] as $table) {
                                $db->adapter()->query('DROP TABLE ' . $table);
                            }
                        }
                    }

                    $contentPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH;
                    $exts = array('.zip', '.tar.gz', '.tar.bz2', '.tgz', '.tbz', '.tbz2');

                    if (file_exists($contentPath . '/extensions/modules/' . $ext->name)) {
                        $dir = new Dir($contentPath . '/extensions/modules/' . $ext->name);
                        $dir->emptyDir(null, true);
                    }

                    foreach ($exts as $e) {
                        if (file_exists($contentPath . '/extensions/modules/' . $ext->name . $e) &&
                            is_writable($contentPath . '/extensions/modules/' . $ext->name . $e)) {
                            unlink($contentPath . '/extensions/modules/' . $ext->name . $e);
                        }
                    }

                    if (file_exists(__DIR__ . '/../../../../../module/' . $ext->name)) {
                        $dir = new Dir(__DIR__ . '/../../../../../module/' . $ext->name);
                        $dir->emptyDir(null, true);
                    }

                    foreach ($exts as $e) {
                        if (file_exists(__DIR__ . '/../../../../../module/' . $ext->name . $e) &&
                            is_writable(__DIR__ . '/../../../../../module/' . $ext->name . $e)) {
                            unlink(__DIR__ . '/../../../../../module/' . $ext->name . $e);
                        }
                    }

                    if (file_exists($contentPath . '/assets/' . strtolower($ext->name))) {
                        $dir = new Dir($contentPath . '/assets/' . strtolower($ext->name));
                        $dir->emptyDir(null, true);
                    }
                    $ext->delete();
                }
            }
        }
    }

}

