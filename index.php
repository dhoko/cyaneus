<?php
// include_once 'vendor/Markdown/markdown.php';
require 'vendor/Markdown/smartypants.php';
require 'vendor/Php-Markdown-Extended/markdown.php';
require 'vendor/Php-Markdown-Extended/markdown_extended.php';
require 'vendor/ip_in_range/ip_in_range.php';
require 'core/CDate.php';
require 'core/Log.php';
require 'core/String.php';
require 'core/Cyaneus.php';
require 'core/Hooks/AbstractHookListener.php';
require 'core/Hooks/GithubListener.php';
require 'core/Factory.php';
require 'core/Template.php';
require 'core/Build.php';

// Define Cyaneus main path
define('CYANEUS_PATH',__DIR__.DIRECTORY_SEPARATOR);

/**
 * Github Hook page. url?github
 */
if(isset($_GET['github'])) {

    try {

        if( !Cyaneus::ipValidator($_SERVER['REMOTE_ADDR'],['192.30.252.0/22']) ){
            throw new RuntimeException('Invalid access for IP : '$_SERVER['REMOTE_ADDR']);
        }

        Cyaneus::init();
        $build = (new Build())->setHook('github')->init()->run();
        die('Build done');

    } catch (Exception $e) {
        Log::server($e->getMessage());
        die('You shall not pass !!!'); // GG Gandalf
    }

}
