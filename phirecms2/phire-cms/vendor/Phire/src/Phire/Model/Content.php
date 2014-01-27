<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Archive\Archive;
use Pop\Data\Type\Html;
use Pop\File\Dir;
use Pop\File\File;
use Pop\Web\Session;
use Phire\Table;

class Content extends AbstractModel
{

    /**
     * Constant for unpublished
     */
    const UNPUBLISHED = 0;

    /**
     * Constant for draft
     */
    const DRAFT = 1;

    /**
     * Constant for published
     */
    const PUBLISHED = 2;

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
     * Method to check is content object is allowed
     *
     * @param  mixed $content
     * @return boolean
     */
    public static function isAllowed($content)
    {
        $sess = Session::getInstance();
        $user = (isset($sess->user)) ? $sess->user : null;

        // Get any content roles
        $rolesAry = array();

        if (isset($content->title)) {
            $roles = (null !== $content->roles) ? unserialize($content->roles) : array();
            foreach ($roles as $id) {
                $rolesAry[] = $id;
            }
        }

        // If there are no roles, or the user's role is allowed
        if ((count($rolesAry) == 0) || ((count($rolesAry) > 0) && (null !== $user) && in_array($user['role_id'], $rolesAry))) {
            $allowed = true;
        // Else, not allowed
        } else {
            $allowed = false;
        }

        // Check if the content is published, a draft or expired
        if (isset($content->title) && isset($content->type_uri) && (null !== $content->status)) {
            // If a regular URI type
            if (($content->type_uri == 1) && ((strtotime($content->published) >= time()) ||
                ((null !== $content->expired) && ($content->expired != '0000-00-00 00:00:00') && (strtotime($content->expired) <= time())))) {
                $allowed = false;
            // Else, if an event type
            } else if ($content->type_uri == 2) {
                // If no end date
                if ((null === $content->expired) || ($content->expired == '0000-00-00 00:00:00')) {
                    if (strtotime($content->published) < time()) {
                        $allowed = false;
                    }
                } else {
                    if (strtotime($content->expired) <= time()) {
                        $allowed = false;
                    }
                }
            }

            // Published status override
            if ((int)$content->status == self::UNPUBLISHED) {
                $allowed = false;
            } else if ((int)$content->status == self::DRAFT) {
                $allowed = (isset($sess->user) && (strtolower($sess->user->type) == 'user'));
            }

            $site = Table\Sites::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
            if ((int)$content->site_id != (int)$site->id)  {
                $allowed = false;
            }
        }

        return $allowed;
    }

    /**
     * Get all content types method
     *
     * @return array
     */
    public function getContentTypes()
    {
        $types = Table\ContentTypes::findAll('order ASC');
        return $types->rows;
    }

    /**
     * Get recent content method
     *
     * @param  int  $limit
     * @return array
     */
    public function getRecent($limit = 10)
    {
        $sql = Table\Content::getSql();
        $sql->select(array(
            DB_PREFIX . 'content.id',
            DB_PREFIX . 'content.site_id',
            DB_PREFIX . 'content.type_id',
            DB_PREFIX . 'content_types.name',
            'type_uri' => DB_PREFIX . 'content_types.uri',
            DB_PREFIX . 'content.title',
            DB_PREFIX . 'content.uri',
            DB_PREFIX . 'content.created',
            DB_PREFIX . 'content.created_by',
            'user_id' => DB_PREFIX . 'users.id',
            DB_PREFIX . 'users.username',
            DB_PREFIX . 'content.status'
        ))->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'users', array('created_by', 'id'), 'LEFT JOIN')
          ->orderBy('created', 'DESC')
          ->limit((int)$limit);

        $content = Table\Content::execute($sql->render(true));

        foreach ($content->rows as $key => $value) {
            $site = Table\Sites::getSite((int)$value->site_id);
            $content->rows[$key]->domain    = $site->domain;
            $content->rows[$key]->base_path = $site->base_path;
        }

