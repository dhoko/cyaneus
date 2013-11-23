<?php
/**
* Main class to build generate pages from templates
*/
class Template
{
    private $template = [];
    private $config = [];

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



    /**
     * Replace var in a template from an array [key=>value]
     * @param Array $opt Options of data to bind
     * @param String $string Template string
     * @return String Template with datas
     */
    private function replace(Array $opt, $string)
    {
        if(empty($string)) {
            throw new Exception("Cannot fill an empty string");
        }

        $_data = array();
        foreach ($opt as $key => $value) {
            $_data['{{'.$key.'}}'] = $value;
        }
        return strtr($string,$_data);
    }

    private function navigation()
    {
        return $this->replace($this->config, $this->template['navigation']);
    }

    /**
     * Build a post
     * @param String $context Template to build
     * @param Array  $data Options of data to bind
     * @return String Template with datas
     */
    public function post(Array $data)
    {
        $_content           = '';
        $content            = $this->template['post'];
        $data['navigation'] = $this->navigation();

        array_merge($this->config, $this->buildKeyTemplate($data));

        if($content){
            return $this->replace($data,$content);
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
            return $this->replace($data,$content);
        }
    }

    public function pages(Array $config)
    {
        if(empty($config)) {
            throw new Exception('We cannot build pages without a config');
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

            if($page === 'navigation') {
                continue;
            }

            foreach ($config as $post) {

                $_data['content'] .= $this->loop('',$post['config']);
                $_data['navigation'] = $this->navigation();

            }
            $_tmp = array_merge($this->config, $_data);
            $_pages[$page] = $this->replace($_tmp,$this->template[$page]['main']);
        }

        return $_pages;
    }


    /**
    * Main configuration for Template's keys
    * These keys are available in a template
    * @param  Array $info Default configuration
    * @return Array       template keys
    */
    private function buildKeyTemplate($info) {
        return [
            'post_url'         => POST.DIRECTORY_SEPARATOR.$info['url'].".html",
            'post_title'       => $info['title'],
            'post_date'        => (new DateTime($info['added_time']))->format(DATE_FORMAT),
            'post_update'      => (new DateTime($info['last_update']))->format(DATE_FORMAT),
            'post_date_rss'    => date('D, j M Y H:i:s \G\M\T',(new DateTime($info['last_update']))->format('U')),
            'post_description' => $info['description'],
            'post_content'     => $info['content'],
            'post_author'      => $info['author'],
            'post_tags'        => $info['tags'],
            'timestamp'        => $info['added_time'],
            'timestamp_up'     => $info['last_update'],
            'timestamp_upRaw'  => (new DateTime($info['last_update']))->format('U'),
        ];
    }



    public function sitemap(Array $data)
    {
        $header = '<?xml version="1.0" encoding="UTF-8"?><!-- generator="'.GENERATOR.'" -->';
        $header .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $url = function ($data) {
            // var_dump(date('c',$data['timestamp_upRaw'])); exit();
            $path = URL.$data['post_url'];
            $update = date('c',$data['timestamp_upRaw']);
            $freq = (isset($data['type']) && $data['type'] === 'page') ? 'daily' : 'monthly';
            $priority = (isset($data['type']) && $data['type'] === 'page') ? '0.6' : '0.2';
            if($data['post_url'] === 'index.html') {
                $priority = '1.0';
                $path = URL;
            }
            $url = '<url>'."\n";
            $url .= "\t".'<loc>%s</loc>'."\n";
            $url .= "\t".'<lastmod>%s</lastmod>'."\n";
            $url .= "\t".'<changefreq>%s</changefreq>'."\n";
            $url .= "\t".'<priority>%.1f</priority>'."\n";
            $url .= '</url>';
            return sprintf($url,$path,$update,$freq,$priority);
        };
        foreach ($data as $element) {
            $header .= "\n".$url($element);
        }

        $header .= "\n".'</urlset>';
        return $header;
    }

    /**
     * Build configuration from tge default one
     * @param Array  $data Options of data to bind
     * @return Array Configuration var to bind
     */
    private function config(Array $data)
    {
        $merge = array_merge(array(
            'lang'             => LANGUAGE,
            'site_url'         => URL,
            'site_title'       => NAME,
            'site_description' => DESCRIPTION,
            'generator'        => GENERATOR,
            'author'           => AUTHOR,
            'template'         => TEMPLATE_NAME,
            'rss_url'          => RSS,
            'css_url'          => CSS,
            ),$data);
        return $merge;
    }
}
