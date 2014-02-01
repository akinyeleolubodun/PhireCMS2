<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Pop\File\File;
use Phire\Table;

class Site extends \Phire\Model\AbstractModel
{

    /**
     * Get all sites method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $sites = Table\Sites::findAll($order['field'] . ' ' . $order['order']);

        if ($this->data['acl']->isAuth('Phire\Controller\Config\SitesController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_sites[]" id="remove_sites[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_sites" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove')
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove'),
                'style' => 'display: none;'
            );
        }

        if ($this->data['acl']->isAuth('Phire\Controller\Config\SitesController', 'edit')) {
            $domain = '<a href="' . BASE_PATH . APP_URI . '/config/sites/edit/[{id}]">[{domain}]</a>';
        } else {
            $domain = '[{domain}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'sites-remove-form',
                'action'  => BASE_PATH . APP_URI . '/config/sites/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'            => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=id">#</a>',
                    'domain'        => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=domain">' . $this->i18n->__('Domain') . '</a>',
                    'document_root' => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=document_root">' . $this->i18n->__('Document Root') . '</a>',
                    'title'         => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=title">' . $this->i18n->__('Title') . '</a>',
                    'live'          => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=live">' . $this->i18n->__('Live') . '</a>',
                    'process'       => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0
            ),
            'exclude' => array(
                'force_ssl'
            ),
            'domain'   => $domain,
            'indent' => '        '
        );

        $siteAry = array();
        foreach ($sites->rows as $site) {
            $site->live = ($site->live == 1) ? $this->i18n->__('Yes') : $this->i18n->__('No');
            $siteAry[] = $site;
        }

        if (isset($siteAry[0])) {
            $this->data['table'] = Html::encode($siteAry, $options, $this->config->pagination_limit, $this->config->pagination_range);
        }
    }

    /**
     * Get site by ID method
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $site = Table\Sites::findById($id);
        if (isset($site->id)) {
            $siteValues = $site->getValues();
            $siteValues = array_merge($siteValues, FieldValue::getAll($id));
            $this->data = array_merge($this->data, $siteValues);
        }
    }

    /**
     * Save site
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function save(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $docRoot = ((substr($fields['document_root'], -1) == '/') && (substr($fields['document_root'], -1) == "\\")) ?
            substr($fields['document_root'], 0, -1) : $fields['document_root'];

        if ($fields['base_path'] != '') {
            $basePath = ((substr($fields['base_path'], 0, 1) != '/') && (substr($fields['base_path'], 0, 1) != "\\")) ?
                '/' . $fields['base_path'] : $fields['base_path'];

            if ((substr($basePath, -1) == '/') && (substr($basePath, -1) == "\\")) {
                $basePath = substr($basePath, 0, -1);
            }
        } else {
            $basePath = '';
        }

        $site = new Table\Sites(array(
            'domain'        => $fields['domain'],
            'document_root' => $docRoot,
            'base_path'     => $basePath,
            'title'         => $fields['title'],
            'force_ssl'     => (int)$fields['force_ssl'],
            'live'          => (int)$fields['live']
        ));

        $site->save();
        $this->data['id'] = $site->id;

        $user = Table\Users::findById($this->data['user']->id);
        $siteIds = unserialize($user->site_ids);
        $siteIds[] = $site->id;
        $user->site_ids = serialize($siteIds);
        $user->update();

        $sess = \Pop\Web\Session::getInstance();
        $sess->user->site_ids = $siteIds;

        FieldValue::save($fields, $site->id);

        $this->createFolders($docRoot, $basePath);

        // Copy any themes over
        $themes = Table\Extensions::findAll(null, array('type' => 0));
        if (isset($themes->rows[0])) {
            $themePath = $docRoot . $basePath . CONTENT_PATH . '/extensions/themes';
            foreach ($themes->rows as $theme) {
                if (!file_exists($themePath . '/' . $theme->name)) {
                    copy(
                        $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->file,
                        $themePath . '/' . $theme->file
                    );
                    $archive = new \Pop\Archive\Archive($themePath . '/' . $theme->file);
                    $archive->extract($themePath . '/');
                    if ((stripos($theme->file, 'gz') || stripos($theme->file, 'bz')) && (file_exists($themePath . '/' . $theme->name . '.tar'))) {
                        unlink($themePath . '/' . $theme->name . '.tar');
                    }
                }
            }
        }
    }

    /**
     * Update site
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function update(\Pop\Form\Form $form)
    {
        $form->filter('html_entity_decode', array(ENT_QUOTES, 'UTF-8'));
        $fields = $form->getFields();

        $site = Table\Sites::findById($fields['id']);

        $docRoot = ((substr($fields['document_root'], -1) == '/') && (substr($fields['document_root'], -1) == "\\")) ?
            substr($fields['document_root'], 0, -1) : $fields['document_root'];

        $oldDocRoot = $site->document_root;

        if ($fields['base_path'] != '') {
            $basePath = ((substr($fields['base_path'], 0, 1) != '/') && (substr($fields['base_path'], 0, 1) != "\\")) ?
                '/' . $fields['base_path'] : $fields['base_path'];

            if ((substr($basePath, -1) == '/') && (substr($basePath, -1) == "\\")) {
                $basePath = substr($basePath, 0, -1);
            }
        } else {
            $basePath = '';
        }

        $site->domain        = $fields['domain'];
        $site->document_root = $docRoot;
        $site->base_path     = $basePath;
        $site->title         = $fields['title'];
        $site->force_ssl     = (int)$fields['force_ssl'];
        $site->live          = (int)$fields['live'];

        $site->update();
        $this->data['id'] = $site->id;

        FieldValue::update($fields, $site->id);

        if ($oldDocRoot != $docRoot) {
            $this->createFolders($docRoot, $basePath);

            // Copy any themes over
            $themes = Table\Extensions::findAll(null, array('type' => 0));
            if (isset($themes->rows[0])) {
                $themePath = $docRoot . $basePath . CONTENT_PATH . '/extensions/themes';
                foreach ($themes->rows as $theme) {
                    if (!file_exists($themePath . '/' . $theme->name)) {
                        copy(
                            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->file,
                            $themePath . '/' . $theme->file
                        );
                        $archive = new \Pop\Archive\Archive($themePath . '/' . $theme->file);
                        $archive->extract($themePath . '/');
                        if ((stripos($theme->file, 'gz') || stripos($theme->file, 'bz')) && (file_exists($themePath . '/' . $theme->name . '.tar'))) {
                            unlink($themePath . '/' . $theme->name . '.tar');
                        }
                    }
                }
            }
        }
    }

    /**
     * Migrate sites
     *
     * @param \Pop\Form\Form $form
     * @return void
     */
    public function migrate($form)
    {
        $siteFromId = ($form->site_from == 'Main') ? 0 : (int)$form->site_from;
        $siteToId   = ($form->site_to == 'Main') ? 0 : (int)$form->site_to;

        $siteFrom         = Table\Sites::getSite($siteFromId);
        $siteFromDomain   = $siteFrom->domain;
        $siteFromDocRoot  = $siteFrom->document_root;
        $siteFromBasePath = (substr($siteFrom->base_path, 0, 1) == '/') ? substr($siteFrom->base_path, 1) : $siteFrom->base_path;

        $siteTo         = Table\Sites::getSite($siteToId);
        $siteToDomain   = $siteTo->domain;
        $siteToDocRoot  = $siteTo->document_root;
        $siteToBasePath = (substr($siteTo->base_path, 0, 1) == '/') ? substr($siteTo->base_path, 1) : $siteTo->base_path;

        if ($siteFromBasePath != '') {
            $search = array(
                'href="http://' . $siteFromDomain . '/' . $siteFromBasePath,
                'src="http://' . $siteFromDomain . '/' . $siteFromBasePath,
                'href="/' . $siteFromBasePath,
                'src="/' . $siteFromBasePath
            );
        } else {
            $search = array(
                'href="http://' . $siteFromDomain,
                'src="http://' . $siteFromDomain,
                'href="',
                'src="'
            );
        }
        if ($siteToBasePath != '') {
            $replace = array(
                'href="http://' . $siteToDomain . '/' . $siteToBasePath,
                'src="http://' . $siteToDomain . '/' . $siteToBasePath,
                'href="/' . $siteToBasePath,
                'src="/' . $siteToBasePath
            );
        } else {
            $replace = array(
                'href="http://' . $siteToDomain,
                'src="http://' . $siteToDomain,
                'href="',
                'src="'
            );
        }

        $contentFrom  = Table\Content::findAll(null, array('site_id' => $siteFromId));

        foreach ($contentFrom->rows as $content) {
            $migrate = true;
            if ($form->migrate != '----') {
                $type = $cType = Table\ContentTypes::findById($content->type_id);
                if (($form->migrate == 'URI') && (!$type->uri)) {
                    $migrate = false;
                } else if (($form->migrate == 'File') && ($type->uri)) {
                    $migrate = false;
                }
            }

            if ($migrate) {
                $newContentId = null;
                $c = Table\Content::findBy(array(
                    'site_id' => $siteToId,
                    'uri'     => $content->uri
                ));

                // If content object already exists under the "from site" with the same URI
                if (isset($c->id)) {
                    $newContentId = $c->id;
                    $c->title = $content->title;
                    $c->slug  = $content->slug;

                    // If content object is a file
                    if (substr($c->uri, 0, 1) != '/') {
                        $sizes = Table\Config::getMediaSizes();

                        if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $c->uri)) {
                            copy(
                                $siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $c->uri,
                                $siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $c->uri
                            );
                            chmod($siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $c->uri, 0777);
                        }

                        foreach ($sizes as $size) {
                            if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $c->uri)) {
                                copy(
                                    $siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $c->uri,
                                    $siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $c->uri
                                );
                                chmod($siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $c->uri, 0777);
                            }
                        }
                    }

