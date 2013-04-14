<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

class Content extends \Pop\Mvc\Model
{

    /**
     * Instantiate the model object.
     *
     * @param  mixed  $data
     * @param  string $name
     * @return self
     */
    public function __construct($data = null, $name = null)
    {
        parent::__construct($data, $name);
    }

    /**
     * Get content by URI method
     *
     * @param  string
     * @return void
     */
    public function getByUri($uri)
    {
        $content = Table\Content::findBy(array('uri' => $uri));
        if(isset($content->id)) {
            $this->data = array_merge($this->data, $content->getValues());
            $this->data['code'] = 200;
            $this->data['template'] = Table\Config::findById('default_template')->value;
        } else {
            $site = Table\Sites::findById(6001);
            $this->data = array_merge($this->data, array(
                'code' => 404,
                'uri'  => $uri,
                'title' => $site->title . $site->separator . '404 Error : Page Not Found',
                'template' => Table\Config::findById('default_template')->value,
                'content' => $site->error
            ));
        }
    }

}

