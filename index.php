<?php
// include_once 'vendor/Markdown/markdown.php';
// require 'vendor/Markdown/smartypants.php';
require 'vendor/Php-Markdown-Extended/markdown.php';
require 'vendor/Php-Markdown-Extended/markdown_extended.php';
require 'vendor/ip_in_range/ip_in_range.php';
require 'vendor/PhpImageWorkshop/ImageWorkshop.php';

require 'vendor/autoload.php';
// Define Cyaneus main path
define('CYANEUS_PATH',__DIR__.DIRECTORY_SEPARATOR);

// Init your application
Cyaneus\Cyaneus::init();

/**
 * Github Hook page. url?github=password
 */
if(isset($_GET['github']) && trim($_GET['github']) === Cyaneus\Cyaneus::app()->hook_password) {

    // Run your application
    Cyaneus\Cyaneus::run();

    try {

        if( !Cyaneus\Cyaneus::ipValidator($_SERVER['REMOTE_ADDR'],['192.30.252.0/22']) ){
            throw new \RuntimeException('Invalid access for IP : '.$_SERVER['REMOTE_ADDR']);
        }

        $build = (new Cyaneus\Build())->setHook('github')->init()->run();


    } catch (\Exception $e) {
        Cyaneus\Helpers\Log::server($e->getMessage());
        die('You shall not pass !!!'); // GG Gandalf
    }

}else{
    die('You shall not pass !!!');
}
