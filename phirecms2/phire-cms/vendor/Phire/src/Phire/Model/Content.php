<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\Dir;
use Pop\File\File;
use Phire\Table;

class Content extends AbstractContentModel
{

    /**
     * Allowed media actions
     *
     * @var   array
     */
    protected static $mediaActions = array(
        'resize'         => 'resize',
        'resizeToWidth'  => 'resizeToWidth',
        'resizeToHeight' => 'resizeToHeight',
        'scale'          => 'scale',
        'crop'           => 'crop',
        'cropThumb'      => 'cropThumb'
    );

    /**
     * Allowed media types
     *
     * @var   array
     */
    protected static $mediaTypes = array(
        'ai'     => 'application/postscript',
        'aif'    => 'audio/x-aiff',
        'aiff'   => 'audio/x-aiff',
        'avi'    => 'video/x-msvideo',
        'bmp'    => 'image/x-ms-bmp',
        'bz2'    => 'application/bzip2',
        'css'    => 'text/css',
        'csv'    => 'text/csv',
        'doc'    => 'application/msword',
        'docx'   => 'application/msword',
        'eps'    => 'application/octet-stream',
        'fla'    => 'application/octet-stream',
        'flv'    => 'application/octet-stream',
        'gif'    => 'image/gif',
        'gz'     => 'application/x-gzip',
        'html'   => 'text/html',
        'htm'    => 'text/html',
        'jpe'    => 'image/jpeg',
        'jpg'    => 'image/jpeg',
        'jpeg'   => 'image/jpeg',
        'js'     => 'text/plain',
        'json'   => 'text/plain',
        'mov'    => 'video/quicktime',
        'mp2'    => 'audio/mpeg',
        'mp3'    => 'audio/mpeg',
        'mp4'    => 'video/mp4',
        'mpg'    => 'video/mpeg',
        'mpeg'   => 'video/mpeg',
        'otf'    => 'application/x-font-otf',
        'pdf'    => 'application/pdf',
        'phar'   => 'application/x-phar',
        'php'    => 'text/plain',
        'php3'   => 'text/plain',
        'phtml'  => 'text/plain',
        'png'    => 'image/png',
        'ppt'    => 'application/msword',
        'pptx'   => 'application/msword',
        'psd'    => 'image/x-photoshop',
        'rar'    => 'application/x-rar-compressed',
        'sql'    => 'text/plain',
        'svg'    => 'image/svg+xml',
        'swf'    => 'application/x-shockwave-flash',
        'tar'    => 'application/x-tar',
        'tbz'    => 'application/bzip2',
        'tbz2'   => 'application/bzip2',
        'tgz'    => 'application/x-gzip',
        'tif'    => 'image/tiff',
        'tiff'   => 'image/tiff',
        'tsv'    => 'text/tsv',
        'ttf'    => 'application/x-font-ttf',
        'txt'    => 'text/plain',
        'wav'    => 'audio/x-wav',
        'wma'    => 'audio/x-ms-wma',
        'wmv'    => 'audio/x-ms-wmv',
        'xls'    => 'application/msword',
        'xlsx'   => 'application/msword',
        'xhtml'  => 'application/xhtml+xml',
        'xml'    => 'application/xml',
        'yml'    => 'text/plain',
        'zip'    => 'application/x-zip'
    );

    /**
     * Get media actions
     *
     * @return array
     */
    public static function getMediaActions()
    {
        return self::$mediaActions;
    }

    /**
     * Get media types
     *
     * @return array
     */
    public static function getMediaTypes()
    {
        return self::$mediaTypes;
    }

    /**
     * Get all content types method
     *
     * @return void
     */
    public function getContentTypes()
    {
        $types = Table\ContentTypes::findAll('order ASC');
        $this->data['types'] = $types->rows;
    }

