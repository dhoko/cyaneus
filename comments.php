<?php
require 'vendor/autoload.php';
// Define Cyaneus main path
define('CYANEUS_PATH',__DIR__.DIRECTORY_SEPARATOR);

// Init your application
Cyaneus\Cyaneus::init();
Cyaneus\Cyaneus::run();

if( !empty($_POST) && empty($_POST['about'])  && empty($_POST['info']) ) {

    $urlSource = $_SERVER['HTTP_REFERER'];
    $urlSource = parse_url($urlSource);
    $urlSource = trim(str_replace('/', '', $urlSource['path']));
    $post = array_map(function($value) {
        return htmlentities(trim($value));
    }, $_POST);

    if($urlSource !== $post['url']) {
        die('You cannot post this comment');
    }
    try {

        Cyaneus\Helpers\Log::trace('Try to record a new comment : '.var_export($post,true));

        $record = new Cyaneus\Storage\Csv\Comment($post['url']);
        $record->fill($post);
        $record->write();

        die(json_encode(['status' => 'success']));

    } catch (Exception $e) {
        Cyaneus\Helpers\Log::error($e->getMessage());
        die(json_encode(['status' => 'error']));
    }

}

if ( !empty($_GET['url']) ) {
    echo json_encode(Cyaneus\Storage\Csv\Comment::find(trim($_GET['url'])));
    die();
}
