<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
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

