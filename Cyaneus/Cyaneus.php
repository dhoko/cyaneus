<?php

namespace Cyaneus;
use Cyaneus\Helpers\Factory;

class Cyaneus
{
    private static $config = [];

    /**
    * Init Cyaneus - it will build required folders :
    * - Site destination folder
    * - Post destination folder
    * - Move your CSS template to your site folder
    */
    public static function init($env = '')
    {
        require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.$env.'.php';

        self::$config['site'] = $cyaneus;
        self::$config['path'] = self::buildPathConfig($cyaneus);

        Factory::buildPath();
    }

    /**
     * Tu use your site's configuration
     * @return StdClass
     */
    public static function app()
    {
        return (object) self::$config['site'];
    }

    /**
     * Path's configuration
     * @return StdClass
     */
    public static function path()
    {
        return (object) self::$config['path'];
    }

    /**
     * Build the specific configuration for the site
     * each path and urls
     * @param  Array  $config
     * @return Array
     */
    private static function buildPathConfig(Array $config)
    {
        $base     = CYANEUS_PATH.$config['folder_main_path'];
        $url       = $config['url'];
        $site      = $base.DIRECTORY_SEPARATOR;
        $postPath  = $site.$config['articles'];
        $pagesPath = $site.$config['pages'];
        // Some paths for custom Cyaneus Resources
        $resources = __DIR__.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR;

        return [
            'base'          => $base,
            'logs'          => CYANEUS_PATH.'data'.DIRECTORY_SEPARATOR,
            'draft'         => CYANEUS_PATH.'data'.DIRECTORY_SEPARATOR.$config['draft'].DIRECTORY_SEPARATOR,
            'template'      => CYANEUS_PATH.$config['template'].DIRECTORY_SEPARATOR.$config['template_name'].DIRECTORY_SEPARATOR,
            'repositoryUrl' => $config['repositoryUrl'],
            'post'          => $postPath.DIRECTORY_SEPARATOR,
            'pages'          => $pagesPath.DIRECTORY_SEPARATOR,
            'url'           => $url,
            'postUrl'       => $url.$config['articles'].DIRECTORY_SEPARATOR,
            'pageUrl'       => $url.$config['pages'].DIRECTORY_SEPARATOR,
            'site'          => $site,
            'css'           => $url.'style.css',
            'rss'           => $url.'rss.xml',
            'sitemap'       => $url.'sitemap.xml',
            'resources'     => $resources,
            'ctemplate'     => $resources.DIRECTORY_SEPARATOR.'Template'.DIRECTORY_SEPARATOR,

        ];
    }

    /**
     * Get the path of a page or a post
     * @param  String  $path name of the page
     * @param  boolean $post Is it a post ?
     * @return String        Path
     */
    public static function pages($path, $post = false, $ext = 'html')
    {
        if($post) {
            $path = self::path()->post.$path;
        }else{
            $path = self::path()->site.$path;
        }

        return $path.'.'.$ext;
    }

    public static function postUrl($post)
    {
        return self::path()->postUrl.$post.'.html';
    }

    public static function pageUrl($page)
    {
        return self::path()->url.$page.'.html';
    }

    /**
     * Determine if an IP is within a specific range.
     * @param  String  $ip     Current request IP
     * @param  Array   $ranges Array of ranges or IP
     * @return boolean
     */
    public function ipValidator($ip, Array $ranges)
    {
        foreach ($ranges as $range) {
            if(ip_in_range($ip, $range)) return true;
            continue;
        }
        return false;
    }

}
