<?php
class Build
{
    private $content;
    private $files;
    private $datetime;
    public function __construct()
    {
        $this->datetime = (new DateTime("now",new DateTimeZone("Europe/Paris")))->format('Y-m-d H:i:s');
        return $this;
    }

    public function setHook($name)
    {
        $name = ucfirst($name).'Listener';
        $hook = new $name(DRAFT);
        $hook->get();
        $this->files = $hook->files();
        return $this;
    }

    public function init() {

        $data = [];

        foreach ($this->files['post'] as $file => $fullPath) {
            $data[] = Factory::getContent($fullPath);
        }

        $this->content = $data;
        unset($data);
        return $this;
    }

    public function run() {
        var_dump($this->content);
    }


}
