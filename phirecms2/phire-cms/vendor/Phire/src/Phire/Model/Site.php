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
                'value' => 'Remove'
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
                    'id'                 => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=id">#</a>',
                    'domain'        => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=domain">Domain</a>',
                    'document_root' => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=document_root">Document Root</a>',
                    'title'         => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=title">Title</a>',
                    'live'               => '<a href="' . BASE_PATH . APP_URI . '/sites?sort=live">Live</a>',
                    'process'            => $removeCheckAll
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
            $site->live = ($site->live == 1) ? 'Yes' : 'No';
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

        $site = new Table\Sites(array(
            'domain'        => $fields['domain'],
            'document_root' => $fields['document_root'],
            'title'         => $fields['title'],
            'force_ssl'          => (int)$fields['force_ssl'],
            'live'               => (int)$fields['live']
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

        mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets');
        mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions');
        mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules');
        mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes');
        mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');

        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
            $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html',
            $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html',
            $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html',
            $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html',
            $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html'
        );
        copy(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html',
            $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html'
        );
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media', 0777);
        chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html', 0777);
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

        $oldDocRoot = $site->document_root;

        $site->domain        = $fields['domain'];
        $site->document_root = $fields['document_root'];
        $site->title         = $fields['title'];
        $site->force_ssl          = (int)$fields['force_ssl'];
        $site->live               = (int)$fields['live'];

        $site->update();
        $this->data['id'] = $site->id;

        FieldValue::update($fields, $site->id);

        if ($oldDocRoot != $fields['document_root']) {
            mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets');
            mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions');
            mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules');
            mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes');
            mkdir($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media');

            copy(
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html',
                $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html'
            );
            copy(
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html',
                $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html'
            );
            copy(
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html',
                $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html'
            );
            copy(
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html',
                $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html'
            );
            copy(
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html',
                $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html'
            );
            copy(
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html',
                $fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html'
            );
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'index.html', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'index.html', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'index.html', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'index.html', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'index.html', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media', 0777);
            chmod($fields['document_root'] . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'index.html', 0777);
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

        if ($siteFromId > 0) {
            $site = Table\Sites::findById($siteFromId);
            $siteFromDocRoot = $site->document_root;
        } else {
            $siteFromDocRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        if ($siteToId > 0) {
            $site = Table\Sites::findById($siteToId);
            $siteToDocRoot = $site->document_root;
        } else {
            $siteToDocRoot = $_SERVER['DOCUMENT_ROOT'];
        }

        $contentFrom  = Table\Content::findAll(null, array('site_id' => $siteFromId));

        foreach ($contentFrom->rows as $content) {
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
                    $contentPath = $siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
                    $newUri = File::checkDupe($c->uri, $contentPath);

                    if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $c->uri)) {
                        copy(
                            $siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $c->uri,
                            $siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $newUri
                        );
                        chmod($siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $newUri, 0777);
                    }

                    foreach ($sizes as $size) {
                        if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $c->uri)) {
                            copy(
                                $siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $c->uri,
                                $siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newUri
                            );
                            chmod($siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newUri, 0777);
                        }
                    }

                    $c->uri = $newUri;
                }

                $c->update();

                $fv = Table\FieldValues::findAll(null, array('model_id' => $content->id));
                if (isset($fv->rows[0])) {
                    foreach ($fv->rows as $f) {
                        $field = Table\Fields::findById($f->field_id);
                        if (isset($field->id) && ($field->type != 'file')) {
                            $newFv = Table\FieldValues::findBy(array('field_id' => $f->field_id, 'model_id' => $c->id), null, 1);
                            if (isset($newFv->field_id)) {
                                $newFv->value     = $f->value;
                                $newFv->timestamp = $f->timestamp;
                                $newFv->history   = $f->history;
                                $newFv->update();
                            } else {
                                $newFv = new Table\FieldValues(array(
                                    'field_id'  => $f->field_id,
                                    'model_id'  => $c->id,
                                    'value'     => $f->value,
                                    'timestamp' => $f->timestamp,
                                    'history'   => $f->history,
                                ));
                                $newFv->save();
                            }
                        }
                    }
                }
            // Create new content object
            } else {
                $newContent = new Table\Content(array(
                    'site_id'    => $siteToId,
                    'type_id'    => $content->type_id,
                    'parent_id'  => $content->parent_id,
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
                    $contentPath = $siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media';
                    $newUri = File::checkDupe($content->uri, $contentPath);

                    if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri)) {
                        copy(
                            $siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $content->uri,
                            $siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $newUri
                        );
                        chmod($siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $newUri, 0777);
                    }

                    foreach ($sizes as $size) {
                        if (file_exists($siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri)) {
                            copy(
                                $siteFromDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $content->uri,
                                $siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newUri
                            );
                            chmod($siteToDocRoot . DIRECTORY_SEPARATOR . BASE_PATH . DIRECTORY_SEPARATOR . CONTENT_PATH . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . $size . DIRECTORY_SEPARATOR . $newUri, 0777);
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
                            $newFv = new Table\FieldValues(array(
                                'field_id'  => $f->field_id,
                                'model_id'  => $newContent->id,
                                'value'     => $f->value,
                                'timestamp' => $f->timestamp,
                                'history'   => $f->history,
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
                                    \Phire\Model\Content::removeMedia($c->uri, $site->document_root);
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

}

