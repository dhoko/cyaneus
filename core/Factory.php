<?php
class Factory
{
    /**
     * Build pages for the site
     * @param  Array  $pages Configuration of each pages
     */
    public static function make(Array $pages)
    {
        // var_dump($pages); die();
        if( !file_exists(Cyaneus::config('path')->site) ) {
            mkdir(Cyaneus::config('path')->site);
        }

        foreach ($pages as $pageName => $content) {

            // Posts are not associative
            if( !is_string($pageName) ) {

                $pageName = $content['config']['url'];
                $content  = $content['html'];
                $file     = Cyaneus::pages($pageName,1);
            }else {
                $file = Cyaneus::pages($pageName);
            }


            // var_dump($file);
            // var_dump($content);
            Log::trace('Content found for '.$pageName);

            if( file_exists($file) ) {
                unlink($file);
            }

            file_put_contents($file,$content);
        }

        Log::trace('Make all the pages is a success');
    }

    /**
     * Drop all compiled files from your site in order to rebuild it
     * @return bool
     */
    public static function drop()
    {
        klog('Drop project site');
        $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(
                            REPOSITORY,
                            FilesystemIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );

        $ext = ['css','xml','html','htm','jpg','png'.'jpeg','webp','gif','bmp'];

        foreach($files as $file) {
            if(!$file->isFile()) continue;

            if(in_array($file->getExtension(), $ext)) {
                klog('Remove file : '.$file->getRealPath());
                unlink($file->getRealPath());
            }
        }

        unlink(USERDATA.DIRECTORY_SEPARATOR.'cyaneus.sqlite');
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
                'config' => self::getTags($config),
                'raw' => $article
            ];
        }
        return [];
    }

    /**
     * Loop on each TAGS in order to build an array [tag:value]
     * @param string Header from a post
     * @return Array [tag:value]
     */
    private static function getTags($post)
    {
        $info = [];
        $kiwi_tags = explode(',', Cyaneus::config('site')->tags);

        foreach ($kiwi_tags as $tag) {
            $info[$tag] = self::info($post,$tag);
        }

        // Rebuild some informations
        if(empty($info['url'])) {
            $info['url'] = self::url($info['title']);
        }

        return $info;
    }

    /**
     * Find tags from a post from its header.
     * info('author="dhoko"','author') => dhoko
     * @param string Header of a post
     * @param string Tag tag to find cf TAGS
     * @return string tag value
     */
    private static function info($data,$tag)
    {
        preg_match('/"([^"]+)"/',strstr($data,$tag),$match);
        return (isset($match[1])) ? $match[1] : '';
    }

    /**
     * Convert raw content to HTML
     * @param  string $data   Your draft
     * @param  string $format convertion format
     * @return string         html
     * @todo add convertion format
     */
    public static function convert($data,$format = 'markdown')
    {
        return SmartyPants(Markdown($data));
    }

    /**
     * Build a valid url from a title
     * New Firefox OS app : XBMC remote -> new-firefox-os-app-xbmc-remote
     * @param string
     * @return string
     */
    public static function url($path)
    {
        $url = str_replace('&', '-and-', $path);
        $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
        $url = str_replace(' ', '-', $url);
        $url = str_replace('--', '-', $url);
        return strtolower($url);
    }

    public static function pictures(Array $content)
    {
        foreach ($content as $picture) {
            self::picture((array)$picture);
        }
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

}
