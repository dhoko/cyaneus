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
    protected function getTags(Array $data)
    {
        return array_merge($this->tags,$data);
    }
}
