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
        // $this->template = array(
        //     'index' => array(
        //         'main'    => file_get_contents(Cyaneus::config('path')->template.'index.html'),
        //         'content' => file_get_contents(Cyaneus::config('path')->template.'content-index.html'),
        //         ),
        //     'post' => file_get_contents(Cyaneus::config('path')->template.'post.html'),
        //     'archives' => array(
        //         'main'    => file_get_contents(Cyaneus::config('path')->template.'index.html'),
        //         'content' => file_get_contents(Cyaneus::config('path')->template.'content-index.html'),
        //         ),
        //     'rss' => array(
        //         'main'    => file_get_contents(Cyaneus::config('path')->template.'rss.html'),
        //         'content' => file_get_contents(Cyaneus::config('path')->template.'content-rss.html'),
        //         ),
        //     'navigation' => file_get_contents(Cyaneus::config('path')->template.'navigation.html')
        //      );
    }


    // private function navigation()
    // {
    //     return String::replace(self::config(), $this->template['navigation']);
    // }



    public function pages(Array $posts, Array $pages)
    {
        $render = [];

        foreach ($pages as $page) {

            $_page = new Models\Page([
                'tags'      => $this->config(),
                'templates' => [
                    'main'    => file_get_contents(Cyaneus::config('path')->template.$page.'.html'),
                    'content' => file_get_contents(Cyaneus::config('path')->template.'content-'.$page.'.html')
                ]
            ]);

            $_page->setPosts($posts);
            $_page->setPages($page);
            $render[$page] = $_page->build();
            unset($_page);
        }

        return $render;
    }

    public function rss(Array $posts, Array $pages)
    {
        $rss = new Models\Rss([
            'tags'      => $this->config(),
            'templates' => [
                'main'    => file_get_contents(Cyaneus::config('path')->ctemplate.'rss.xml'),
                'content' => file_get_contents(Cyaneus::config('path')->ctemplate.'rss-content.xml')
            ]
        ]);

        $rss->setPosts($posts);
        $rss->setPages($pages);
        $render = $rss->build();

        unset($rss);
        return $render;
    }

    public function sitemap(Array $posts, Array $pages)
    {
        $sitemap = new Models\Sitemap([
            'tags'      => $this->config(),
            'templates' => [
                'main'    => file_get_contents(Cyaneus::config('path')->ctemplate.'sitemap.xml'),
                'content' => file_get_contents(Cyaneus::config('path')->ctemplate.'sitemap-content.xml')
            ]
        ]);

        $sitemap->setPosts($posts);
        $sitemap->setPages($pages);
        $render = $sitemap->build();

        unset($sitemap);
        return $render;
    }

    public function posts(Array $posts)
    {
        $_posts = new Models\Post([
            'tags'      => $this->config(),
            'templates' => [
                'main' => file_get_contents(Cyaneus::config('path')->template.'post.html'),
            ]
        ]);

        $_posts->setPosts($posts);
        $render = $_posts->build();

        unset($_posts);
        return $render;
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
