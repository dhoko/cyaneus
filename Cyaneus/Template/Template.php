<?php
namespace Cyaneus\Template;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;
use Cyaneus\Helpers\Factory;
use Cyaneus\Helpers\String;
use Cyaneus\Template\Models as Models;

/**
* Main class to build generate pages from templates
*/
class Template
{
    private $template = [];
    private $config;

    /**
     * Build our basic configuration for a template, such as default config var and template string
     * @param Array $config Cyaneus COnfig
     */
    public function __construct(Array $config)
    {
        $this->config = $config;
        $this->template = array(
            'index' => array(
                'main'    => file_get_contents(Cyaneus::config('path')->template.'index.html'),
                'content' => file_get_contents(Cyaneus::config('path')->template.'content-index.html'),
                ),
            'post' => file_get_contents(Cyaneus::config('path')->template.'post.html'),
            'archives' => array(
                'main'    => file_get_contents(Cyaneus::config('path')->template.'index.html'),
                'content' => file_get_contents(Cyaneus::config('path')->template.'content-index.html'),
                ),
            'rss' => array(
                'main'    => file_get_contents(Cyaneus::config('path')->template.'rss.html'),
                'content' => file_get_contents(Cyaneus::config('path')->template.'content-rss.html'),
                ),
            'navigation' => file_get_contents(Cyaneus::config('path')->template.'navigation.html')
             );
    }


    private function navigation()
    {
        return String::replace(self::config(), $this->template['navigation']);
    }

    /**
     * Build a post
     * @param String $context Template to build
     * @param Array  $data Options of data to bind
     * @return String Template with datas
     */
    public function post(Array $data)
    {
        $_content = '';
        $content  = $this->template['post'];
        $data['config']['navigation'] = $this->navigation();
        $data['config'] = array_merge($this->config, $this->buildKeyTemplate($data['config'], $data['html']));

        if($content){
            return String::replace($data['config'],$content);
        }
    }

    /**
     * Build loop element such as content on a home page
     * @param String $context Template to build
     * @param Array  $data Options of data to bind
     * @return String Template with datas
     */
    public function loop($context,Array $data)
    {

        $data    = array_merge($this->config, $data);
        $content = $this->template[$context]['content'];

        if($content){
            return String::replace($data,$content);
        }
    }

    public function pages(Array $config)
    {
        if(empty($config)) {
            throw new \RuntimeException('We cannot build pages without a config');
        }

        return $this->page($config);
    }


    /**
     * Build a page
     * @param String $context Template to build
     * @param Array  $data Options of data to bind
     * @return String Template with datas
     */
    public function page(Array $data)
    {
        $_pages = [];
        $_data  = [];

        $pages = array_keys($this->template);

        foreach ($pages as $page) {

            if($page === 'navigation' || $page === 'post') {
                continue;
            }
            $_data['content'] = '';
            foreach ($data as $post) {

                $_data['content'] .= $this->loop($page,$this->buildKeyTemplate($post['config'],$post['html']));
                $_data['navigation'] = $this->navigation();

            }
            $_tmp = $this->config($_data);
            $_pages[$page] = String::replace($_tmp,$this->template[$page]['main']);

        }

        return $_pages;
    }


    /**
    * Main configuration for Template's keys
    * These keys are available in a template
    * @param  Array $info Default configuration
    * @return Array       template keys
    */
    private function buildKeyTemplate($info, $content)
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




    public function sitemap(Array $post, Array $pages)
    {
        $sitemap = new Models\Sitemap([
            'tags'      => $this->config(),
            'templates' => [
                'main'    => file_get_contents(Cyaneus::config('path')->ctemplate.'sitemap.xml'),
                'content' => file_get_contents(Cyaneus::config('path')->ctemplate.'sitemap-content.xml')
            ]
        ]);

        $sitemap->setPosts($post);
        $sitemap->setPages($pages);
        return $sitemap->build();
    }

    /**
     * Build configuration from tge default one
     * @param Array  $data Options of data to bind
     * @return Array Configuration var to bind
     */
    private function config(Array $data = array())
    {
        $merge = array_merge(array(
            'site_lang'        => Cyaneus::config('site')->language,
            'site_url'         => Cyaneus::config('site')->url,
            'site_title'       => Cyaneus::config('site')->name,
            'site_description' => Cyaneus::config('site')->description,
            'site_generator'   => Cyaneus::config('site')->generator,
            'site_author'      => Cyaneus::config('site')->author,
            'site_template'    => Cyaneus::config('site')->template_name,
            'site_rss_url'     => Cyaneus::config('path')->rss,
            'site_css_url'     => Cyaneus::config('path')->css,
            ),$data);
        return $merge;
    }

    /**
     * Move custom elements from the template
     * Default :
     *     - style.css
     *     - images
     */
    public function moveCustom()
    {
        Factory::move(['style.css','images', 'scripts']);
    }
}
