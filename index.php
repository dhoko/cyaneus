<?php 
/**
 * Init Cyaneus configuration
 * From array to constant
 */
require 'config.php';
define('USERDATA', dirname(__FILE__).DIRECTORY_SEPARATOR.'data');
define('STORE', dirname(__FILE__).DIRECTORY_SEPARATOR);

foreach ($cyaneus as $key => $value) {

	if(in_array($key, ['draft','articles','template'])) {
		continue;
	}
	define(strtoupper($key),$value,true);
}

define('DRAFT',dirname(__FILE__).DIRECTORY_SEPARATOR.$cyaneus['draft']);
define('POST',$cyaneus['articles']);
define('TEMPLATEPATH', STORE.$cyaneus['template'].DIRECTORY_SEPARATOR.TEMPLATE_NAME.DIRECTORY_SEPARATOR);
define('REPOSITORY',STORE.FOLDER_MAIN_PATH);
define('RSS',URL.'rss.xml');
define('CSS',URL.'style.css');
define('SITE', dirname(__FILE__).DIRECTORY_SEPARATOR.'site');

/**
 * Log fonction it builds 3 files :
 *  - log.txt
 *  - log_error.txt
 *  - log_server.txt
 * Files are in USERDATA -> data/
 * @param  String $msg  Message to log
 * @param  string $type Type of message
 */
function klog($msg,$type="") {
	$name = 'log';
	if($type === "error") $name = 'log_error';
	if($type === "server") $name = 'log_server';
	file_put_contents(USERDATA.DIRECTORY_SEPARATOR.$name.'.txt',date('Y-m-d H:i:s').' '.$msg."\n",FILE_APPEND);
}

require 'core'.DIRECTORY_SEPARATOR.'includes.php';

/**
 * Github Hook page. url?github
 */
if(isset($_GET['github'])) {
	
	// Init application - build folders if they do not exist
	Cyaneus::init();

	// Github post data in ['payload key']
	if(empty($_POST['payload'])) {
		klog('Invalid Request this hook from Github','error');
		klog(var_export($_POST,true),'server');
		echo json_encode([
			'status' => 'error',
			'msg' => 'Invalid Request'
			]);
		exit();
	}


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