                    $c->update();

                    $fv = Table\FieldValues::findAll(null, array('model_id' => $content->id));
                    if (isset($fv->rows[0])) {
                        foreach ($fv->rows as $f) {
                            $field = Table\Fields::findById($f->field_id);
                            if (isset($field->id) && ($field->type != 'file')) {
                                // Change out the site base path
                                if ((strpos($field->type, 'text') !== false) && ($siteFromBasePath != $siteToBasePath)) {
                                    $v = serialize(str_replace($search, $replace, unserialize($f->value)));
                                    if (null !== $f->history) {
                                        $history = unserialize($f->history);
                                        foreach ($history as $key => $value) {
                                            $history[$key] = str_replace($search, $replace, $value);
                                        }
                                        $h = serialize($history);
                                    } else {
                                        $h = $f->history;
                                    }
                                } else {
                                    $v = $f->value;
                                    $h = $f->history;
                                }

                                $newFv = Table\FieldValues::findBy(array('field_id' => $f->field_id, 'model_id' => $c->id), null, 1);
                                if (isset($newFv->field_id)) {
                                    $newFv->value     = $v;
                                    $newFv->timestamp = $f->timestamp;
                                    $newFv->history   = $h;
                                    $newFv->update();
                                } else {
                                    $newFv = new Table\FieldValues(array(
                                        'field_id'  => $f->field_id,
                                        'model_id'  => $c->id,
                                        'value'     => $v,
                                        'timestamp' => $f->timestamp,
                                        'history'   => $h
                                    ));
                                    $newFv->save();
                                }
                            }
                        }
                    }
                // Create new content object
                } else {
                    $oldParent   = Table\Content::findById($content->parent_id);
                    $newParentId = null;
                    if (isset($oldParent->id)) {
                        $newParent   = Table\Content::findBy(array('site_id' => $siteToId, 'uri' => $oldParent->uri));
                        $newParentId = (isset($newParent->id)) ? $newParent->id : null;
                    }

                    $newContent = new Table\Content(array(
                        'site_id'    => $siteToId,
                        'type_id'    => $content->type_id,
                        'parent_id'  => $newParentId,
                        'template'   => $content->template,
                        'title'      => $content->title,
                        'uri'        => $content->uri,
                        'slug'       => $content->slug,
                        'feed'       => $content->feed,
                        'force_ssl'  => $content->force_ssl,
                        'status'     => $content->status,
                        'roles'      => $content->roles,
                        'created'    => $content->created,
                        'updated'    => $content->updated,
                        'published'  => $content->published,
                        'expired'    => $content->expired,
                        'created_by' => $content->created_by,
                        'updated_by' => $content->updated_by
                    ));

                    $newContent->save();
                    $newContentId = $newContent->id;

                    // If content object is a file
                    if (substr($content->uri, 0, 1) != '/') {
                        $sizes = Table\Config::getMediaSizes();
                        $contentPath = $siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
                        $newUri = File::checkDupe($content->uri, $contentPath);

                        if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri)) {
                            copy(
                                $siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri,
                                $siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $newUri
                            );
                            chmod($siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $newUri, 0777);
                        }

                        foreach ($sizes as $size) {
                            if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri)) {
                                copy(
                                    $siteFromDocRoot . DIRECTORY_SEPARATOR . $siteFromBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri,
                                    $siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newUri
                                );
                                chmod($siteToDocRoot . DIRECTORY_SEPARATOR . $siteToBasePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newUri, 0777);
                            }
                        }

                        $newContent->uri = $newUri;
                        $newContent->update();
                    }

                    $fv = Table\FieldValues::findAll(null, array('model_id' => $content->id));
                    if (isset($fv->rows[0])) {
                        foreach ($fv->rows as $f) {
                            $field = Table\Fields::findById($f->field_id);
                            if (isset($field->id) && ($field->type != 'file')) {
                                // Change out the site base path
                                if ((strpos($field->type, 'text') !== false) && ($siteFromBasePath != $siteToBasePath)) {
                                    $v = serialize(str_replace($search, $replace, unserialize($f->value)));
                                    if (null !== $f->history) {
                                        $history = unserialize($f->history);
                                        foreach ($history as $key => $value) {
                                            $history[$key] = str_replace($search, $replace, $value);
                                        }
                                        $h = serialize($history);
                                    } else {
                                        $h = $f->history;
                                    }
                                } else {
                                    $v = $f->value;
                                    $h = $f->history;
                                }

                                $newFv = new Table\FieldValues(array(
                                    'field_id'  => $f->field_id,
                                    'model_id'  => $newContent->id,
                                    'value'     => $v,
                                    'timestamp' => $f->timestamp,
                                    'history'   => $h,
                                ));
                                $newFv->save();
                            }
                        }
                    }
                }

                if (null !== $newContentId) {
                    // Save any content categories
                    $cats = Table\ContentToCategories::findAll(null, array('content_id' => $content->id));
                    if (isset($cats->rows[0])) {
                        foreach ($cats->rows as $cat) {
                            $contentToCategory = new Table\ContentToCategories(array(
                                'content_id'  => $newContentId,
                                'category_id' => $cat->category_id
                            ));
                            $contentToCategory->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * Remove sites
     *
     * @param array $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_sites'])) {
            foreach ($post['remove_sites'] as $id) {
                $site = Table\Sites::findById($id);
                if (isset($site->id)) {
                    $content = Table\Content::findAll();
                    foreach ($content->rows as $content) {
                        if ($content->site_id == $site->id) {
                            $c = Table\Content::findById($content->id);
                            if (isset($c->id)) {
                                if (substr($c->uri, 0, 1) != '/') {
                                    \Phire\Model\Content::removeMedia($c->uri, $site->document_root . $site->base_path);
                                }
                                $c->delete();
                            }
                        }
                    }

                    $users = Table\Users::findAll();
                    foreach ($users->rows as $user) {
                        $siteIds = unserialize($user->site_ids);
                        if (in_array($site->id, $siteIds)) {
                            $key = array_search($site->id, $siteIds);
                            unset($siteIds[$key]);
                            $u = Table\Users::findById($user->id);
                            if (isset($u->id)) {
                                $u->site_ids = serialize($siteIds);
                                $u->update();
                            }
                        }
                    }

                    $site->delete();
                }
            }
        }
    }

    /**
     * Create site folders
     *
     * @param string $docRoot
     * @param string $basePath
     * @return void
     */
    protected function createFolders($docRoot, $basePath)
    {
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes');
        mkdir($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');

        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html',
            $docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html'
        );
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media', 0777);
        chmod($docRoot . $basePath . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html', 0777);
    }

}

