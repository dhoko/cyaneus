<?php
class Factory
{
    /**
     * Build pages for the site
     * @param  Array  $pages Configuration of each pages
     * @throws RuntimeException If we cannot write the page
     */
    public static function make(Array $pages)
    {
        if( !file_exists(Cyaneus::config('path')->site) ) {
            mkdir(Cyaneus::config('path')->site);
        }

        foreach ($pages as $pageName => $content) {

            $ext = 'html';
            // Posts are not associative
            if( !is_string($pageName) ) {

                $pageName = $content['config']['url'];
                $content  = $content['html'];
                $file     = Cyaneus::pages($pageName,1,$ext);
            }else {

                if($pageName === 'rss' || $pageName === 'sitemap') {
                    $ext = 'xml';
                }

                $file = Cyaneus::pages($pageName,0,$ext);
            }

            Log::trace('Content found for '.$pageName);

            if( file_exists($file) ) {
                unlink($file);
            }

            if(!file_put_contents($file,$content)) {
                throw new RuntimeException('Cannot write the file : '.$file);
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
                'config' => String::getTags($config),
                'raw' => $article
            ];
        }
        return [];
    }

    /**
     * Build attachement picture for a post
     * @param Array $config Configuration for an image
     * @return bool
     */
    public static function picture(Array $config)
    {
        if(!empty($config)) {

            klog('Find an image attach to the current post');
            // [0] => w ---- [1] => h
            $_info = getimagesize(DRAFT.DIRECTORY_SEPARATOR.$config['path']);
            $image = new PHPImageWorkshop\ImageWorkshop(array(
                    'imageFromPath' => DRAFT.DIRECTORY_SEPARATOR.$config['path'],
            ));

            if (THUMB_W < $_info[0]) {
                $image->resizeInPixel(THUMB_W, null, true);
            }else{
                $image->resizeInPixel($_info[0], null, true);
            }
             //backgroundColor transparent, only for PNG (otherwise it will be white if set null)
            klog('Record file config '.var_export($config,true));
            klog('Record file '.STORE.FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.POST.DIRECTORY_SEPARATOR.$config['basename']);
            // (file_path,file_name,create_folder,background_color,quality)
            return $image->save(STORE.FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.POST.DIRECTORY_SEPARATOR, $config['basename'], true, null, 85);
        }
    }

    /**
     * Initial setup for Cyaneus
     * Build them all
     */
    public static function buildPath()
    {
        if(!file_exists(Cyaneus::config('path')->base)) {
            mkdir(Cyaneus::config('path')->base);
        }
        if(!file_exists(Cyaneus::config('path')->logs)) {
            mkdir(Cyaneus::config('path')->logs);
        }
        if(!file_exists(Cyaneus::config('path')->draft)) {
            mkdir(Cyaneus::config('path')->draft);
        }
        if(!file_exists(Cyaneus::config('path')->site)) {
            mkdir(Cyaneus::config('path')->site);
        }
        if(!file_exists(Cyaneus::config('path')->post)) {
            mkdir(Cyaneus::config('path')->post);
        }
        Log::trace('All the Cyaneus folders are built');
    }

    /**
     * Move custom elements to another directory
     * @param  Array  $files List of files and folder
     * @throws RuntimeException If Cannot move files
     */
    public static function move(Array $files)
    {
        Log::trace('Move some files to the site. '.var_export($files,true));
        foreach ($files as $file) {

            if( !file_exists(Cyaneus::config('path')->template.$file) ) {
                continue;
            }

            // Build a folder, to prevent error during move
            if( pathinfo($file, PATHINFO_EXTENSION) === '' ) {

                if( !file_exists(Cyaneus::config('path')->site.$file) ) {
                    mkdir(Cyaneus::config('path')->site.$file);
                }

                $origin      = Cyaneus::config('path')->template.$file.DIRECTORY_SEPARATOR;
                $destination = Cyaneus::config('path')->site;
            }else {
                $origin      = Cyaneus::config('path')->template.$file;
                $destination = Cyaneus::config('path')->site.$file;
            }

            exec(escapeshellcmd('cp -r '.$origin.' '.$destination).' 2>&1', $cp_output, $cp_error);

             if($cp_output) {
                throw new RuntimeException('An error has occurred with cp: '.var_export($cp_output, true));
            }
        }

    }

}
