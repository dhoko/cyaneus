<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

/**
 * Abstract class for a model
 * It provide some inteligence for models
 */
abstract class AbstractTemplateModel
{
    /**
     * Store the site's tags
     * @var Array
     */
    protected $tags;

    /**
     * Store templates for a model
     * @var Array
     */
    protected $template;

    /**
     * List all the pages you have to parse
     * @var Array
     */
    protected $pages;

    /**
     * Build a model for a page
     * @param Array $config [tags=>[],pages=>[],template=>[]]
     * @throws InvalidArgumentException If a key is missing
     */
    public function __construct(Array $config)
    {
        if( empty($config['tags']) ) {
            throw new \InvalidArgumentException('You must set a tags key with it\'s assiciative array');
        }

        if( empty($config['templates']) ) {
            throw new \InvalidArgumentException('You must set a template key with it\'s assiciative array');
        }

        $this->tags     = $config['tags'];
        $this->template = $config['templates'];
    }

    /**
     * Add pages to the model
     * @param Array $pages
     */
    public function setPages(Array $pages)
    {
        $this->pages = $pages;
    }

    /**
     * Build the template
     * @return String The HTML for a template
     */
    abstract public function build();

    /**
     * Merge the tags you build for a page with the default tags for the site
     * @param  Array  $data Your tags as an associative array
     * @return Array
     */
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
