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
            $data = [];
            foreach ($this->files['post'] as $file => $fullPath) {

                $config = Factory::getContent($fullPath);
                $config['config']['added_time'] = substr($file, 0,10);
                $data[] = $config;
                $config = [];
            }

            $this->content = $data;
            unset($data);

            dd($this->files);
            return $this;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            die('Init content error');
        }
    }

    /**
     * Build Them all
     * @return Build   Build instance
     */
    public function run()
    {
        try {
            $posts    = [];
            $template = new Template((array) Cyaneus::config('site'));

            foreach ($this->content as $post) {
                $posts[] = [
                    'config' => $post['config'],
                    'text'   => String::convert($post['raw']),
                ];
            }

            $template->moveCustom();

            Factory::make($template->pages($posts,['index','archives']));
            Factory::make($template->posts($posts),true);
            Factory::make($template->xmlPages($posts),false, 'xml');

            unset($posts);
            unset($template);
            die('Build done');

        } catch (Exception $e) {
            Log::error($e->getMessage());
            die('Build error');
        }
    }
}