        return $content->rows;
    }

    /**
     * Get themes method
     *
     * @return array
     */
    public function getThemes()
    {
        $themes = Table\Extensions::findAll('id ASC', array('type' => 0));
        return $themes->rows;
    }

    /**
     * Get modules method
     *
     * @return array
     */
    public function getModules()
    {
        $modules = Table\Extensions::findAll('id ASC', array('type' => 1));
        return $modules->rows;
    }

    /**
     * Get all content method
     *
     * @param  int     $typeId
     * @param  string  $sort
     * @param  string  $page
     * @return void
     */
    public function getAll($typeId, $sort = null, $page = null)
    {
        $sess = Session::getInstance();
        $order = $this->getSortOrder($sort, $page);

        $sql = Table\Content::getSql();
        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'content.id' : $order['field'];

        $sql->select(array(
            DB_PREFIX . 'content.id',
            DB_PREFIX . 'content.parent_id',
            DB_PREFIX . 'content.site_id',
            DB_PREFIX . 'content.type_id',
            DB_PREFIX . 'content_types.name',
            'type_uri' => DB_PREFIX . 'content_types.uri',
            DB_PREFIX . 'content.title',
            DB_PREFIX . 'content.uri',
            DB_PREFIX . 'content.published',
            DB_PREFIX . 'content.expired',
            DB_PREFIX . 'content.created',
            DB_PREFIX . 'content.created_by',
            'user_id' => DB_PREFIX . 'users.id',
            DB_PREFIX . 'users.username',
            DB_PREFIX . 'content.status'
        ))->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'users', array('created_by', 'id'), 'LEFT JOIN')
          ->orderBy($order['field'], $order['order']);

        $sql->select()->where()->equalTo(DB_PREFIX . 'content.type_id', ':type_id');
        $params = array('type_id' => $typeId);

        if (isset($_GET['search_title']) && (!empty($_GET['search_title']))) {
            $sql->select()->where()->like(DB_PREFIX . 'content.title', ':title');
            $params['title'] = '%' . $_GET['search_title'] . '%';
            $this->data['searchTitle'] = htmlentities(strip_tags($_GET['search_title']), ENT_QUOTES, 'UTF-8');
        } else {
            $this->data['searchTitle'] = null;
        }

        if (isset($_GET['sites_search']) && (!empty($_GET['sites_search'])) && ($_GET['sites_search'] != '--')) {
            $sql->select()->where()->equalTo(DB_PREFIX . 'content.site_id', ':site_id');
            $params['site_id'] = (int)$_GET['sites_search'];
            $siteMarked = (int)$_GET['sites_search'];
        } else {
            $siteMarked = null;
        }

        $content = Table\Content::execute($sql->render(true), $params);
        $contentType = Table\ContentTypes::findById($typeId);
        $this->data['type'] = $contentType->name;

        if (($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'process')) &&
            ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'process_' . $typeId))) {
            $removeCheckbox = '<input type="checkbox" name="process_content[]" value="[{id}]" id="process_content[{i}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="process_content" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Process',
                'style' => 'float: right;'
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => 'Process',
                'style' => 'display: none;'
            );
        }

        // Set headers based on URI or file
        if ($contentType->uri) {
            $headers = array(
                'id'           => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=id">#</a>',
                'site_id'      => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=site_id">Site</a>',
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
                'site_id'      => '<a href="' . BASE_PATH . APP_URI . '/content/index/' . $typeId . '?sort=site_id">Site</a>',
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
                'action'  => BASE_PATH . APP_URI . '/content/process/' . $typeId,
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
                'parent_id', 'type_id', 'type_uri', 'name', 'order', 'created_by', 'user_id', 'published', 'expired'
            ),
            'indent' => '        '
        );

        $this->data['title']   = (isset($contentType->id)) ? $contentType->name : null;
        $this->data['type']    = $contentType->name;
        $this->data['typeUri'] = $contentType->uri;

        $status = array('<strong class="error">Unpublished</strong>', '<strong class="orange">Draft</strong>', '<strong class="green">Published</strong>');
        $contentAry = array();
        $ids = array();

        foreach ($content->rows as $content) {
            $c = (array)$content;
            $site = Table\Sites::getSite((int)$c['site_id']);
            $domain   = $site->domain;
            $basePath = $site->base_path;
            $docRoot  = $site->document_root;

            // Track open authoring
            if ((!$this->config->open_authoring) && ($c['created_by'] != $this->user->id)) {
                $ids[] = $c['id'];
            } else {
                if (($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'edit')) &&
                    ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'edit_' . $typeId))) {
                    $c['title'] = '<a href="http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . APP_URI . '/content/edit/' . $c['id'] . '">' . $c['title'] . '</a>';
                }
            }

            // Adjust URI link based on URI or file
            if (substr($c['uri'], 0, 1) == '/') {
                $c['status'] = (isset($c['status'])) ? $status[$c['status']] : '';
                $c['uri'] = '<a href="http://' . $domain . $basePath . $c['uri'] . '" target="_blank">http://' . $domain . $basePath . $c['uri'] . '</a>';
            } else {
                $fileInfo = self::getFileIcon($c['uri'], $docRoot . $basePath);
                $c['status'] = '<a href="http://' . $domain . $basePath . CONTENT_PATH . '/media/' . $c['uri'] . '" target="_blank"><img src="http://' . $domain . $basePath . CONTENT_PATH . $fileInfo['fileIcon'] . '" width="32" /></a>';
                $c['size'] = $fileInfo['fileSize'];
                $c['uri'] = '<a href="http://' . $domain . $basePath . CONTENT_PATH . '/media/' . $c['uri'] . '" target="_blank">http://' . $domain . $basePath . CONTENT_PATH . '/media/' . $c['uri'] . '</a>';
            }
            $c['created_date'] = $c['created'];
            // Add copy link
            if (($contentType->uri) && ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'copy')) &&
                ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'copy_' . $typeId))) {
                $c['copy'] = '<a class="copy-link" href="' . BASE_PATH . APP_URI . '/content/copy/' . $c['id'] . '">Copy</a>';
            }

            if (in_array($c['site_id'], $sess->user->site_ids)) {
                $c['site_id'] = $domain;
                unset($c['created']);
                $contentAry[] = $c;
            }
        }

        if (isset($contentAry[0])) {
            $table = Html::encode($contentAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
            if (($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'process')) &&
                ($this->data['acl']->isAuth('Phire\Controller\Phire\Content\IndexController', 'process_' . $typeId))) {
                // If there are open authoring ids, remove "remove" checkbox
                if (count($ids) > 0) {
                    foreach ($ids as $id) {
                        $rm = substr($table, strpos($table, '<input type="checkbox" name="process_content[]" value="' . $id . '" id="process_content'));
                        $rm = substr($rm, 0, (strpos($rm, ' />') + 3));
                        $table = str_replace($rm, '&nbsp;', $table);
                    }
                }
            }
            $select = '<select name="content_process" id="content-process"><option value="-1">Remove</option><option value="2">Publish</option><option value="1">Draft</option><option value="0">Unpublish</option></select>';
            $this->data['table'] = str_replace('value="Process" style="float: right;" />', 'value="Process" style="float: right;" />' . $select, $table);
        }

        $sites = Table\Sites::findAll();
        $sitesAry = array('--' => '(All Sites)');

        if (in_array(0, $this->user->site_ids)) {
            $sitesAry[0] = $_SERVER['HTTP_HOST'];
        }

        foreach ($sites->rows as $site) {
            if (in_array($site->id, $this->user->site_ids)) {
                $sitesAry[$site->id] = $site->domain;
            }
        }

        $this->data['sitesSearch'] = new \Pop\Form\Element\Select('sites_search', $sitesAry, $siteMarked);
    }

    /**
     * Get content by URI method
     *
     * @param  string  $uri
     * @return void
     */
    public function getByUri($uri)
    {
        $sql = Table\Content::getSql();
        $sql->select(array(
            0          => DB_PREFIX . 'content.id',
            1          => DB_PREFIX . 'content.site_id',
            2          => DB_PREFIX . 'content.type_id',
            3          => DB_PREFIX . 'content.parent_id',
            4          => DB_PREFIX . 'content.template',
            5          => DB_PREFIX . 'content.title',
            'uri'      => DB_PREFIX . 'content.uri',
            7          => DB_PREFIX . 'content.slug',
            8          => DB_PREFIX . 'content.feed',
            9          => DB_PREFIX . 'content.force_ssl',
            10         => DB_PREFIX . 'content.status',
            11         => DB_PREFIX . 'content.roles',
            12         => DB_PREFIX . 'content.created',
            13         => DB_PREFIX . 'content.updated',
            14         => DB_PREFIX . 'content.published',
            15         => DB_PREFIX . 'content.expired',
            16         => DB_PREFIX . 'content.created_by',
            17         => DB_PREFIX . 'content.updated_by',
            'type_uri' => DB_PREFIX . 'content_types.uri'
        ))->where()->equalTo(DB_PREFIX . 'content.uri', ':uri');

        $sql->select()->join(DB_PREFIX . 'content_types', array('type_id', 'id'), 'LEFT JOIN');
        $content = Table\Content::execute($sql->render(true), array('uri' => $uri));

        if (isset($content->rows[0])) {
            $contentMatch = null;
            $site = Table\Sites::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
            $siteId = (isset($site->id)) ? (int)$site->id : 0;
            foreach ($content->rows as $content) {
                if ((int)$content->site_id == $siteId) {
                    $this->data['allowed'] = self::isAllowed($content);
                    $contentValues = (array)$content;
                    $contentValues = array_merge($contentValues, FieldValue::getAll($content->id, true));
                    $this->data = array_merge($this->data, $contentValues);
                    $this->filterContent();
                }
            }
        }
    }

    /**
     * Get content by date method
     *
     * @param  array   $date
     * @return void
     */
    public function getByDate($date)
    {
        $this->data['date'] = $date['match'];
        $content = Table\Content::findByDate($date);

        if (empty($date['uri'])) {
            $results = $content->rows;
            foreach ($results as $key => $result) {
                if (self::isAllowed($result)) {
                    $fv = FieldValue::getAll($result->id, true);
                    if (count($fv) > 0) {
                        foreach ($fv as $k => $v) {
                            $results[$key]->{$k} = $v;
                        }
                    }
                } else {
                    unset($results[$key]);
                }
            }
            $this->data['results'] = $results;
        } else if (isset($content->id)) {
            $this->data['allowed'] = self::isAllowed($content);
            $contentValues = $content->getValues();
            $contentValues = array_merge($contentValues, FieldValue::getAll($content->id, true));
            $this->data = array_merge($this->data, $contentValues);
            $this->filterContent();
        }
    }

    /**
     * Search for content
     *
     * @param  \Pop\Http\Request $request
     * @return void
     */
    public function search($request)
    {
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
                $sql->select()->where()->like('title', ':title');
                $content = Table\Content::execute($sql->render(true), array('title' => '%' . $search['title'] . '%'));
                $results = $content->rows;
            }

            foreach ($this->data['keys'] as $key) {
                if (isset($search[$key]) && ($search[$key] != '')) {
                    $field = Table\Fields::findBy(array('name' => $key));
                    if (isset($field->id)) {
                        $sql = Table\FieldValues::getSql();
                        $sql->select(array(
                            DB_PREFIX . 'field_values.field_id',
                            DB_PREFIX . 'field_values.model_id',
                            DB_PREFIX . 'field_values.value'
                        ));
                        $sql->select()
                            ->where()
                            ->equalTo(DB_PREFIX . 'field_values.field_id', ':field_id')->like('value', ':value');

                        // Execute field values SQL
                        $fieldValues = Table\FieldValues::execute(
                            $sql->render(true),
                            array(
                                'field_id' => $field->id,
                                'value' => '%' . $search[$key] . '%'
                            )
                        );

                        // If field values are found, extrapolate the table class from the model class
                        if (isset($fieldValues->rows[0])) {
                            foreach ($fieldValues->rows as $fv) {
                                // If table class is found, find model object
                                if (!in_array($fv->model_id, $track)) {
                                    $cont = Table\Content::findById($fv->model_id);
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

            foreach ($results as $key => $result) {
                if (self::isAllowed($result)) {
                    $fv = FieldValue::getAll($result->id, true);
                    if (count($fv) > 0) {
                        foreach ($fv as $k => $v) {
                            $results[$key]->{$k} = $v;
                        }
                    }
                } else {
                    unset($results[$key]);
                }
            }

            $this->data['results'] = $results;
        }
    }

    /**
     * Get content by ID method
     *
     * @param  int     $id
     * @return void
     */
    public function getById($id)
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
            $content = Table\Content::findById($id);
            $roles = (null !== $content->roles) ? unserialize($content->roles) : array();

            if (isset($roles[0])) {
                $rolesAry = array();
                foreach ($roles as $rid) {
                    $rolesAry[] = $rid;
                }
                $contentValues['roles'] = $rolesAry;
            } else {
                $contentValues['roles'] = array();
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

            $contentValues['typeUri'] = $type->uri;
            $contentValues = array_merge($contentValues, FieldValue::getAll($id));

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

                $site = Table\Sites::getSite((int)$c->site_id);

                if (null !== $c->status) {
                    $uri   = $c['uri'];
                    $title = $c->title;
                    $description = '<![CDATA[<a href="http://' . $site->domain . $site->base_path . $uri . '">http://' . $site->domain . $site->base_path . $uri . '</a>]]>';
                } else {
                    $uri   = CONTENT_PATH . '/media/' . $c['uri'];
                    $fileIcon = self::getFileIcon($c['uri'], $site->document_root . $site->base_path);
                    $title = $c->title;
                    $description = '<![CDATA[<a href="http://' . $site->domain . $site->base_path . $uri . '"><img src="http://' . $site->domain . $site->base_path . CONTENT_PATH . $fileIcon['fileIcon'] . '" width="80" alt="' . $c['uri'] . '" /></a>]]>';
                }

                $entries[] = array(
                    'title'       => $c->title,
                    'link'        => 'http://' . $site->domain . $site->base_path. $uri,
                    'updated'     => $c['published'],
                    'summary'     => $title,
                    'description' => $description
                );
            }
        }

        return $entries;
    }

    /**
     * Method to get content breadcrumb
     *
     * @return string
     */
    public function getBreadcrumb()
    {
        $breadcrumb = $this->title;
        $pId = $this->parent_id;
        $sep = htmlentities($this->config->separator, ENT_QUOTES, 'UTF-8');

        while ($pId != 0) {
            $content = Table\Content::findById($pId);
            if (isset($content->id)) {
                $site = Table\Sites::getSite((int)$content->site_id);
                if ($content->status == self::PUBLISHED) {
                    $breadcrumb = '<a href="' . $site->base_path . $content->uri . '">' . $content->title . '</a> ' .
                        $sep . ' ' . $breadcrumb;
                }
                $pId = $content->parent_id;
            }
        }

        return $breadcrumb;
    }

    /**
     * Save content
     *
     * @param \Pop\Form\Form $form
     * @throws \Pop\File\Exception
     * @return void
     */
    public function save(\Pop\Form\Form $form)
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

        if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['error'] == 1)) {
            throw new \Pop\File\Exception("The file exceeds the PHP 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . ".");
        // If content is a file
        } else if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['tmp_name'] != '')) {
            $site = Table\Sites::getSite((int)$fields['site_id']);
            $dir = $site->document_root . $site->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
            $fileName = File::checkDupe($_FILES['uri']['name'], $dir);

            File::upload(
                $_FILES['uri']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                $this->config->media_max_filesize, $this->config->media_allowed_types
            );
            chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
            if (preg_match(self::$imageRegex, $fileName)) {
                self::processMedia($fileName, $this->config, $site->document_root . $site->base_path);
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
            'site_id'    => (int)$fields['site_id'],
            'type_id'    => $fields['type_id'],
            'parent_id'  => $parentId,
            'template'   => ((isset($fields['template']) && ($fields['template'] != '0')) ? $fields['template'] : null),
            'title'      => $title,
            'uri'        => $uri,
            'slug'       => $slug,
            'feed'       => (int)$fields['feed'],
            'force_ssl'  => ((isset($fields['force_ssl']) ? (int)$fields['force_ssl'] : null)),
            'status'     => ((isset($fields['status']) ? (int)$fields['status'] : null)),
            'roles'      => ((isset($fields['roles']) ? serialize($fields['roles']) : null)),
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

        // Save content navs
        if (isset($fields['navigation_id'])) {
            foreach ($fields['navigation_id'] as $nav) {
                $contentToNav = new Table\NavigationTree(array(
                    'navigation_id' => $nav,
                    'content_id'    => $content->id,
                    'order'         => (int)$_POST['navigation_order_' . $nav]
                ));
                $contentToNav->save();
            }
        }

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

        FieldValue::save($fields, $content->id);
    }

    /**
     * Update content
     *
     * @param \Pop\Form\Form $form
     * @throws \Pop\File\Exception
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $content   = Table\Content::findById($fields['id']);
        $oldSiteId = $content->site_id;
        $oldUri    = $content->uri;

        $parentId = null;
        $uri      = null;
        $slug     = null;
        $expired  = null;

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
            $expired = null;
        }

        // If content is a file
        if (!isset($fields['parent_id'])) {
            if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['error'] == 1)) {
                throw new \Pop\File\Exception("The file exceeds the PHP 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . ".");
            // If content is a file
            } else if (($_FILES) && isset($_FILES['uri']) && ($_FILES['uri']['tmp_name'] != '')) {
                $site = Table\Sites::getSite((int)$fields['site_id']);
                $dir = $site->document_root . $site->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
                self::removeMedia($content->uri, $site->document_root . $site->base_path);
                $fileName = File::checkDupe($_FILES['uri']['name'], $dir);
                File::upload(
                    $_FILES['uri']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                    $this->config->media_max_filesize, $this->config->media_allowed_types
                );
                chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
                if (preg_match(self::$imageRegex, $fileName)) {
                    self::processMedia($fileName, $this->config, $site->document_root . $site->base_path);
                }
                $title = ($fields['content_title'] != '') ?
                    $fields['content_title'] :
                    ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($fileName, 0, strrpos($fileName, '.'))));

                $uri = $fileName;
                $slug = $fileName;
            } else {
                $title = $fields['content_title'];
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

        $content->site_id    = (int)$fields['site_id'];
        $content->type_id    = $fields['type_id'];
        $content->parent_id  = $parentId;
        $content->template   = ((isset($fields['template']) && ($fields['template'] != '0')) ? $fields['template'] : null);
        $content->title      = $title;
        $content->uri        = $uri;
        $content->slug       = $slug;
        $content->feed       = (int)$fields['feed'];
        $content->force_ssl  = ((isset($fields['force_ssl']) ? (int)$fields['force_ssl'] : null));
        $content->status     = ((isset($fields['status']) ? (int)$fields['status'] : null));
        $content->roles      = ((isset($fields['roles']) ? serialize($fields['roles']) : null));
        $content->updated    = date('Y-m-d H:i:s');
        $content->published  = $published;
        $content->expired    = $expired;
        $content->updated_by = ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null);


        $content->update();
        $this->data['id'] = $content->id;
        $this->data['uri'] = $content->uri;

        if (!isset($fields['parent_id']) && ($oldSiteId != $content->site_id)) {
            $oldSite = Table\Sites::getSite((int)$oldSiteId);
            $newSite = Table\Sites::getSite((int)$content->site_id);

            if (file_exists($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri) &&
                !file_exists($newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri)) {
                rename(
                    $oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri,
                    $newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri
                );
                chmod($newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri, 0777);
            } else if (file_exists($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $oldUri)) {
                unlink($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $oldUri);
            }

            $dirs = new Dir($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');
            foreach ($dirs->getFiles() as $size) {
                if (is_dir($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size) &&
                    is_dir($newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR  . $size)) {
                    if (file_exists($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri) &&
                        !file_exists($newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri)) {
                        rename(
                            $oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri,
                            $newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri
                        );
                        chmod($newSite->document_root . $newSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri, 0777);
                    } else if (file_exists($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $oldUri)) {
                        unlink($oldSite->document_root . $oldSite->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR .$size . DIRECTORY_SEPARATOR .  $oldUri);
                    }
                }
            }
        }

        // Update content navs
        $contentToNavigation = Table\NavigationTree::findBy(array('content_id' => $content->id));
        foreach ($contentToNavigation->rows as $nav) {
            $contentToNav = Table\NavigationTree::findById(array($nav->navigation_id, $content->id, null));
            if (isset($contentToNav->content_id)) {
                $contentToNav->delete();
            }
        }

        if (isset($_POST['navigation_id'])) {
            foreach ($_POST['navigation_id'] as $nav) {
                $contentToNav = new Table\NavigationTree(array(
                    'content_id'    => $content->id,
                    'navigation_id' => $nav,
                    'order'         => (int)$_POST['navigation_order_' . $nav]
                ));
                $contentToNav->save();
            }
        }

        // Update content categories
        $contentToCategories = Table\ContentToCategories::findBy(array('content_id' => $content->id));
        foreach ($contentToCategories->rows as $cat) {
            $contentToCat = Table\ContentToCategories::findById(array($content->id, $cat->category_id));
            if (isset($contentToCat->content_id)) {
                $contentToCat->delete();
            }
        }

        if (isset($_POST['category_id'])) {
            foreach ($_POST['category_id'] as $cat) {
                $contentToCategory = new Table\ContentToCategories(array(
                    'content_id'  => $content->id,
                    'category_id' => $cat
                ));
                $contentToCategory->save();
            }
        }

        FieldValue::update($fields, $content->id);
    }

    /**
     * Copy content
     *
     * @return void
     */
    public function copy()
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
            'site_id'    => $this->data['site_id'],
            'type_id'    => $this->data['type_id'],
            'parent_id'  => $this->data['parent_id'],
            'template'   => $this->data['template'],
            'title'      => $title,
            'uri'        => $uri,
            'slug'       => $slug,
            'feed'       => $this->data['feed'],
            'force_ssl'  => $this->data['force_ssl'],
            'status'     => 0,
            'roles'      => (isset($this->data['roles']) ? serialize($this->data['roles']) : null),
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

        $values = Table\FieldValues::findAll(null, array('model_id' => $id));
        if (isset($values->rows[0])) {
            foreach ($values->rows as $value) {
                $field = Table\Fields::findById($value->field_id);
                if (isset($field->id) && ($field->type != 'file')) {
                    $val = new Table\FieldValues(array(
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

    /**
     * Process batch
     *
     * @throws \Pop\File\Exception
     * @return void
     */
    public function batch()
    {
        $batchErrors = array();

        // Check for global file setting configurations
        if ($_FILES) {
            $config = \Phire\Table\Config::getSystemConfig();
            $regex = '/^.*\.(' . implode('|', array_keys($config->media_allowed_types))  . ')$/i';

            foreach ($_FILES as $key => $value) {
                if (($_FILES) && isset($_FILES[$key]) && ($_FILES[$key]['error'] == 1)) {
                    throw new \Pop\File\Exception("A file exceeds the PHP 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . ".");
                } else if (!empty($value['name'])) {
                    if ($value['size'] > $config->media_max_filesize) {
                        $batchErrors[] = 'The file \'' . $value['name'] . '\' must be less than ' . $config->media_max_filesize_formatted . '.';
                    }
                    if (preg_match($regex, $value['name']) == 0) {
                        $type = strtoupper(substr($value['name'], (strrpos($value['name'], '.') + 1)));
                        $batchErrors[] = 'The ' . $type . ' file type is not allowed.';
                    }
                }
            }
        }

        $this->data['batchErrors'] = $batchErrors;

        if (count($batchErrors) == 0) {
            if ($_FILES) {
                $site = Table\Sites::getSite((int)$_POST['site_id']);
                $dir = $site->document_root . $site->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
                if (($_FILES) && isset($_FILES['archive_file']) && ($_FILES['archive_file']['error'] == 1)) {
                    throw new \Pop\File\Exception("The archive file exceeds the PHP 'upload_max_filesize' setting of " . ini_get('upload_max_filesize') . ".");
                } else if (!empty($_FILES['archive_file']) && ($_FILES['archive_file']['name'] != '')) {
                    mkdir($dir . DIRECTORY_SEPARATOR . 'tmp');
                    chmod($dir . DIRECTORY_SEPARATOR . 'tmp', 0777);

                    $archive = Archive::upload(
                        $_FILES['archive_file']['tmp_name'], $dir . DIRECTORY_SEPARATOR . $_FILES['archive_file']['name'],
                        $this->config->media_max_filesize, $this->config->media_allowed_types
                    );
                    $archive->setPermissions(0777);
                    $archive->extract($dir . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR);
                    $archive->delete();

                    if (stripos($_FILES['archive_file']['name'], '.tar') !== false) {
                        $filename = substr($_FILES['archive_file']['name'], 0, (strpos($_FILES['archive_file']['name'], '.tar') + 4));
                        if (file_exists($dir . DIRECTORY_SEPARATOR . $filename) && !is_dir($dir . DIRECTORY_SEPARATOR . $filename)) {
                            unlink($dir . DIRECTORY_SEPARATOR . $filename);
                        }
                    } else if ((stripos($_FILES['archive_file']['name'], '.tgz') !== false) ||
                               (stripos($_FILES['archive_file']['name'], '.tbz') !== false)) {
                        $filename = substr($_FILES['archive_file']['name'], 0, strpos($_FILES['archive_file']['name'], '.t')) . '.tar';
                        if (file_exists($dir . DIRECTORY_SEPARATOR . $filename) && !is_dir($dir . DIRECTORY_SEPARATOR . $filename)) {
                            unlink($dir . DIRECTORY_SEPARATOR . $filename);
                        }
                    }

                    $tmpDir = new Dir($dir . DIRECTORY_SEPARATOR . 'tmp', true, true, false);
                    $allowed = $this->config->media_allowed_types;

                    foreach ($tmpDir->getFiles() as $file) {
                        $pathParts = pathinfo($file);
                        if ((filesize($file) <= $this->config->media_max_filesize) && array_key_exists($pathParts['extension'], $allowed)) {
                            $fileName = File::checkDupe($pathParts['basename'], $dir);
                            copy($file, $dir . DIRECTORY_SEPARATOR . $fileName);
                            chmod($dir . DIRECTORY_SEPARATOR . $fileName, 0777);
                            if (preg_match(self::$imageRegex, $fileName)) {
                                self::processMedia($fileName, $this->config, $site->document_root . $site->base_path);
                            }
                            $content = new Table\Content(array(
                                'site_id'    => $_POST['site_id'],
                                'type_id'    => $_POST['type_id'],
                                'title'      => ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($fileName, 0, strrpos($fileName, '.')))),
                                'uri'        => $fileName,
                                'slug'       => $fileName,
                                'feed'       => 0,
                                'force_ssl'  => null,
                                'status'     => null,
                                'created'    => date('Y-m-d H:i:s'),
                                'updated'    => null,
                                'published'  => date('Y-m-d H:i:s'),
                                'expired'    => null,
                                'created_by' => ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null),
                                'updated_by' => null
                            ));

                            $content->save();
                        }
                    }

                    $tmpDir->emptyDir(null, true);
                }

                foreach ($_FILES as $key => $value) {
                    if (($key != 'archive_file') && ($value['name'] != '')) {
                        $id = substr($key, (strrpos($key, '_') + 1));
                        $fileName = File::checkDupe($value['name'], $dir);
                        $upload = File::upload(
                            $value['tmp_name'], $dir . DIRECTORY_SEPARATOR . $fileName,
                            $this->config->media_max_filesize, $this->config->media_allowed_types
                        );
                        $upload->setPermissions(0777);
                        if (preg_match(self::$imageRegex, $fileName)) {
                            self::processMedia($fileName, $this->config, $site->document_root . $site->base_path);
                        }

                        $title = ($_POST['file_title_' . $id] != '') ?
                            $_POST['file_title_' . $id] :
                            ucwords(str_replace(array('_', '-'), array(' ', ' '), substr($fileName, 0, strrpos($fileName, '.'))));

                        $content = new Table\Content(array(
                            'site_id'    => $_POST['site_id'],
                            'type_id'    => $_POST['type_id'],
                            'title'      => $title,
                            'uri'        => $fileName,
                            'slug'       => $fileName,
                            'feed'       => 0,
                            'force_ssl'  => null,
                            'status'     => null,
                            'created'    => date('Y-m-d H:i:s'),
                            'updated'    => null,
                            'published'  => date('Y-m-d H:i:s'),
                            'expired'    => null,
                            'created_by' => ((isset($this->user) && isset($this->user->id)) ? $this->user->id : null),
                            'updated_by' => null
                        ));

                        $content->save();
                    }
                }
            }
        }
    }

    /**
     * Process batch
     *
     * @param  array $post
     * @return void
     */
    public function process(array $post)
    {
        $process = (int)$post['content_process'];
        if (isset($post['process_content'])) {
            $open = $this->config('open_authoring');
            foreach ($post['process_content'] as $id) {
                $content = Table\Content::findById($id);
                $createdBy = null;
                if (isset($content->id)) {
                    $createdBy = $content->created_by;
                    if (!((!$open) && ($content->created_by != $this->user->id))) {
                        if ($process < 0) {
                            $type = Table\ContentTypes::findById($content->type_id);
                            if (isset($type->id) && (!$type->uri)) {
                                $site = Table\Sites::getSite((int)$content->site_id);
                                self::removeMedia($content->uri, $site->document_root . $site->base_path);
                            }
                            $content->delete();
                        } else {
                            $content->status = $process;
                            $content->update();
                        }
                    }
                }

                // If the Fields module is installed, and if there are fields for this form/model
                if (($process < 0) && !((!$open) && ($createdBy != $this->user->id))) {
                    FieldValue::remove($id);
                }
            }
        }
    }

    /**
     * Static method to process uploaded media
     *
     * @param string       $fileName
     * @param \ArrayObject $config
     * @param string       $docRoot
     * @return void
     */
    public static function processMedia($fileName, $config, $docRoot = null)
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
    public static function removeMedia($fileName, $docRoot = null)
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

