<?php

namespace Cyaneus\Helpers;
use PHPImageWorkshop\ImageWorkshop;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\Log;
use Cyaneus\Helpers\String;

class Factory
{

    /**
     * Build pages for the site
     * @param  Array   $pages  Configuration of each pages
     * @param  boolean $isPost Is it a post ?
     * @param  string  $ext    File extension
     * @throws RuntimeException If we cannot write the page
     */
    public static function make(Array $pages, $isPost = false, $ext = 'html')
    {
        if( !file_exists(Cyaneus::path()->site) ) {
            mkdir(Cyaneus::path()->site);
        }

        foreach ($pages as $pageName => $content) {

            Log::trace('Content found for '.$pageName);

            $file = Cyaneus::pages($pageName,$isPost,$ext);

            if( file_exists($file) ) {
                unlink($file);
            }

            if(!file_put_contents($file,$content)) {
                throw new \RuntimeException('Cannot write the file : '.$file);
            }
        }

        Log::trace('Make all the pages is a success');
    }

    /**
     * Parse a draft and extract the post content and its configuration
     * @param  String $file
     * @return Array       [config,raw]
     */
    public static function getContent($file)
    {
        $config = [];
        if( file_exists($file) ) {

            $content = file_get_contents($file);
            // We extract headers from the draft
            $config = strstr($content,'==POST==', true );
            // Remove headers from the draft to keep the content
            $article = str_replace('==POST==','',strstr($content,'==POST=='));

            Log::trace('Get content for '.$file);
            return [
                'config' => String::parseConfig($config),
                'raw' => $article
            ];
        }
        return [];
    }

    /**
     * Build pictures attach to a page
     * @param String $name   Name of the source image
     * @param String $source Source of the image
     * @param Array $config  Configuration for an image [width,heigth,crop]
     * @return bool
     */
    public static function picture($name, $source, Array $config)
    {
        try {
            Log::trace('Init creation of '.$name.' from '.$source);

            $width  = (isset($config['width'])) ? $config['width'] : Cyaneus::site('site')->thumb_w;
            $height = (isset($config['height'])) ? $config['height'] : null;
            $crop   = (isset($config['crop'])) ? $config['crop'] : false;
            $name   = $name.'.'.pathinfo($config['file'],PATHINFO_EXTENSION);

            if( file_exists(Cyaneus::path()->post.$name) ) {
                Log::trace('Image already exist :'.Cyaneus::path()->post.$name);
                return false;
            }

            // [0] => w ---- [1] => h
            $_info = getimagesize($source);

            $image = new ImageWorkshop(array(
                'imageFromPath' => $source,
            ));

            $image->resizeInPixel($width, $height, $crop);

            // (file_path,file_name,create_folder,background_color,quality)
            $image->save(Cyaneus::path()->post, $name, true, null, 85);

            unset($image);
            Log::trace('Image build');

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Initial setup for Cyaneus
     * Build them all
     */
    public static function buildPath()
    {

        $paths = ['base','logs','draft','site','post','pages'];

        foreach ($paths as $path) {

            if(!file_exists(Cyaneus::path()->$path)) {
                mkdir(Cyaneus::path()->$path);
            }
        }

        Log::trace('All the Cyaneus folders are built');
    }

    /**
     * Move custom elements to another directory
     * @param  Array  $files List of files and folder
     * @throws RuntimeException If Cannot move files
     */
    public static function moveFromTemplate(Array $files)
    {
        Log::trace('Move some files to the site. '.var_export($files,true));
        foreach ($files as $file) {

            if( !file_exists(Cyaneus::path()->template.$file) ) {
                continue;
            }

            // Build a folder, to prevent error during move
            if( pathinfo($file, PATHINFO_EXTENSION) === '' ) {

                if( !file_exists(Cyaneus::path()->site.$file) ) {
                    mkdir(Cyaneus::path()->site.$file);
                }

                $origin      = Cyaneus::path()->template.$file.DIRECTORY_SEPARATOR;
                $destination = Cyaneus::path()->site;
            }else {
                $origin      = Cyaneus::path()->template.$file;
                $destination = Cyaneus::path()->site.$file;
            }

            exec(escapeshellcmd('cp -r '.$origin.' '.$destination).' 2>&1', $cp_output, $cp_error);

             if($cp_output) {
                throw new \RuntimeException('An error has occurred with cp: '.var_export($cp_output, true));
            }
        }
    }

    /**
     * Move custom elements to another directory
     * @param  Array  $files List of files and folder
     * @throws RuntimeException If Cannot move files
     */
    public static function moveFromCore(Array $files)
    {
        Log::trace('Move some files to the site. '.var_export($files,true));
        foreach ($files as $type => $_file) {

            $_path = Cyaneus::path()->{'c'.$type.'s'};


           foreach ($_file as $file) {

                if( !file_exists($_path.$file) ) {
                    continue;
                }

                // Build a folder, to prevent error during move
                if( pathinfo($file, PATHINFO_EXTENSION) === '' ) {

                    if( !file_exists(Cyaneus::path()->site.$file) ) {
                        mkdir(Cyaneus::path()->site.$file);
                    }

                    $origin      = $_path.$file.DIRECTORY_SEPARATOR;
                    $destination = Cyaneus::path()->site;
                }else {
                    $origin      = $_path.$file;
                    $destination = Cyaneus::path()->site.$file;
                }

                exec(escapeshellcmd('cp -r '.$origin.' '.$destination).' 2>&1', $cp_output, $cp_error);

                 if($cp_output) {
                    throw new \RuntimeException('An error has occurred with cp: '.var_export($cp_output, true));
            }

           }
        }
    }

}
