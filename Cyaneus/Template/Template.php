<?php
namespace Cyaneus\Template;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\Factory;
use Cyaneus\Template\Models as Models;

/**
* Main class to build generate pages from templates
* @todo Load each template file with the construct to prevent too many file request
*/
class Template
{
    private $nav;
    private $config;

    /**
     * Build our basic configuration for a template, such as default config var and template string
     * @param Array $config Cyaneus COnfig
     */
    public function __construct(Array $config)
    {
        $this->config = $config;
        $this->nav = $this->navigation();
    }

    /**
     * Build the site navigation
     * You must have a navigation.html in the template folder
     * @return String HTML
     */
    private function navigation()
    {
        $nav = new Models\Navigation([
                'tags'      => $this->config(),
                'templates' => [
                    'main'    => file_get_contents(Cyaneus::config('path')->template.'navigation.html'),
                ]
            ]);
        $render = $nav->build();
        unset($nav);
        return $render;
    }

    /**
     * Build the HTML for each pages
     * @param  Array  $posts List of posts
     * @param  Array  $pages List of page to build (array of string)
     * @return Array        [page => HTML]
     */
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
            $_page->setNavigation($this->nav);
            $_page->setPosts($posts);
            $_page->setPages($page);
            $render[$page] = $_page->build();
            unset($_page);
        }

        return $render;
    }

    /**
     * Build the RSS
     * @param  Array  $posts List of posts
     * @param  Array  $pages List of page to build (array of string)
     * @return String   XML
     */
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

    /**
     * Build the sitemap
     * @param  Array  $posts List of posts
     * @param  Array  $pages List of page to build (array of string)
     * @return String XML
     */
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

    /**
     * Build the sitemap and the RSS
     * @param  Array  $posts List of posts
     * @param  Array  $pages List of page to build (array of string)
     * @return Array  [sitemap,rss]
     */
    public function xmlPages(Array $posts, Array $pages = array('index','archives'))
    {
        return [
            'rss'     => $this->rss($posts, $pages),
            'sitemap' => $this->sitemap($posts, $pages),
            ];
    }

    /**
     * Build all the posts
     * @param  Array  $posts List of posts
     * @return Array [post=>HTML]
     */
    public function posts(Array $posts)
    {
        $_posts = new Models\Post([
            'tags'      => $this->config(),
            'templates' => [
                'main' => file_get_contents(Cyaneus::config('path')->template.'post.html'),
            ]
        ]);

        $_posts->setNavigation($this->nav);
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
            'site_lang'        => $this->config['language'],
            'site_url'         => $this->config['url'],
            'site_title'       => $this->config['name'],
            'site_description' => $this->config['description'],
            'site_generator'   => $this->config['generator'],
            'site_author'      => $this->config['author'],
            'site_template'    => $this->config['template_name'],
            'site_rss_url'     => Cyaneus::config('path')->rss,
            'site_css_url'     => Cyaneus::config('path')->css,
            ),$data);
        return $merge;
    }

    /**
    * Attach images to a post and build the HTML syntaxe
    * @param  Array  $pictures List of picture
    * @return Array
    */
    public function attachPictures(Array $pictures)
    {
     $_pict    = [];
     $template = file_get_contents(Cyaneus::config('path')->ctemplate.'picture.html');

     foreach ($pictures as $name => $params) {

         $params['name'] = $name;
         $params['ext'] = pathinfo($params['file'],PATHINFO_EXTENSION);

         $pict = new Models\Picture([
             'tags'      => $params,
             'templates' => [
                 'main' => $template,
             ]
         ]);

         $render = $pict->build();
         unset($pict);
         $picture_name = $name.'.'.pathinfo($params['file'],PATHINFO_EXTENSION);
         $_pict['picture_'.$name] = $render;
     }

     return $_pict;
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
