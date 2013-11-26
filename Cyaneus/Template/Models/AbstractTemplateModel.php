<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

abstract class AbstractTemplateModel
{
    protected $tags;
    protected $template;

    public function __construct(Array $config)
    {
        if( empty($config['tags']) ) {
            throw new \InvalidArgumentException('You must set a tags key with it\'s assiciative array');
        }

        if( empty($config['templates']) ) {
            throw new \InvalidArgumentException('You must set a template key with it\'s assiciative array');
        }

        $this->tags     = $config['tags'];
        $this->template = $config['template'];
    }

    abstract public function build();
    abstract private function configureTags(Array $tags);

    protected function getTags(Array $data)
    {
        return array_merge($this->tags,$data);
    }

    /**
    * Main configuration for Template's keys
    * These keys are available in a template
    * @param  Array  $info Default configuration For a post
    * @param  String $content Content for a post
    * @return Array       template keys
    */
    protected function getPostTags($info, $content)
    {
        if(!isset($info['last_update'])) {
            $info['last_update'] = $info['added_time'];
        }
        return $this->config([
            'post_url'             => Cyaneus::config('path')->postUrl.$info['url'].'.html',
            'post_title'           => $info['title'],
            'post_date'            => CDate::formated($info['added_time']),
            'post_lang'            => (isset($info['plang'])) ? $info['plang'] : $this->config['lang'],
            'post_update'          => CDate::formated($info['last_update']),
            'post_date_rss'        => CDate::rss($info['last_update']),
            'post_description'     => $info['description'],
            'post_content'         => $content,
            'post_author'          => $info['author'],
            'post_tags'            => $info['tags'],
            'post_timestamp'       => $info['added_time'],
            'post_timestamp_up'    => $info['last_update'],
            'post_timestamp_upRaw' => CDate::timestamp($info['last_update']),
            'navigation'       => (isset($info['navigation'])) ? $info['navigation'] : '',
        ]);
    }
}
