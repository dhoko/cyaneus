<?php
class Cyaneus {

    private static $config = [];

    /**
    * Init Cyaneus - it will build required folders :
    * - Site destination folder
    * - Post destination folder
    * - Move your CSS template to your site folder
    */
    public static function init() {

        require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';

        self::$config['site'] = $cyaneus;
        self::$config['path'] = self::buildPathConfig($cyaneus);

        Factory::buildPath();
    }

    /**
     * Tu use your site's configuration
     * @param  string $about What do you want
     * @return Array
     */
    public static function config($about) {

        if( !isset(self::$config[$about]) ) {
            throw new Exception('Cannot find your configuration : '.$about);
        }

        return (object) self::$config[$about];
    }

    /**
     * Build the specific configuration for the site
     * each path and urls
     * @param  Array  $config
     * @return Array
     */
    private static function buildPathConfig(Array $config) {

        $base     = CYANEUS_PATH.$config['folder_main_path'];
        $url      = $config['url'].'/';
        $site     = $base.DIRECTORY_SEPARATOR;
        $postPath = $site.$config['articles'];

        return [
            'base'          => $base,
            'logs'          => CYANEUS_PATH.'data'.DIRECTORY_SEPARATOR,
            'draft'         => CYANEUS_PATH.$config['draft'].DIRECTORY_SEPARATOR,
            'template'      => CYANEUS_PATH.$config['template'].DIRECTORY_SEPARATOR.$config['template_name'].DIRECTORY_SEPARATOR,
            'repositoryUrl' => $config['repositoryUrl'],
            'post'          => $postPath.DIRECTORY_SEPARATOR,
            'url'           => $url,
            'postUrl'       => $url.$config['articles'].DIRECTORY_SEPARATOR,
            'site'          => $site,
            'css'           => $url.'css.css',
            'rss'           => $url.'rss.xml',
            'sitemap'       => $url.'sitemap.xml'
        ];
    }

    /**
     * Get the path of a page or a post
     * @param  String  $path name of the page
     * @param  boolean $post Is it a post ?
     * @return String        Path
     */
    public static function pages($path, $post = false) {

        if($post) {
            $path = self::config('path')->post.$path;
        }else{
            $path = self::config('path')->site.$path;
        }

        return $path.'.html';
    }

}
