<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;
use Cyaneus\Helpers\String;

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
     * List all the posts you have to parse
     * @var Array
     */
    protected $posts;

    /**
     * The navigation template
     * @var String
     */
    protected $navigation;

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
     * @param Mixed $pages (Array of pages or just the page)
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * Add navigation to the model
     * @param String template
     */
    public function setNavigation($navigation)
    {
        $this->navigation = $navigation;
    }

    /**
     * Add posts to the model
     * @param Array $pages
     */
    public function setposts(Array $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Build the template
     * @return String The HTML for a template
     */
    abstract public function build();


    /**
     * Bind each params to a template
     * @param  String $template Raw template
     * @param  Array  $tags     Associative array
     * @return String           HTML
     */
    protected function bindParams($template, Array $tags)
    {
        return String::replace($tags, $template);
    }

    /**
     * Merge the tags you build for a page with the default tags for the site
     * @param  Array  $data Your tags as an associative array
     * @return Array
     */
    protected function getTags(Array $data = array())
    {
        return array_merge($this->tags,$data);
    }

    /**
    * Main configuration for Template's keys
    * These keys are available in a template
    * @param  Array  $info Default configuration For a post
    * @param  String $content Content for a post
    * @return Array       template keys
    * @todo Move this method into it's own object
    */
    protected function buildCustomtags($config, $content)
    {
        if(!isset($config['last_update'])) {
            $config['last_update'] = $config['added_time'];
        }
        return [
            'post_url'             => Cyaneus::postUrl($config['url']),
            'post_title'           => $config['title'],
            'post_date'            => CDate::formated($config['added_time']),
            'post_lang'            => (isset($config['plang'])) ? $config['plang'] : $this->tags['lang'],
            'post_update'          => CDate::formated($config['last_update']),
            'post_date_rss'        => CDate::rss($config['last_update']),
            'post_description'     => $config['description'],
            'post_content'         => $content,
            'post_author'          => $config['author'],
            'post_tags'            => $config['tags'],
            'post_timestamp'       => $config['added_time'],
            'post_timestamp_up'    => $config['last_update'],
            'post_timestamp_upRaw' => CDate::timestamp($config['last_update']),
            'navigation'       => (isset($config['navigation'])) ? $config['navigation'] : '',
        ];
    }
}
