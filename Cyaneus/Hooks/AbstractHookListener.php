<?php
namespace Cyaneus\Hooks;
use Cyaneus\Cyaneus;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \ZipArchive;
/**
*   Hook listener
*/
abstract class AbstractHookListener
{
    private $name;
    private $path;

    /**
     * Init a hook by its name and where he has to be looking
     * for content
     * @param String $name Hook name - lowercase
     * @param String $path path to iterate
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Should get your hook content
     */
    abstract public function get();

    /**
     * Find all files generated after a hook
     *  -> [post => 'fileName'=>fullpath,media=>[fullpath]]
     * @return Array
     */
    public function files()
    {
        $_files = ['post' => [], 'media' => []];
        $iterator = new RecursiveDirectoryIterator($this->path,RecursiveIteratorIterator::CHILD_FIRST);

        foreach (new RecursiveIteratorIterator($iterator) as $file) {

            if($file->isFile()) {

                $folder = pathinfo($file->getpath());

                if( !in_array($file->getExtension(), ["md",'markdown']) ) {
                    $_files['media'][$file->getfilename()] = Cyaneus::config('path')->draft.$folder['basename'].DIRECTORY_SEPARATOR.$file->getfilename();
                }else {
                    $_files['post'][$file->getfilename()] = Cyaneus::config('path')->draft.$folder['basename'].DIRECTORY_SEPARATOR.$file->getfilename();
                }

            }
        }

        // If you keep it in the repository
        if(isset($_files['post']['README.md'])) {
            unset($_files['post']['README.md']);
        }

        return $_files;
    }

    /**
     * Extract a Zipball if your hook download a zip
     * Extract it to self::$path
     * @param  String $file
     */
    protected function extract($file)
    {
        $archive = new ZipArchive();
        $open = $archive->open($file);

        if($open != true) {
            throw new \Exception('Cannot open '.$file.' with unzip(): Erreur '.$open);
        }

        if(! $archive->extractTo($this->path)) {
            throw new \Exception('Cannot extract '.$file.' to '.$destination.' with unzip()');
        }

        if(! $archive->close()) {
            throw new \Exception('Cannot open '.$file.' with unzip()');
        }

        // Remove the zip at the end
        unlink($file);
    }
}
