<?php
// include_once 'vendor/Markdown/markdown.php';
include_once 'vendor/Markdown/smartypants.php';
require 'vendor/Php-Markdown-Extended/markdown.php';
require 'vendor/Php-Markdown-Extended/markdown_extended.php';
include('core/CDate.php');
include('core/Log.php');
include('core/String.php');
include('core/Cyaneus.php');
include('core/Hooks/AbstractHookListener.php');
include('core/Hooks/GithubListener.php');
include('core/Factory.php');
include('core/Template.php');
include('core/Build.php');


define('CYANEUS_PATH',__DIR__.DIRECTORY_SEPARATOR);

Cyaneus::init();
$build = (new Build())->setHook('github')->init()->run();
die('Build done');


/**
 * Github Hook page. url?github
 */
if(isset($_GET['github'])) {


    // Hook API init (default Github)
    $hook =  new Hook();

    // Data for this hook
    $hook->init(json_decode($_POST['payload'],true));

    // Filter IP - Is the request is valid or not ?
    $validIp = $hook->isValidIp($_SERVER['REMOTE_ADDR'],['204.232.175.64/27', '192.30.252.0/22', '127.0.0.1/27']);

    if($validIp) {
        try {

            // Is it from my commit ?
            $hook->validate([
                'pusher' => [
                    'key' => 'email',
                    'value' => EMAIL_GIT,
                    'msg' => 'Wrong email for this Github Hook'
                ]
            ]);

            // Yup - let's go
            $run = $hook->run();

            if($run['status'] != 'success')
                throw new Exception('Github Hook - Could not write content');

        } catch (Exception $e) {
            klog($e->getMessage(),'error');
        }
    }else {
        // You should not pass !
        klog('Invalid IP : '.$_SERVER['REMOTE_ADDR'].' for this hook from Github','error');
    }
}

/**
 * Rebuild the site
 */
if( !empty($_GET['rebuild']) ) {

    // Encode each string to compare
    if(htmlspecialchars(REBUILD_KEY,ENT_QUOTES) === htmlspecialchars(trim($_GET['rebuild']),ENT_QUOTES)) {

        klog('REBUILD : Access for this IP : '.$_SERVER['REMOTE_ADDR']);
        Cyaneus::rebuild();

    }else{
        klog('REBUILD : Access denied for this IP : '.$_SERVER['REMOTE_ADDR'],'error');
        klog('REBUILD : Access denied with this password : '.trim($_GET['rebuild']),'error');
    }

}
