<?php
class Cyaneus {

    private $config = [];

    /**
    * Init Cyaneus - it will build required folders :
    * - Site destination folder
    * - Post destination folder
    * - Move your CSS template to your site folder
    */
    public static function init() {

        $config = require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';

        self::$config['site'] = $config;
        self::$config['path'] = self::buildPathConfig($config);
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

        return (object) self::$config;
    }

    /**
     * Build the specific configuration for the site
     * each path and urls
     * @param  Array  $config
     * @return Array
     */
    private static function buildPathConfig(Array $config) {

        $base = __DIR__.DIRECTORY_SEPARATOR.$config['folder_main_path'];
        $url  = $config['url'].'/';

        return [
            'logs'          => $base.$config['logs'].DIRECTORY_SEPARATOR,
            'draft'         => $base.$config['draft'].DIRECTORY_SEPARATOR,
            'template'      => $base.$config['template'].DIRECTORY_SEPARATOR,
            'repositoryUrl' => $base.$config['repositoryUrl'],
            'url'           => $url,
            'site'          => $base.$config['site'].DIRECTORY_SEPARATOR,
            'css'           => $url.'css.css',
            'rss'           => $url.'rss.xml',
            'sitemap'       => $url.'sitemap.xml',
            'page'          => function ($path) use ($site) {
                return $site.DIRECTORY_SEPARATOR.$path.'html';
            },
            'post'          => function ($path) use ($site, $postPath) {
                return $site.DIRECTORY_SEPARATOR.$postPath.DIRECTORY_SEPARATOR.$path.'html';
            }
        ];
    }

}
