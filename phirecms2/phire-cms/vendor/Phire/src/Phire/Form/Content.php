<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\File\Dir;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;
use Phire\Model;
use Phire\Table;

class Content extends Form
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string         $action
     * @param  string         $method
     * @param  int            $tid
     * @param  int            $mid
     * @param  array          $cfg
     * @param \Phire\Auth\Acl $acl
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $mid = 0, $cfg = array(), $acl = null)
    {
        // Generate fields for content type select first
        if ($tid == 0) {
            $typesAry = array();
            $types = Table\ContentTypes::findAll('order ASC');
            foreach ($types->rows as $type) {
                if ($acl->isAuth('Phire\Controller\Phire\Content\IndexController', 'add_' . $type->id)) {
                    $typesAry[$type->id] = $type->name;
                }
            }

            $this->initFieldsValues = array(
                'type_id' => array(
                    'type'     => 'select',
                    'required' => true,
                    'label'    => 'Select Content Type:',
                    'value'    => $typesAry
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'value' => 'SELECT',
                    'attributes' => array(
                        'class' => 'save-btn',
                        'style' => 'padding: 5px 6px 6px 6px; width: 100px;'
                    )
                )
            );
            $id = 'content-select-form';
        // Else, generate fields for the content object
        } else {
            $this->initFieldsValues = $this->getInitFields($tid, $mid, $cfg);
            $id = 'content-form';
        }

        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', $id);
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  mixed $filters
     * @param  mixed $params
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $params = null)
    {
        parent::setFieldValues($values, $filters, $params);

        // Add validators for checking dupe uris
        if (($_POST) && isset($_POST['id'])) {
            $type = Table\ContentTypes::findById($_POST['type_id']);
            if ($type->uri) {
                $uri = Table\Content::findBy(array('slug' => $this->uri));
                if (($type->uri) && (isset($uri->id)) && ((int)$this->parent_id == (int)$uri->parent_id) &&
                    ($this->id != $uri->id) && ($this->site_id == $uri->site_id)) {
                    if ($this->uri == '') {
                        $this->getElement('uri')
                             ->addValidator(new Validator\NotEmpty($this->uri, 'The root URI already exists.'));
                    } else {
                        $this->getElement('uri')
                             ->addValidator(new Validator\NotEqual($this->uri, 'That URI already exists under that parent content.'));
                    }
                }
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if (($_FILES) && isset($_FILES[$key]) && ($_FILES[$key]['error'] == 1)) {
                    $this->getElement($key)
                         ->addValidator(new Validator\LessThanEqual(-1, "The 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . " exceeded."));
                } else if ($value['error'] != 4) {
                    if ($value['size'] > $config->media_max_filesize) {
                        $this->getElement($key)
                             ->addValidator(new Validator\LessThanEqual($config->media_max_filesize, 'The file must be less than ' . $config->media_max_filesize_formatted . '.'));
                    }
                    if (preg_match($regex, $value['name']) == 0) {
                        $type = strtoupper(substr($value['name'], (strrpos($value['name'], '.') + 1)));
                        $this->getElement($key)
                             ->addValidator(new Validator\NotEqual($value['name'], 'The ' . $type . ' file type is not allowed.'));
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  int     $mid
     * @param  array   $cfg
     * @return array
     */
    protected function getInitFields($tid = 0, $mid = 0, $cfg = array())
    {
        // Get types and type object
        $typesAry = array();
        $types = Table\ContentTypes::findAll(null, array('uri' => 1));
        foreach ($types->rows as $type) {
            $typesAry[] = $type->id;
        }

        $type = Table\ContentTypes::findById($tid);

        // Get parents and children, if applicable
        $parents = array(0 => '----');

        // Prevent the object's children or itself from being in the parent drop down
        $content = Table\Content::findAll('id ASC');
        foreach ($content->rows as $c) {
            if (($c->parent_id == 0) && ($c->id != $mid) && (null !== $c->status)) {
                $parents[$c->id] = $c->title;
                $children = $this->children($c->id);
                if (count($children) > 0) {
                    foreach ($children as $cid => $child) {
                        if ($cid != $mid) {
                            $parents[$cid] = $child;
                        }
                    }
                }
            }
        }

        // Get categories
        $categories = new Model\Category();
        $categories->getAll();
        $categoryAry = $categories->getCategoryArray();
        unset($categoryAry[0]);

        // If type requires a URI
        if ($type->uri == 1) {
            $fields1 = array();

            $uri = array(
                'type'       => 'text',
                'label'      => 'URI:' . (($mid != 0) ? ' <a class="small-link" href="#" onclick="phire.slug(\'content_title\', \'uri\'); return false;">[ Generate URI ]</a>': null),
                'attributes' => array(
                    'size'    => 80,
                    'onkeyup' => "phire.slug(null, 'uri');"
                )
            );
            $titleAttributes = array(
                'size'    => 80,
                'style'   => 'display: block;'
            );
            if ($mid == 0) {
                $titleAttributes['onkeyup'] = "phire.slug('content_title', 'uri');";
            }
        // Else, if type is a file
        } else {
            $this->hasFile = true;
            $label = 'File:';
            $required = true;
            if ($mid != 0) {
                $content = Table\Content::findById($mid);
                if (isset($content->id)) {
                    $site = Table\Sites::getSite((int)$content->site_id);
                    $fileInfo = Model\Content::getFileIcon($content->uri, $site->document_root . $site->base_path);
                    $label = '<em>Replace?</em><br /><a href="http://' .
                        $site->domain . BASE_PATH . CONTENT_PATH . '/media/' . $content->uri . '" target="_blank"><img id="current-file" style="padding-top: 3px;" src="http://' .
                        $site->domain . BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="http://' . $site->domain . BASE_PATH . CONTENT_PATH . '/media/' . $content->uri . '" target="_blank">' .
                        $content->uri . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span>';
                    $required = false;
                }
            }
            $uri = array(
                'type'       => 'file',
                'label'      => $label,
                'required'   => $required,
                'attributes' => array('size' => 80)
            );
            $titleAttributes = array(
                'size'    => 80
            );
        }

        $fields1['content_title'] = array(
            'type'       => 'text',
            'label'      => 'Title:',
            'required'   => (!$this->hasFile),
            'attributes' => $titleAttributes
        );

        $fields1['uri'] =  $uri;

        $sess = \Pop\Web\Session::getInstance();
        $siteIds = array(0 => $_SERVER['HTTP_HOST']);

        $sites = Table\Sites::findAll();
        foreach ($sites->rows as $site) {
            if (in_array($site->id, $sess->user->site_ids)) {
                $siteIds[$site->id] = $site->domain;
            }
        }

        $fields4 = array(
            'site_id' => array(
                'type'       => 'select',
                'label'      => 'Site:',
                'value'      => $siteIds,
                'marked'     => 0,
                'attributes' => array('style' => 'min-width: 200px;')
            )
        );

        // If type requires a URI
        if ($type->uri == 1) {
            $fields4['parent_id'] = array(
                'type'       => 'select',
                'label'      => 'Parent:',
                'value'      => $parents,
                'attributes' => array(
                    'onchange' => "phire.slug(null, 'uri');",
                    'style'    => 'min-width: 200px;'
                )
            );
            $fields4['template'] = array(
                'type'       => 'select',
                'label'      => 'Template:',
                'value'      => $this->getTemplates($cfg),
                'attributes' => array(
                    'style'    => 'min-width: 200px;'
                )
            );
        }

        // Add nav include and roles
        if (!$this->hasFile) {
            $fields4['status'] = array(
                'type'   => 'select',
                'label'  => 'Status:',
                'value'  => array(
                    0 => 'Unpublished',
                    1 => 'Draft',
                    2 => 'Published'
                ),
                'marked'     => 0,
                'attributes' => array('style' => 'min-width: 200px;')
            );

            $navOrder = array();
            $navsMarked = array();
            if ($mid != 0) {
                $navs = Table\NavigationTree::findAll(null, array('content_id' => $mid));
                if (isset($navs->rows[0])) {
                    foreach ($navs->rows as $nav) {
                        $navsMarked[] = $nav->navigation_id;
                        $navOrder[$nav->navigation_id] = $nav->order;
                    }
                }
            }
            $navsAry = array();
            $navs = \Phire\Table\Navigation::findAll('id ASC');
            foreach ($navs->rows as $nav) {
                $navsAry[$nav->id] = '<strong style="display: block; float: left; width: 90px; font-size: 0.9em;">' . $nav->navigation . '</strong> <input style="margin: -4px 0 0 10px; padding: 2px; font-size: 0.9em;" type="text" name="navigation_order_' . $nav->id . '" value="' . (isset($navOrder[$nav->id]) ? $navOrder[$nav->id] : 0) . '" size="3" />';
            }
            $fields5['navigation_id'] = array(
                'type'   => 'checkbox',
                'label'  => 'Navigation / Order:',
                'value'  => $navsAry,
                'marked' => $navsMarked
            );
            // Add categories
            if (count($categoryAry) > 0) {
                $fields5['category_id'] = array(
                    'type'     => 'checkbox',
                    'label'    => 'Categories:',
                    'value'    => $categoryAry
                );
            }
            $fields5['feed'] = array(
                'type'   => 'radio',
                'label'  => 'Include in Feed:',
                'value'  => array(1 => 'Yes', 0 => 'No'),
                'marked' => 1
            );
            $fields5['force_ssl'] = array(
                'type'   => 'radio',
                'label'  => 'Force SSL:',
                'value'  => array(1 => 'Yes', 0 => 'No'),
                'marked' => 0
            );
            $rolesAry = array();
            $roles = \Phire\Table\UserRoles::findAll('id ASC');
            foreach ($roles->rows as $role) {
                $rolesAry[$role->id] = $role->name;
            }
            $fields5['roles'] = array(
                'type'   => 'checkbox',
                'label'  => 'Roles:',
                'value'  => $rolesAry
            );
        } else {
            // Add categories
            if (count($categoryAry) > 0) {
                $fields5['category_id'] = array(
                    'type'     => 'checkbox',
                    'label'    => 'Categories:',
                    'value'    => $categoryAry
                );
            }
            $fields5['feed'] = array(
                'type'   => 'radio',
                'label'  => 'Include in Feed:',
                'value'  => array(1 => 'Yes', 0 => 'No'),
                'marked' => 1
            );
        }

        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, $tid, $mid);
        if ($newFields['dynamic']) {
            $dynamicFields = true;
        }
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        // Create pub/exp date fields for a URI-based content object
        if ($type->uri) {
            $fields4['published_month'] = array(
                'type'       => 'select',
                'label'      => 'Publish / Start Date:',
                'value'      => Element\Select::MONTHS_SHORT,
                'marked'     => date('m')
            );
            $fields4['published_day'] = array(
                'type'       => 'select',
                'value'      => Element\Select::DAYS_OF_MONTH,
                'marked'     => date('d')
            );
            $fields4['published_year'] = array(
                'type'       => 'select',
                'value'      => 'YEAR_' . (date('Y') - 10) . '_' . (date('Y') + 10),
                'marked'     => date('Y')
            );
            $fields4['published_hour'] = array(
                'type'       => 'select',
                'value'      => Element\Select::HOURS_24,
                'marked'     => date('H')
            );
            $fields4['published_minute'] = array(
                'type'       => 'select',
                'value'      => Element\Select::MINUTES,
                'marked'     => date('i')
            );
            $fields4['expired_month'] = array(
                'type'       => 'select',
                'label'      => 'Expiration / End Date:',
                'value'      => Element\Select::MONTHS_SHORT
            );
            $fields4['expired_day'] = array(
                'type'       => 'select',
                'value'      => Element\Select::DAYS_OF_MONTH
            );
            $fields4['expired_year'] = array(
                'type'       => 'select',
                'value'      => 'YEAR_' . (date('Y') - 10) . '_' . (date('Y') + 10)
            );
            $fields4['expired_hour'] = array(
                'type'       => 'select',
                'value'      => Element\Select::HOURS_24
            );
            $fields4['expired_minute'] = array(
                'type'       => 'select',
                'value'      => Element\Select::MINUTES
            );
        }

        $fields6 = array(
            'submit' => array(
                'type'  => 'submit',
                'value' => 'SAVE',
                'attributes' => array(
                    'class'   => 'save-btn'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => 'UPDATE',
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#content-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class'   => 'update-btn'
                )
            )
        );

        if ($type->uri == 1) {
            $fields6['preview'] = array(
                'type'       => 'button',
                'value'      => 'PREVIEW',
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#content-form', true, true);",
                    'class'   => 'preview-btn'
                )
            );
        }

        $fields6['type_id'] = array(
            'type'  => 'hidden',
            'value' => $tid
        );
        $fields6['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields6['update_value'] = array(
            'type'  => 'hidden',
            'value' => 0
        );
        $fields6['live'] = array(
            'type'  => 'hidden',
            'value' => (isset($_GET['live']) && ($_GET['live'] == 1)) ? 1 : 0
        );

        $allFields = array();
        $allFields[] = array_merge($fields6, $fields4, $fields5);
        $allFields[] = $fields1;

        if (count($fieldGroups) > 0) {
            foreach ($fieldGroups as $fg) {
                $allFields[] = $fg;
            }
        }

        return $allFields;
    }

    /**
     * Method to get templates
     *
     * @param  array $cfg
     * @return array
     */
    protected function getTemplates($cfg = array())
    {
        // Get view templates path from config, or fall back to the default
        if (isset($cfg['view'])) {
            if (is_array($cfg['view'])) {
                if (isset($cfg['view']['Phire\Controller\IndexController'])) {
                    $view = $cfg['view']['Phire\Controller\IndexController'];
                } else if (isset($cfg['view']['*'])) {
                    $view = $cfg['view']['*'];
                } else {
                    $view = realpath(__DIR__ . '/../../../view');
                }
            } else {
                $view = $cfg['view'];
            }
        } else {
            $view = realpath(__DIR__ . '/../../../view');
        }

        $templates = array('0' => '(Default)');

        $theme = Table\Extensions::findBy(array('type' => 0, 'active' => 1), null, 1);
        if (isset($theme->id)) {
            $assets = unserialize($theme->assets);
            // Get any new templates
            $newTmpls = false;
            $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/' . $theme->name, false, false, false);
            foreach ($dir->getFiles() as $file) {
                if (!in_array($file, $assets['templates'])) {
                    if (stripos($file, '.html') !== false) {
                        $tmpl = file_get_contents($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/' . $theme->name . '/' . $file);
                        $newTemplate = new Table\Templates(array(
                            'parent_id'    => null,
                            'name'         => $file,
                            'content_type' => 'text/html',
                            'device'       => 'desktop',
                            'template'     => $tmpl
                        ));
                        $newTemplate->save();
                        $assets['templates']['template_' . $newTemplate->id] = $file;
                    } else if ((stripos($file, '.phtml') !== false) || (stripos($file, '.php') !== false) || (stripos($file, '.php3') !== false)) {
                        $assets['templates'][] = $file;
                    }
                    $newTmpls = true;
                }
            }

            // Save new template assets
            if ($newTmpls) {
                $theme->assets = serialize($assets);
                $theme->update();
            }

            $themeName = $theme->name;
            foreach ($assets['info'] as $k => $v) {
                if (stripos($k, 'name') !== false) {
                    $themeName = $v;
                }
            }

            foreach ($assets['templates'] as $key => $value) {
                if ((stripos($value, 'search') === false) &&
                    (stripos($value, 'sidebar') === false) &&
                    (stripos($value, 'category') === false) &&
                    (stripos($value, 'date') === false) &&
                    (stripos($value, 'error') === false) &&
                    (stripos($value, 'header') === false) &&
                    (stripos($value, 'footer') === false)) {
                    if (strpos($key, 'template_') !== false) {
                        $id = substr($key, (strpos($key, '_') + 1));
                        $templates[$id] = $value . ' (' . $themeName . ')';
                    } else {
                        $templates[$value] = $value . ' (' . $themeName . ')';
                    }
                }
            }
        }

        $tmpls = Table\Templates::findAll('id ASC');
        $viewDir = new Dir($view, false, false, false);

        foreach ($tmpls->rows as $tmpl) {
            if (null === $tmpl->parent_id) {
                if ((stripos($tmpl->name, 'search') === false) &&
                    (stripos($tmpl->name, 'sidebar') === false) &&
                    (stripos($tmpl->name, 'category') === false) &&
                    (stripos($tmpl->name, 'date') === false) &&
                    (stripos($tmpl->name, 'error') === false) &&
                    (stripos($tmpl->name, 'header') === false) &&
                    (stripos($tmpl->name, 'footer') === false)) {
                    $templates[$tmpl->id] = $tmpl->name;
                }
            }
        }

        foreach ($viewDir->getFiles() as $file) {
            $ext = strtolower(substr($file, strrpos($file, '.')));
            if (($ext == '.phtml') || ($ext == '.php') || ($ext == '.php3')) {
                if ((stripos($file, 'search') === false) &&
                    (stripos($file, 'sidebar') === false) &&
                    (stripos($file, 'category') === false) &&
                    (stripos($file, 'date') === false) &&
                    (stripos($file, 'error') === false) &&
                    (stripos($file, 'header') === false) &&
                    (stripos($file, 'footer') === false)) {
                    $templates[$file] = $file;
                }
            }
        }

        return $templates;
    }

    /**
     * Recursive method to get children of the content object
     *
     * @param  int   $pid
     * @param  array $children
     * @param  int   $depth
     * @return array
     */
    protected function children($pid, $children = array(), $depth = 0)
    {
        $c = Table\Content::findBy(array('parent_id' => $pid));

        if (isset($c->rows[0])) {
            foreach ($c->rows as $child) {
                $children[$child->id] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', ($depth + 1)) . '&gt; ' . $child->title;
                $c = Table\Content::findBy(array('parent_id' => $child->id));
                if (isset($c->rows[0])) {
                    $d = $depth + 1;
                    $children = $this->children($child->id, $children, $d);
                }
            }
        }

        return $children;
    }

}