    /**
     * Get all content method
     *
     * @param  int    $typeId
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($typeId, $sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);

        $sql = Table\Content::getSql();

        // Get the correct placeholder
        if ($sql->getDbType() == \Pop\Db\Sql::PGSQL) {
            $p1 = '$1';
            $p2 = '$2';
        } else if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
            $p1 = ':type_id';
            $p2 = ':title';
        } else {
            $p1 = '?';
            $p2 = '?';
        }

        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'content.id' : $order['field'];

        $sql->select(array(
            DB_PREFIX . 'content.id',
            DB_PREFIX . 'content.parent_id',
            DB_PREFIX . 'content.type_id',
            DB_PREFIX . 'content_types.name',
            DB_PREFIX . 'content.title',
            DB_PREFIX . 'content.uri',
            DB_PREFIX . 'content.created',
            DB_PREFIX . 'content.created_by',
            DB_PREFIX . 'content.order',
            'user_id' => DB_PREFIX . 'users.id',
            DB_PREFIX . 'users.username',
            DB_PREFIX . 'content.status'
        ))->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'users', array('created_by', 'id'), 'LEFT JOIN')
          ->orderBy($order['field'], $order['order']);

        $sql->select()->where()->equalTo(DB_PREFIX . 'content.type_id', $p1);
        $params = array('type_id' => $typeId);

        if (isset($_GET['search_title']) && (!empty($_GET['search_title']))) {
            $sql->select()->where()->like(DB_PREFIX . 'content.title', $p2);
            $params['title'] = '%' . $_GET['search_title'] . '%';
        }

        $content = Table\Content::execute($sql->render(true), $params);
        $contentType = Table\ContentTypes::findById($typeId);
        $this->data['type'] = $contentType->name;

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_content[]" value="[{id}]" id="remove_content[{i}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_content" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove',
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Remove',
                'style' => 'display: none;'
            );
        }

        // Set headers based on URI or file
        if ($contentType->uri) {
            $headers = array(
                'id'           => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=id">#</a>',
                'title'        => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=title">Title</a>',
                'created_date' => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=created">Created</a>',
                'status'       => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=status">Status</a>',
                'uri'          => 'URI',
                'username'     => 'Author',
                'copy'         => '<span style="display: block; margin: 0 auto; width: 100%; text-align: center;">Copy</span>',
                'process'      => $removeCheckAll
            );
        } else {
            $headers = array(
                'id'           => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=id">#</a>',
                'title'        => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=title">Title</a>',
                'created_date' => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=created">Created</a>',
                'username'     => 'Author',
                'status'       => 'File',
                'size'         => 'Size',
                'uri'          => 'URI',
                'process'      => $removeCheckAll
            );
        }

        $options = array(
            'form' => array(
                'id'      => 'content-remove-form',
                'action'  => BASE_PATH . APP_URI . '/content/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers'     => $headers,
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'date' => 'M j, Y',
            'exclude' => array(
                'parent_id', 'type_id', 'name', 'order', 'created_by', 'user_id'
            )
        );

        $this->data['title'] .= (isset($contentType->id)) ? ' ' . $this->config->separator . ' ' . $contentType->name : null;
        $this->data['content'] = $content->rows;
        $this->data['contentTree'] = $this->getChildren($content->rows, 0);

        $status = array('Unpublished', 'Draft', 'Published');
        $contentAry = array();
        $ids = array();

        foreach ($content->rows as $content) {
            $c = (array)$content;

            // Track open authoring
            if ((!$this->config->open_authoring) && ($c['created_by'] != $this->user->id)) {
                $ids[] = $c['id'];
            } else {
                if ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'edit')) {
                    $c['title'] = '<a href="' . BASE_PATH . APP_URI . '/content/edit/' . $c['id'] . '">' . $c['title'] . '</a>';
                }
            }

            // Adjust URI link based on URI or file
            if (substr($c['uri'], 0, 1) == '/') {
                $c['status'] = (isset($c['status'])) ? $status[$c['status']] : '';
                $c['uri'] = '<a href="http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . $c['uri'] . '" target="_blank">http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . $c['uri'] . '</a>';
            } else {
                $fileInfo = self::getFileIcon($c['uri']);
                $c['status'] = '<a href="http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . CONTENT_PATH . '/media/' . $c['uri'] . '" target="_blank"><img src="http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="32" /></a>';
                $c['size'] = $fileInfo['fileSize'];
                $c['uri'] = '<a href="http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . CONTENT_PATH . '/media/' . $c['uri'] . '" target="_blank">http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . CONTENT_PATH . '/media/' . $c['uri'] . '</a>';
            }
            $c['created_date'] = $c['created'];
            // Add copy link
            if (($contentType->uri) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'copy'))) {
                $c['copy'] = '<a class="copy-link" href="' . BASE_PATH . APP_URI . '/content/copy/' . $c['id'] . '">Copy</a>';
            }
            unset($c['created']);
            $contentAry[] = $c;
        }

        if (isset($contentAry[0])) {
            $table = Html::encode($contentAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            if ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'remove')) {
                // If there are open authoring ids, remove "remove" checkbox
                if (count($ids) > 0) {
                    foreach ($ids as $id) {
                        $rm = substr($table, strpos($table, '<input type="checkbox" name="remove_content[]" value="' . $id . '" id="remove_content'));
                        $rm = substr($rm, 0, (strpos($rm, ' />') + 3));
                        $table = str_replace($rm, '&nbsp;', $table);
                    }
                }
            }
            $this->data['table'] = $table;
        }
    }

    /**
     * Get content by URI method
     *
     * @param  string  $uri
     * @param  boolean $isFields
     * @return void
     */
    public function getByUri($uri, $isFields = false)
    {
        $content = Table\Content::findBy(array('uri' => $uri));
        if (isset($content->id)) {
            $this->getNav($content);
            $this->isAllowed($content);

            $contentValues = $content->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $contentValues = array_merge($contentValues, \Fields\Model\FieldValue::getAll($content->id, true));
            }

            $this->data = array_merge($this->data, $contentValues);
        }
    }

    /**
     * Get content by date method
     *
     * @param  array   $date
     * @param  boolean $isFields
     * @return void
     */
    public function getByDate($date, $isFields = false)
    {
        $this->data['date'] = $date['match'];
        $content = Table\Content::findByDate($date);

        if (empty($date['uri'])) {
            $this->data['rows'] = $content->rows;
        } else if (isset($content->id)) {
            $this->getNav($content);
            $this->isAllowed($content);

            $contentValues = $content->getValues();

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $contentValues = array_merge($contentValues, \Fields\Model\FieldValue::getAll($content->id, true));
            }

            $this->data = array_merge($this->data, $contentValues);
        }
    }

    /**
     * Search for content
     *
     * @param  boolean $isFields
     * @param  \Pop\Http\Request $request
     * @return void
     */
    public function search($request, $isFields = false)
    {
        $this->getNav();
        $this->data['keys'] = array();
        $this->data['results'] = array();
        $track = array();

        // Get search keys
        if ($request->isPost()) {
            $this->data['keys'] = array_keys($request->getPost());
            $search = $request->getPost();
        } else {
            $this->data['keys'] = array_keys($request->getQuery());
            $search = $request->getQuery();
        }

        // Perform search
        if (count($this->data['keys']) > 0) {
            $results = array();

            // If just a search by content title
            if (isset($search['title'])) {
                $sql = Table\Content::getSql();

                // Get the correct placeholder
                if ($sql->getDbType() == \Pop\Db\Sql::PGSQL) {
                    $placeholder = '$1';
                } else if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
                    $placeholder = ':title';
                } else {
                    $placeholder = '?';
                }

                $sql->select()->where()->like('title', $placeholder);
                $content = Table\Content::execute($sql->render(true), array('title' => '%' . $search['title'] . '%'));
                $results = $content->rows;
            }

            // If the Fields module is installed, search fields by name/value
            if ($isFields) {
                foreach ($this->data['keys'] as $key) {
                    if (isset($search[$key]) && ($search[$key] != '')) {
                        $field = \Fields\Table\Fields::findBy(array('name' => $key));
                        if (isset($field->id)) {
                            $sql = \Fields\Table\FieldValues::getSql();
                            // Get the correct placeholder
                            if ($sql->getDbType() == \Pop\Db\Sql::PGSQL) {
                                $p1 = '$1';
                                $p2 = '$2';
                            } else if ($sql->getDbType() == \Pop\Db\Sql::SQLITE) {
                                $p1 = ':field_id';
                                $p2 = ':value';
                            } else {
                                $p1 = '?';
                                $p2 = '?';
                            }
                            $sql->select(array(
                                DB_PREFIX . 'field_values.field_id',
                                DB_PREFIX . 'field_values.model_id',
                                DB_PREFIX . 'field_values.value',
                                DB_PREFIX . 'fields_to_models.model'
                            ))->join(DB_PREFIX . 'fields_to_models', array('field_id', 'field_id'), 'LEFT_JOIN');
                            $sql->select()->where()->equalTo(DB_PREFIX . 'field_values.field_id', $p1)->like('value', $p2);

                            // Execute field values SQL
                            $fieldValues = \Fields\Table\FieldValues::execute(
                                $sql->render(true),
                                array(
                                    'field_id' => $field->id,
                                    'value' => '%' . $search[$key] . '%'
                                )
                            );

                            // If field values are found, extrapolate the table class from the model class
                            if (isset($fieldValues->rows[0])) {
                                foreach ($fieldValues->rows as $fv) {
                                    $tableClass = str_replace('Model', 'Table', $fv->model);
                                    if (!class_exists($tableClass, false)) {
                                        if (substr($tableClass, -1) == 's') {
                                            $tableClass = substr($tableClass, 0, -1);
                                        } else {
                                            $tableClass .= 's';
                                        }
                                        if (!class_exists($tableClass, false)) {
                                            if (substr($tableClass, -1) == 'y') {
                                                $tableClass = substr($tableClass, 0, -1) . 'ies';
                                                if (!class_exists($tableClass, false)) {
                                                    $tableClass = null;
                                                }
                                            }
                                        }
                                    }

                                    // If table class is found, find model object
                                    if ((null !== $tableClass) && (!in_array($fv->model_id, $track))) {
                                        $cont = $tableClass::findById($fv->model_id);
                                        if (isset($cont->id)) {
                                            $results[] = $cont;
                                            $track[] = $fv->model_id;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->data['results'] = $results;
        }
    }

    /**
     * Get content by ID method
     *
     * @param  int     $id
     * @param  boolean $isFields
     * @return void
     */
    public function getById($id, $isFields = false)
    {
        $content = Table\Content::findById($id);
        if (isset($content->id)) {
            $type = Table\ContentTypes::findById($content->type_id);

            $contentValues = $content->getValues();
            $contentValues['type_name'] = (isset($type->id) ? $type->name : null);
            $contentValues['content_title'] = $contentValues['title'];
            $contentValues['full_uri'] = $contentValues['uri'];
            $contentValues['uri'] = $contentValues['slug'];
            unset($contentValues['title']);
            unset($contentValues['slug']);

            $publishedAry = explode(' ', $contentValues['published']);
            $dateAry = explode('-', $publishedAry[0]);
            $timeAry = explode(':', $publishedAry[1]);

            $contentValues['published_month'] = $dateAry[1];
            $contentValues['published_day'] = $dateAry[2];
            $contentValues['published_year'] = $dateAry[0];
            $contentValues['published_hour'] = $timeAry[0];
            $contentValues['published_minute'] = $timeAry[1];

            if ((null !== $contentValues['expired']) && ($contentValues['expired'] != '0000-00-00 00:00:00')) {
                $expiredAry = explode(' ', $contentValues['expired']);
                $dateAry = explode('-', $expiredAry[0]);
                $timeAry = explode(':', $expiredAry[1]);

                $contentValues['expired_month'] = $dateAry[1];
                $contentValues['expired_day'] = $dateAry[2];
                $contentValues['expired_year'] = $dateAry[0];
                $contentValues['expired_hour'] = $timeAry[0];
                $contentValues['expired_minute'] = $timeAry[1];
            }

            $cats = Table\ContentToCategories::findAll(null, array('content_id' => $id));
            if (isset($cats->rows[0])) {
                $catAry = array();
                foreach ($cats->rows as $cat) {
                    $catAry[] = $cat->category_id;
                }
                $contentValues['category_id'] = $catAry;
            }

            // Get roles
            $roles = Table\ContentToRoles::findAll(null, array('content_id' => $id));
            if (isset($roles->rows[0])) {
                $rolesAry = array();
                foreach ($roles->rows as $role) {
                    $rolesAry[] = $role->role_id;
                }
                $contentValues['roles'] = $rolesAry;
            }
            if (($contentValues['updated'] != '0000-00-00 00:00:00') && (null !== $contentValues['updated'])) {
                $contentValues['updated'] = '<strong>Updated:</strong> ' . date($this->config->datetime_format, strtotime($contentValues['updated']));
                if (null !== $contentValues['updated_by']) {
                    $u = Table\Users::findById($contentValues['updated_by']);
                    if (isset($u->username)) {
                        $contentValues['updated'] .= ' by <strong>' . $u->username . '</strong>';
                    }
                }
            } else {
                $contentValues['updated'] = '<strong>Updated:</strong> Never';
            }

            // If the Fields module is installed, and if there are fields for this form/model
            if ($isFields) {
                $contentValues = array_merge($contentValues, \Fields\Model\FieldValue::getAll($id));
            }

            if (!((!$this->config->open_authoring) && ($contentValues['created_by'] != $this->user->id))) {
                $this->data = array_merge($this->data, $contentValues);
            }
        }
    }

    /**
     * Get content feed
     *
     * @param  int $limit
     * @return array
     */
    public function getFeed($limit = 0)
    {
        if ($limit == 0) {
            $limit = null;
        }

        $entries = array();

        $content = Table\Content::findAll('published DESC', array('feed' => 1), $limit);
        foreach ($content->rows as $c) {
            if (((null === $c->status) || ($c->status == self::PUBLISHED)) &&
                (strtotime($c->published) <= time()) &&
                ((null === $c->expired) || ((null !== $c->expired) && (strtotime($c->expired) >= time())))) {
                $uri = (null !== $c->status) ? $c['uri'] : CONTENT_PATH . '/media/' . $c['uri'];
                $entries[] = array(
                    'title'    => $c->title,
                    'link'     => 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . $uri,
                    'updated'  => $c['published'],
                    'summary'  => $c->title
                );
            }
        }

        return $entries;
    }

    /**
     * Save content
     *
     * @param \Pop\Form\Form $form
     * @param  array         $cfg
     * @param  boolean       $isFields
     * @return void
     */
    public function save(\Pop\Form\Form $form, array $cfg = null, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $parentId = null;
        $published = null;
        $expired = null;
        $uri = null;
        $slug = null;

        if (isset($fields['parent_id'])) {
            $parentId = ((int)$fields['parent_id'] != 0) ? (int)$fields['parent_id'] : null;
        }

        if (isset($fields['published_year']) && ($fields['published_year'] != '----') && ($fields['published_month'] != '--') &&
            ($fields['published_day'] != '--') && ($fields['published_hour'] != '--') && ($fields['published_minute'] != '--')) {
            $published = $fields['published_year'] . '-' . $fields['published_month'] . '-' .
                $fields['published_day'] . ' ' . $fields['published_hour'] . ':' . $fields['published_minute'] . ':00';
        } else {
            $published = date('Y-m-d H:i:s');
        }

        if (isset($fields['expired_year']) && ($fields['expired_year'] != '----') && ($fields['expired_month'] != '--') &&
            ($fields['expired_day'] != '--') && ($fields['expired_hour'] != '--') && ($fields['expired_minute'] != '--')) {
            $expired = $fields['expired_year'] . '-' . $fields['expired_month'] . '-' .
                $fields['expired_day'] . ' ' . $fields['expired_hour'] . ':' . $fields['expired_minute'] . ':00';
        }

        // If content is a file
        if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['tmp_name'] != '')) {
            $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
            $fileName = File::checkDupe($_FILES['uri']['name'], $dir);

            $upload = File::upload(
                $_FILES['uri']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                $this->config->media_max_filesize, $this->config->media_allowed_types
            );
            $upload->setPermissions(0777);
            if (preg_match(self::$imageRegex, $fileName)) {
                self::processMedia($fileName, $this->config);
            }

            $title = ($fields['content_title'] != '') ?
                $fields['content_title'] :
                ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($fileName, 0, strrpos($fileName, '.'))));
            $uri = $fileName;
            $slug = $fileName;
        // Else, if the content is a regular content object
        } else {
            $title = $fields['content_title'];
            $slug = $fields['uri'];
            $uri = $fields['uri'];

            if ((int)$fields['parent_id'] != 0) {
                $pId = $fields['parent_id'];
                while ($pId != 0) {
                    $parentContent = Table\Content::findById($pId);
                    if (isset($parentContent->id)) {
                        $pId = $parentContent->parent_id;
                        $uri = $parentContent->slug . '/' . $uri;
                    }
                }
            }

            // URI clean up
            if (substr($uri, 0, 1) != '/') {
                $uri = '/' . $uri;
            } else if (substr($uri, 0, 2) == '//') {
                $uri = substr($uri, 1);
            } else if ($uri == '') {
                $uri = '/';
            }
        }

        $content = new Table\Content(array(
            'type_id'    => $fields['type_id'],
            'parent_id'  => $parentId,
            'template'   => ((isset($fields['template']) && ($fields['template'] != '0')) ? $fields['template'] : null),
            'title'      => $title,
            'uri'        => $uri,
            'slug'       => $slug,
            'order'      => (int)$fields['order'],
            'include'    => ((isset($fields['include']) ? (int)$fields['include'] : null)),
            'feed'       => (int)$fields['feed'],
            'force_ssl'  => ((isset($fields['force_ssl']) ? (int)$fields['force_ssl'] : null)),
            'status'     => ((isset($fields['status']) ? (int)$fields['status'] : null)),
            'created'    => date('Y-m-d H:i:s'),
            'updated'    => null,
            'published'  => $published,
            'expired'    => $expired,
            'created_by' => ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null),
            'updated_by' => null
        ));

        $content->save();
        $this->data['id'] = $content->id;
        $this->data['uri'] = $content->uri;

        // Save content categories
        if (isset($fields['category_id'])) {
            foreach ($fields['category_id'] as $cat) {
                $contentToCategory = new Table\ContentToCategories(array(
                    'content_id'  => $content->id,
                    'category_id' => $cat
                ));
                $contentToCategory->save();
            }
        }

        // Save content roles
        if (isset($fields['roles'])) {
            foreach ($fields['roles'] as $role) {
                $contentToRole = new Table\ContentToRoles(array(
                    'content_id' => $content->id,
                    'role_id'    => $role
                ));
                $contentToRole->save();
            }
        }

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::save($fields, $content->id);
        }
    }

    /**
     * Update content
     *
     * @param \Pop\Form\Form $form
     * @param  array         $cfg
     * @param  boolean       $isFields
     * @return void
     */
    public function update(\Pop\Form\Form $form, array $cfg = null, $isFields = false)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $content = Table\Content::findById($fields['id']);

        $parentId = null;
        $uri = null;
        $slug = null;

        if (isset($fields['parent_id'])) {
            $parentId = ((int)$fields['parent_id'] != 0) ? (int)$fields['parent_id'] : null;
        }

        if (isset($fields['published_year']) && ($fields['published_year'] != '----') && ($fields['published_month'] != '--') &&
            ($fields['published_day'] != '--') && ($fields['published_hour'] != '--') && ($fields['published_minute'] != '--')) {
            $published = $fields['published_year'] . '-' . $fields['published_month'] . '-' .
                $fields['published_day'] . ' ' . $fields['published_hour'] . ':' . $fields['published_minute'] . ':00';
        } else {
            $published = $content->published;
        }

        if (isset($fields['expired_year']) && ($fields['expired_year'] != '----') && ($fields['expired_month'] != '--') &&
            ($fields['expired_day'] != '--') && ($fields['expired_hour'] != '--') && ($fields['expired_minute'] != '--')) {
            $expired = $fields['expired_year'] . '-' . $fields['expired_month'] . '-' .
                $fields['expired_day'] . ' ' . $fields['expired_hour'] . ':' . $fields['expired_minute'] . ':00';
        } else if (isset($fields['expired_year']) && ($fields['expired_year'] == '----') && ($fields['expired_month'] == '--') &&
            ($fields['expired_day'] == '--') && ($fields['expired_hour'] == '--') && ($fields['expired_minute'] == '--')) {
            $expired = '0000-00-00 00:00:00';
        }

        // If content is a file
        if (!isset($fields['parent_id'])) {
            if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['tmp_name'] != '')) {
                $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
                self::removeMedia($content->uri);
                $fileName = File::checkDupe($_FILES['uri']['name'], $dir);
                $upload = File::upload(
                    $_FILES['uri']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                    $this->config->media_max_filesize, $this->config->media_allowed_types
                );
                $upload->setPermissions(0777);
                if (preg_match(self::$imageRegex, $fileName)) {
                    self::processMedia($fileName, $this->config);
                }
                $title = ($fields['content_title'] != '') ?
                    $fields['content_title'] :
                    ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($fileName, 0, strrpos($fileName, '.'))));

                $uri = $fileName;
                $slug = $fileName;
            } else {
                $title = $content->title;
                $uri = $content->uri;
                $slug = $content->slug;
            }
        // Else, if the content is a regular content object
        } else {
            $title = $fields['content_title'];
            $slug = $fields['uri'];
            $uri = $fields['uri'];

            if ($fields['parent_id'] != 0) {
                $pId = $fields['parent_id'];
                while ($pId != 0) {
                    $parentContent = Table\Content::findById($pId);
                    if (isset($parentContent->id)) {
                        $pId = $parentContent->parent_id;
                        $uri = $parentContent->slug . '/' . $uri;
                    }
                }
            }

            // URI clean up
            if (substr($uri, 0, 1) != '/') {
                $uri = '/' . $uri;
            } else if (substr($uri, 0, 2) == '//') {
                $uri = substr($uri, 1);
            } else if ($uri == '') {
                $uri = '/';
            }
        }

        $content->type_id    = $fields['type_id'];
        $content->parent_id  = $parentId;
        $content->template   = ((isset($fields['template']) && ($fields['template'] != '0')) ? $fields['template'] : null);
        $content->title      = $title;
        $content->uri        = $uri;
        $content->slug       = $slug;
        $content->order      = (int)$fields['order'];
        $content->include    = ((isset($fields['include']) ? (int)$fields['include'] : null));
        $content->feed       = (int)$fields['feed'];
        $content->force_ssl  = ((isset($fields['force_ssl']) ? (int)$fields['force_ssl'] : null));
        $content->status     = ((isset($fields['status']) ? (int)$fields['status'] : null));
        $content->updated    = date('Y-m-d H:i:s');
        $content->published  = $published;
        $content->expired    = $expired;
        $content->updated_by = ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null);

        $content->update();
        $this->data['id'] = $content->id;
        $this->data['uri'] = $content->uri;

        // Update content categories
        $contentToCategories = Table\ContentToCategories::findBy(array('content_id' => $content->id));
        foreach ($contentToCategories->rows as $cat) {
            $contentToCat = Table\ContentToCategories::findById(array($content->id, $cat->category_id));
            if (isset($contentToCat->content_id)) {
                $contentToCat->delete();
            }
        }

        if (isset($fields['category_id'])) {
            foreach ($fields['category_id'] as $cat) {
                $contentToCategory = new Table\ContentToCategories(array(
                    'content_id'  => $content->id,
                    'category_id' => $cat
                ));
                $contentToCategory->save();
            }
        }

        // Update content roles
        $contentToRoles = Table\ContentToRoles::findBy(array('content_id' => $content->id));
        foreach ($contentToRoles->rows as $role) {
            $contentToRole = Table\ContentToRoles::findById(array($content->id, $role->role_id));
            if (isset($contentToRole->content_id)) {
                $contentToRole->delete();
            }
        }

        if (isset($fields['roles'])) {
            foreach ($fields['roles'] as $role) {
                $contentToRole = new Table\ContentToRoles(array(
                    'content_id' => $content->id,
                    'role_id'    => $role
                ));
                $contentToRole->save();
            }
        }

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            \Fields\Model\FieldValue::update($fields, $content->id);
        }
    }

    /**
     * Copy content
     *
     * @param  boolean $isFields
     * @return void
     */
    public function copy($isFields = false)
    {
        $id    = $this->data['id'];
        $title = $this->data['content_title'] . ' (Copy ';
        $uri   = $this->data['full_uri'];
        $slug  = $this->data['uri'];

        // Check for dupe uris
        $i = 1;
        $dupe = Table\Content::findBy(array('uri' => $uri . '-' . $i));
        while (isset($dupe->id)) {
            $i++;
            $dupe = Table\Content::findBy(array('uri' => $uri . '-' . $i));
        }

        $title .= $i . ')';
        $uri   .= '-' . $i;
        $slug  .= '-' . $i;

        $content = new Table\Content(array(
            'type_id'    => $this->data['type_id'],
            'parent_id'  => $this->data['parent_id'],
            'template'   => $this->data['template'],
            'title'      => $title,
            'uri'        => $uri,
            'slug'       => $slug,
            'order'      => $this->data['order'],
            'include'    => $this->data['include'],
            'feed'       => $this->data['feed'],
            'force_ssl'  => $this->data['force_ssl'],
            'status'     => 0,
            'created'    => date('Y-m-d H:i:s'),
            'updated'    => null,
            'published'  => date('Y-m-d H:i:s'),
            'expired'    => null,
            'created_by' => ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null),
            'updated_by' => null
        ));

        $content->save();
        $this->data['id'] = $content->id;

        // Save any content categories
        $cats = Table\ContentToCategories::findAll(null, array('content_id' => $id));
        if (isset($cats->rows[0])) {
            foreach ($cats->rows as $cat) {
                $contentToCategory = new Table\ContentToCategories(array(
                    'content_id'  => $content->id,
                    'category_id' => $cat->category_id
                ));
                $contentToCategory->save();
            }
        }

        // Save any content roles
        $roles = Table\ContentToRoles::findAll(null, array('content_id' => $id));
        if (isset($roles->rows[0])) {
            foreach ($roles->rows as $role) {
                $contentToRole = new Table\ContentToRoles(array(
                    'content_id' => $content->id,
                    'role_id'    => $role->role_id
                ));
                $contentToRole->save();
            }
        }

        // If the Fields module is installed, and if there are fields for this form/model
        if ($isFields) {
            $values = \Fields\Table\FieldValues::findAll(null, array('model_id' => $id));
            if (isset($values->rows[0])) {
                foreach ($values->rows as $value) {
                    $field = \Fields\Table\Fields::findById($value->field_id);
                    if (isset($field->id) && ($field->type != 'file')) {
                        $val = new \Fields\Table\FieldValues(array(
                            'field_id'  => $value->field_id,
                            'model_id'  => $content->id,
                            'value'     => $value->value,
                            'timestamp' => $value->timestamp,
                            'history'   => $value->history
                        ));
                        $val->save();
                    }
                }
            }
        }
    }

    /**
     * Static method to process uploaded media
     *
     * @param string       $fileName
     * @param \ArrayObject $config
     * @return void
     */
    public static function processMedia($fileName, $config)
    {
        $cfg = $config->media_actions;
        $adapter = '\Pop\Image\\' . $config->media_image_adapter;
        $formats = $adapter::formats();
        $ext = strtolower(substr($fileName, (strrpos($fileName, '.') + 1)));

        if (in_array($ext, $formats)) {
            $mediaDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
            foreach ($cfg as $size => $action) {
                if (in_array($action['action'], self::$mediaActions)) {
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

                    $img->setPermissions(0777);
                }
            }
        }
    }

    /**
     * Static method to remove uploaded media
     *
     * @param string $fileName
     * @return void
     */
    public static function removeMedia($fileName)
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
        if (file_exists($dir . $fileName)) {
            unlink($dir . $fileName);
        }

        $dirs = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');
        foreach ($dirs->getFiles() as $size) {
            if (is_dir($dir . $size)) {
                $newFileName = $fileName . '.jpg';
                if (file_exists($dir . $size . DIRECTORY_SEPARATOR . $fileName)) {
                    unlink($dir . $size . DIRECTORY_SEPARATOR . $fileName);
                } else if (file_exists($dir . $size . DIRECTORY_SEPARATOR . $newFileName)) {
                    unlink($dir . $size . DIRECTORY_SEPARATOR . $newFileName);
                }
            }
        }
    }

    /**
     * Static method to get a file icon
     *
     * @param string $fileName
     * @return array
     */
    public static function getFileIcon($fileName)
    {
        $mediaDir = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media/';
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
            $dirs = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/media', true);
            $fileSizes = array();
            foreach ($dirs->getFiles() as $dir) {
                if (is_dir($dir)) {
                    $f = $dir . $newFileName;
                    if (file_exists($f)) {
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

