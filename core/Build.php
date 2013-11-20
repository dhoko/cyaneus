<?php
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
     * A DateTime for logs
     * @var String
     */
    private $datetime;

    /**
     * Init the build process, and set a datetime
     * @return Build   Build instance
     */
    public function __construct()
    {
        $this->datetime = (new DateTime("now",new DateTimeZone("Europe/Paris")))->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Allow you to find content from a Hook
     * @param String $name lowercase of your hook
     * @return Build   Build instance
     */
    public function setHook($name)
    {
        $name = ucfirst($name).'Listener';
        $hook = new $name(DRAFT);
        $hook->get();
        $this->files = $hook->files();
        return $this;
    }

    /**
     * Build {self::$content} - Parse each files in order to build them
     * @return Build   Build instance
     */
    public function init()
    {
        $data = [];

        foreach ($this->files['post'] as $file => $fullPath) {
            $data[] = Factory::getContent($fullPath);
        }

        $this->content = $data;
        unset($data);
        return $this;
    }

    /**
     * Build Them all
     * @return Build   Build instance
     */
    public function run()
    {
        var_dump($this->content);
    }


}
