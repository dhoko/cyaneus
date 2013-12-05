<?php

namespace Cyaneus;
use Cyaneus\Cyaneus;
use Cyaneus\Template\Template;
use Cyaneus\Helpers\Log;
use Cyaneus\Helpers\CDate;
use Cyaneus\Helpers\String;
use Cyaneus\Helpers\Factory;

/**
 * Cyaneus Build method
 * It will compile the site for you
 */
class Build
{
    /**
     * Store the content and info about each posts
     * @var Array
     */
    private $content;

    /**
     * Store the files found
     * @var Array
     */
    private $files;

    /**
     * Store the configuration of medias found
     * @var Array
     */
    private $medias;

    /**
     * Init the build process, and set a datetime
     * @return Build   Build instance
     */
    public function __construct()
    {
        define('CYANEUS_DATETIME',CDate::datetime());
        return $this;
    }

    /**
     * Allow you to find content from a Hook
     * @param String $name lowercase of your hook
     * @return Build   Build instance
     */
    public function setHook($name)
    {
        try {
            $name = 'Cyaneus\Hooks\\'.ucfirst($name).'Listener';
            $hook = new $name(Cyaneus::config('path')->draft);

            Log::trace('Init a new Hook '.$name);

            $hook->get();
            $this->files = $hook->files();

            return $this;

        } catch (Exception $e) {
            Log::server($e->getMessage());
            die('Cannot get your posts');
        }
    }

    /**
     * Build {self::$content} - Parse each files in order to build them
     * @return Build   Build instance
     */
    public function init()
    {
        try {
            $data   = [];
            $_media = [];

            foreach ($this->files['post'] as $file => $fullPath) {

                $config = [];
                $config['config'] = [];

                // Define a custom page format if your file name begin with Page
                if( strtolower(substr($file, 0,4)) === 'page' ) {
                    $config['type'] = 'page';
                    $config['config']['added_time'] = substr($file, 5,15);
                }else {
                    $config['config']['added_time'] = substr($file, 0,10);
                }

                $config = array_merge($config, Factory::getContent($fullPath));

                $data[] = $config;

                // Find all the medias you can
                if( !empty($config['config']['picture']) ) {
                    $_media = $_media + $config['config']['picture'];
                }
            }

            $this->content = $data;
            $this->medias  = $_media;

            unset($data);
            unset($_media);

            return $this;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            die('Init content error');
        }
    }

    /**
     * Build each medias associate to a page
     */
    private function media()
    {
        foreach ($this->medias as $name => $params) {

            if( isset($this->files['media'][$params['file']]) ) {
                Factory::picture($name, $this->files['media'][$params['file']], $params);
            }
            continue;
        }
    }

    /**
     * Attach images to a post and build the HTML syntaxe
     * @param  Array  $pictures List of picture
     * @return Array
     */
    private function attachPictures(Array $pictures)
    {
        $_pict = [];

        if( !empty($pictures) ) {

            foreach ($pictures as $name => $params) {

                $picture_name = $name.'.'.pathinfo($params['file'],PATHINFO_EXTENSION);
                $description  = (isset($params['description'])) ? $params['description'] : '';
                $_pict['picture_'.$name] = String::convert(String::pict2Markdown($picture_name, $description));
            }
        }
        return $_pict;
    }

    /**
     * Build Them all
     * @return Build   Build instance
     */
    public function run()
    {
        try {
            $posts    = [];
            $pages    = [];
            $template = new Template((array) Cyaneus::config('site'));

            foreach ($this->content as $post) {

                $post['config']['picture'] = $this->attachPictures($post['config']['picture']);

                if( !isset($post['type']) && $post['type'] !== 'page' ) {

                    $posts[] = [
                        'config' => $post['config'],
                        'text'   => String::convert($post['raw']),
                    ];
                }else {
                    $pages[] = [
                        'config' => $post['config'],
                        'text'   => String::convert($post['raw']),
                    ];
                }

            }

            $template->moveCustom();

            Factory::make($template->singlePages($pages));
            Factory::make($template->pages($posts,['index','archives']));
            Factory::make($template->posts($posts),true);
            Factory::make($template->xmlPages($posts),false, 'xml');

            $this->media();

            unset($pages);
            unset($posts);
            unset($template);
            die('Build done');

        } catch (Exception $e) {
            Log::error($e->getMessage());
            die('Build error');
        }
    }
}
