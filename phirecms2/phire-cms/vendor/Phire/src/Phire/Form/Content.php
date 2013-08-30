<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\File\Dir;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Http\Response;
use Pop\Validator;
use Phire\Model;
use Phire\Table;

class Content extends Form
{

    /**
     * Has file flag
     *
     * @var boolean
     */
    protected $hasFile = false;

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string  $action
     * @param  string  $method
     * @param  int     $tid
     * @param  int     $mid
     * @param  boolean $isFields
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $mid = 0, $isFields = false)
    {
        // Generate fields for content type select first
        if ($tid == 0) {
            $typesAry = array();
            $types = Table\ContentTypes::findAll('order ASC');
            foreach ($types->rows as $type) {
                $typesAry[$type->id] = $type->name;
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
                    'value' => 'Select'
                )
            );
        // Else, generate fields for the content object
        } else {
            $this->initFieldsValues = $this->getInitFields($tid, $mid, $isFields);
        }

        parent::__construct($action, $method, null, '    ');
        $this->setAttributes('id', 'content-form');

        if ($this->hasFile) {
            $this->setAttributes('enctype', 'multipart/form-data');
        }
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
            $uri = Table\Content::findBy(array('slug' => $this->uri));
            if (($type->uri) && (isset($uri->id)) && ((int)$this->parent_id == (int)$uri->parent_id)  && ($this->id != $uri->id)) {
                if ($this->uri == '') {
                    $this->getElement('uri')
                         ->addValidator(new Validator\NotEmpty($this->uri, 'The root URI already exists.'));
                } else {
                    $this->getElement('uri')
                         ->addValidator(new Validator\NotEqual($this->uri, 'That URI already exists under that parent content.'));
                }
            }
        }

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
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

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  int     $mid
     * @param  boolean $isFields
     * @return array
     */
    protected function getInitFields($tid = 0, $mid = 0, $isFields = false)
    {
        // Get types and type object
        $typesAry = array();
        $types = Table\ContentTypes::findAll(null, array('uri' => 1));
        foreach ($types->rows as $type) {
            $typesAry[] = $type->id;
        }

        $type = Table\ContentTypes::findById($tid);

        // Get children, if applicable
        $children = ($mid != 0) ? $this->children($mid) : array();
        $parents = array(0 => '----');

        // Prevent the object's children or itself from being in the parent drop down
        $content = Table\Content::findAll('order ASC');
        foreach ($content->rows as $c) {
            if (($c->id != $mid) && (!in_array($c->id, $children)) && (in_array($c->type_id, $typesAry))) {
                $parents[$c->id] = $c->title;
            }
        }

        // Get categories
        $categories = new Model\Category();
        $categories->getAll();
        $categoryAry = $categories->getCategoryArray();
        unset($categoryAry[0]);

        // If type requires a URI
        if ($type->uri) {
            $fields1 = array(
                'parent_id' => array(
                    'type'       => 'select',
                    'label'      => 'Parent:',
                    'value'      => $parents,
                    'attributes' => array(
                        'onchange' => "slug(null, 'uri');"
                    )
                ),
                'template' => array(
                    'type' => 'select',
                    'label'      => 'Template:',
                    'value'      => $this->getTemplates(),
                )
            );
            $uri = array(
                'type'       => 'text',
                'label'      => 'URI:',
                'attributes' => array(
                    'size'    => 40,
                    'onkeyup' => "slug(null, 'uri');"
                )
            );
            $titleAttributes = array(
                'size'    => 40,
                'style'   => 'display: block;',
                'onkeyup' => "slug('content_title', 'uri');"
            );
        // Else, if type is a file
        } else {
            $this->hasFile = true;
            $label = 'File:';
            $required = true;
            if ($mid != 0) {
                $content = Table\Content::findById($mid);
                if (isset($content->id)) {
                    $fileInfo = Model\Content::getFileIcon($content->uri);
                    $label = '<em>Replace?</em><br /><a href="' .
                        BASE_PATH . CONTENT_PATH . '/media/' . $content->uri . '"><img style="padding-top: 3px;" src="' .
                        BASE_PATH . CONTENT_PATH . '/media' . $fileInfo['fileIcon'] . '" width="50" /></a><br /><a href="' . BASE_PATH . CONTENT_PATH . '/media/' . $content->uri . '">' .
                        $content->uri . '</a><br /><span style="font-size: 0.9em;">(' . $fileInfo['fileSize'] . ')</span>';
                    $required = false;
                }
            }
            $uri = array(
                'type'       => 'file',
                'label'      => $label,
                'required'   => $required,
                'attributes' => array('size' => 40)
            );
            $titleAttributes = array(
                'size'    => 40
            );
        }

        $fields1['content_title'] = array(
            'type'       => 'text',
            'label'      => 'Title:',
            'required'   => true,
            'attributes' => $titleAttributes
        );

        $fields1['uri'] =  $uri;

        $fields5 = array(
            'order' => array(
                'type'       => 'text',
                'label'      => 'Order:',
                'attributes' => array('size' => 3),
                'value'      => 0
            )
        );

        // Add nav include and roles
        if (!$this->hasFile) {
            $fields5['include'] = array(
                'type'   => 'radio',
                'label'  => 'Include in Nav:',
                'value'  => array(1 => 'Yes', 0 => 'No'),
                'marked' => 1
            );
            $fields5['feed'] = array(
                'type'   => 'radio',
                'label'  => 'Include in Feed:',
                'value'  => array(1 => 'Yes', 0 => 'No'),
                'marked' => 1
            );
            $fields5['status'] = array(
                'type'   => 'select',
                'label'  => 'Status:',
                'value'  => array(
                    0 => 'Unpublished',
                    1 => 'Draft',
                    2 => 'Published'
                ),
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
            $fields5['feed'] = array(
                'type'   => 'radio',
                'label'  => 'Include in Feed:',
                'value'  => array(1 => 'Yes', 0 => 'No'),
                'marked' => 1
            );
        }

        $fields2 = array();
        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $model = str_replace('Form', 'Model', get_class($this));
            $newFields = \Fields\Model\Field::getByModel($model, $tid, $mid);
            if (count($newFields) > 0) {
                foreach ($newFields as $key => $value) {
                    $fields2[$key] = $value;
                    if ($value['type'] == 'file') {
                        $this->hasFile = true;
                    }
                }
            }
        }

        $fields3 = array();
        // Add categories
        if (count($categoryAry) > 0) {
            $fields3['category_id'] = array(
                'type'     => 'checkbox',
                'label'    => 'Categories:',
                'value'    => $categoryAry
            );
        }

        $fields4 = array();
        // Create pub/exp date fields for a URI-based content object
        if ($type->uri) {
            $fields4['published_month'] = array(
                'type'       => 'select',
                'label'      => 'Published:',
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
                'label'      => 'Expired:',
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
            'type_id' => array(
                'type'  => 'hidden',
                'value' => $tid
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Save'
            )
        );

        $allFields = array($fields1);
        if(count($fields2) > 0) {
            $allFields[] = $fields2;
        }
        if(count($fields3) > 0) {
            $allFields[] = $fields3;
        }
        if(count($fields4) > 0) {
            $allFields[] = $fields4;
        }
        $allFields[] = $fields5;
        $allFields[] = $fields6;

        return $allFields;
    }

    /**
     * Method to get templates
     *
     * @return array
     */
    protected function getTemplates()
    {
        $cfg = include __DIR__ . '/../../../config/module.config.php';
        $cfg = $cfg['Phire']->asArray();

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
                    } else {
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

            foreach ($assets['templates'] as $key => $value) {
                if ((stripos($value, 'search') === false) &&
                    (stripos($value, 'category') === false) &&
                    (stripos($value, 'date') === false) &&
                    (stripos($value, 'error') === false) &&
                    (stripos($value, 'header') === false) &&
                    (stripos($value, 'footer') === false)) {
                    if (strpos($key, 'template_') !== false) {
                        $id = substr($key, (strpos($key, '_') + 1));
                        $templates[$id] = $value . ' (' . $theme->name . ')';
                    } else {
                        $templates[$value] = $value . ' (' . $theme->name . ')';
                    }
                }
            }
        } else {
            $tmpls = Table\Templates::findAll('id ASC');
            $viewDir = new Dir($view, false, false, false);

            foreach ($tmpls->rows as $tmpl) {
                if (null === $tmpl->parent_id) {
                    if ((stripos($tmpl->name, 'search') === false) &&
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
                        (stripos($file, 'category') === false) &&
                        (stripos($file, 'date') === false) &&
                        (stripos($file, 'error') === false) &&
                        (stripos($file, 'header') === false) &&
                        (stripos($file, 'footer') === false)) {
                        $templates[$file] = $file;
                    }
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
     * @return array
     */
    protected function children($pid, $children = array())
    {
        $c = Table\Content::findBy(array('parent_id' => $pid));

        if (isset($c->rows[0])) {
            foreach ($c->rows as $child) {
                $children[] = $child->id;
                $c = Table\Content::findBy(array('parent_id' => $child->id));
                if (isset($c->rows[0])) {
                    $children = $this->children($child->id, $children);
                }
            }
        }

        return $children;
    }

}

