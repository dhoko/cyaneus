<?php 
/**
 * Main config
 */
include_once ('lib'.DIRECTORY_SEPARATOR.'template.php');
include_once ('lib'.DIRECTORY_SEPARATOR.'markdown.php');
include_once ('lib'.DIRECTORY_SEPARATOR.'smartypants.php');
include_once ('lib'.DIRECTORY_SEPARATOR.'ImageWorkshop.php');

/**
 * Log each step of the generation
 * USERDATA
 * 		- log.txt Step by step informations
 * 		- log_server.txt $_SERVER dump
 * 		- log_error.txt error
 */
function klog($msg,$type="") {
	$name = 'log';
	if($type === "error") $name = 'log_error';
	if($type === "server") $name = 'log_server';
	file_put_contents(USERDATA.$name.'.txt',date('Y-m-d H:i:s').' '.$msg."\n",FILE_APPEND);
}
/**
 * Init Kiwi - It will create user's config folder with some usefull files such as list of each articles etc...
 */
function init() {
	require 'config.php';
	$cyaneus['rss'] = $cyaneus['url'].'rss.xml';
	$cyaneus['css'] = $cyaneus['url'].'style.css';
	$GLOBALS['cyaneus'] = $cyaneus;

	define('TEMPLATEPATH', $cyaneus['template'].DIRECTORY_SEPARATOR.$cyaneus['template_name'].DIRECTORY_SEPARATOR);

	$data_folder = dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
	$list_article = $data_folder.'articles.json';

	define('USERDATA', $data_folder);

	if(!file_exists($data_folder)) mkdir($data_folder,0705);
	// Store an empty string base 64
	if(!file_exists($list_article)) file_put_contents($list_article,base64_encode(json_encode(array())));
	$GLOBALS['archives'] = json_decode(base64_decode(file_get_contents($list_article)),true);
	klog('INIT - Cyaneus loaded');
}
/**
 * Find tags from a post from its header.
 * info('author="dhoko"','author') => dhoko
 * @param string Header of a post
 * @param string Tag tag to find cf TAGS
 * @return string tag value
 */
function info($data,$tag) {
	preg_match('/"([^"]+)"/',strstr($data,$tag),$match);
	return (isset($match[1])) ? $match[1] : '';
}
/**
 * Build a valid url from a title
 * New Firefox OS app : XBMC remote -> new-firefox-os-app-xbmc-remote
 * @param string 
 * @return string
 */
function url($path) {
    $url = str_replace('&', '-and-', $path);
    $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
    $url = str_replace(' ', '-', $url);
    $url = str_replace('--', '-', $url);
    return strtolower($url);
}
/**
 * Will find each drafts from DRAFT. 
 * File must have these extensions : md|markdown
 * @return Array array of ['build':timestamp,file,path]
 */
function getDrafts() {
	$files          = array(); 
	$readable_draft = array('md','markdown');
	$draftPath      = dirname(__FILE__).DIRECTORY_SEPARATOR.$GLOBALS['cyaneus']['draft'].DIRECTORY_SEPARATOR;
	$iterator       = new RecursiveDirectoryIterator($draftPath,RecursiveIteratorIterator::CHILD_FIRST);

	klog('Looking for drafts');
	foreach(new RecursiveIteratorIterator($iterator) as $file) {
		if($file->isFile()) {
			$md5 = md5($file->getPath());
			if (in_array($file->getExtension(), $readable_draft)) {
				$files[$md5]['draft'] = array(
					'build' => $file->getMTime(),
					'file'  => $file->getfilename(),
					'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
				);
			}
			if( in_array($file->getExtension(), array("jpg",'png','gif','jpeg')) ) {
				$files[$md5]['pict'] = array(
					'build' => $file->getMTime(),
					'file'  => $file->getfilename(),
					'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
				);
			}

			if(empty($files[$md5]['draft'])) unset($files[$md5]);
		} 
	}
	return $files;
}

/**
 * Build attachement picture for a post
 * @param Array $config Configuration for an image
 * @return bool
 */
function generatePict(Array $config) {

	klog('Find an image attach to the current post');
	// [0] => w ---- [1] => h
	$_info = getimagesize($config['path']);
	$image = new PHPImageWorkshop\ImageWorkshop(array(
		    'imageFromPath' => $config['path'],
	));
	if ($GLOBALS['cyaneus']['thumb_w'] < $_info[0]) {
		$image->resizeInPixel($GLOBALS['cyaneus']['thumb_w'], null, true);
	}else{
		$image->resizeInPixel($_info[0], null, true);
	}
	 //backgroundColor transparent, only for PNG (otherwise it will be white if set null)
	// (file_path,file_name,create_folder,background_color,quality)
	return $image->save($GLOBALS['cyaneus']['articles'].DIRECTORY_SEPARATOR, $config['file'], true, null, 85);
}

/**
 * Loop on each TAGS in order to build an array [tag:value]
 * @param string Header from a post
 * @return Array [tag:value]
 */
function getTags($post) {
	$info = array();
	$kiwi_tags = explode(',', $GLOBALS['cyaneus']['tags']);
	foreach ($kiwi_tags as $tag) {
		$info[$tag] = info($post,$tag);
	}
	return $info;
}

/**
 * Build a page from each article. And regenerate configuration for articles and rss/archives/index
 * @todo order by date DESC
  */
function draftsToHtml() {
	$rss           = "";
	$index_list    = array();
	$archives_list = "";
	$drafts        = getDrafts();

	foreach ($drafts as $d) {
		klog('New draft found : '.$d['draft']['file']);
		// We extract headers from the draft
		$config = strstr(file_get_contents($d['draft']['path']),'==POST==', true );
		// Remove headers from the draft to keep the content
		$article = str_replace('==POST==','',strstr(file_get_contents($d['draft']['path']),'==POST=='));

		$info = getTags($config); // Build TAGS array
		$info['content'] = SmartyPants(Markdown($article));

		// Rebuild some informations
		if(empty($info['url'])) $info['url'] = url($info['title']);

		$info['date'] = date($GLOBALS['cyaneus']['date_format'],$d['draft']['build']);
		$index_list[] = array(
			'post_url' => $GLOBALS['cyaneus']['articles'].DIRECTORY_SEPARATOR.$info['url'].".html",
			'post_title' => $info['title'],
			'post_date' => $info['date'],
			'post_date_rss' => date('D, j M Y H:i:s \G\M\T',$d['draft']['build']),
			'post_description' => $info['description'],
			'post_content' =>  $info['content'],
			'post_author' =>  $info['author'],
			'post_tags' =>  $info['tags'],
			'timestamp' => $d['draft']['build'],
		);

		// Attach pictures
		if (!empty($d['pict'])) generatePict($d['pict']);
	}

	buildPost($index_list);
	klog('SUCCESS - Posts creation');
	// Create default pages
	buildRss($index_list);
	buildPage($index_list);
	buildPage($index_list,'archives');
	klog('SUCCESS - Pages and posts creation');
	echo "SUCCESS";
}

/**
 * Build a configuration JSON store in USERDATA.articles.json
 * We create an index of each artciles, to prevent rebuild each time we have a new one
 * @param Array Information about a post.
 */
function checkPostToUpdate($info) {
	$config = $GLOBALS['archives'];

	if(!isset($config[$info['post_url']])) {
		$config[$info['post_url']] = array(
			'added_time' => $info['timestamp'],
			'update'     => $info['timestamp']
			);
		file_put_contents(USERDATA.'articles.json',base64_encode(json_encode($config)));
		klog('Updated archives configuration in '.USERDATA);
		$GLOBALS['archives'] = $config;
		$info['post_date'] = date($GLOBALS['cyaneus']['date_format'],$info['timestamp']);
		createPageHtml($info);
		return true;
	}

	if ($config[$info['post_url']] !== $info['timestamp']) {
		$config[$info['post_url']] = $info['timestamp'];
		file_put_contents(USERDATA.'articles.json',base64_encode(json_encode($config)));
		$GLOBALS['archives'] = $config;
		$info['post_date'] = date($GLOBALS['cyaneus']['date_format'],$config[$info['post_url']]['added_time']);
		createPageHtml($info);
	}

}

function buildPost($posts) {
	foreach ($posts as $post) {
		klog('POST : build post for - '.$post['post_title']);
		checkPostToUpdate($post);
	}
}

/**
 * Build the rss file -> ./rss.xml
 * @param string Rss stringify
 * @return Bool
 */
function buildRss($content) {
	klog('Build Rss file');
	$template = new Template($GLOBALS['cyaneus']);
	$str = $template->page('rss',array('content' => $content));
	$path = trim($GLOBALS['cyaneus']['folder_main_path']).DIRECTORY_SEPARATOR;
    return file_put_contents($path.'rss.xml',$str);
}
/**
 * Build a page for the site
 * @param string content html stringify
 * @param string page to build
 * @return Bool
 */
function buildPage($content,$page='index') {

	$template = new Template($GLOBALS['cyaneus']);
	$str = $template->page($page,array('content' => $content));
	$path = trim($GLOBALS['cyaneus']['folder_main_path']).DIRECTORY_SEPARATOR;
    return file_put_contents($path.$page.'.html',$str);
}
/**
 * Build the post
 * @param string content html stringify
 * @return Bool
 */
function createPageHtml($content) {

	klog('CREATION : Create a page : '.$content['post_url']);
	$template = new Template($GLOBALS['cyaneus']);
	$str = $template->post($content);
	$path = trim($GLOBALS['cyaneus']['folder_main_path']).DIRECTORY_SEPARATOR.$content['post_url'];
	return file_put_contents($path, $str);
}

/**
 * Erase all files store in ARTICLES and rss&archives&index.html
 */
function cleanFiles() {
    $current = dirname(__FILE__).DIRECTORY_SEPARATOR;
    $_noDelete = $current.ARTICLES.DIRECTORY_SEPARATOR.'index.html';
    $_noDelete2 = $current.DRAFT.DIRECTORY_SEPARATOR.'index.html';
    $iterator = new RecursiveDirectoryIterator($current,RecursiveIteratorIterator::CHILD_FIRST);

    foreach(new RecursiveIteratorIterator($iterator) as $file) {
        if($file->isFile()) {

        	if($file->getPathname() === $_noDelete || $file->getPathname() === $_noDelete2) continue;
            if(in_array($file->getExtension(), array('xml','html')) ) {
                unlink($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename());
            }
            if('articles.json' === $file->getFilename()) 
            	unlink($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename());
        }
    }
}

function formRebuild() {
	$str = head(array('title'=> 'Rebuild')).menu().$content.footer();
	echo '<form method="GET" action="'.$GLOBALS['url'].'cya.php">';
	echo '<input type="password" name="rebuild" id="rebuild" />';
	echo '<button type="submit">Rebuild</button>';
	echo '</form>';

}
init();

if (isset($_GET['rebuild'])){
	if(empty($_GET['rebuild']) || trim($_GET['rebuild']) !== REBUILD_KEY) {
		klog('WARNING '.$_SERVER['REMOTE_ADDR'].' Access to rebuild Failed');
		formRebuild();
	}else{
		klog('WARNING '.$_SERVER['REMOTE_ADDR'].' Access to rebuild success');
		klog('CLEAN - clean directory');
		cleanFiles();

		klog('REBUILD - Rebuild the website');
		draftsToHtml();
	}
}else{
	draftsToHtml();
}

/**
 * Github Hook page. url?github
 */
if(isset($_GET['github'])) {
	if(!empty($_POST['payload'])) {
		klog(var_export($_SERVER,true),'server');
		klog($_SERVER['REMOTE_ADDR'].' Connected');
		
		$_ip = array(
			'204.232.175.75',
			'207.97.227.253',
			'50.57.128.197',
			'108.171.174.178',
			'50.57.231.61',
			'204.232.175.64',
			'192.30.252.0'
			);
		/**
		 * URL to grab raw data such as
		 * https://raw.github.com/dhoko/blog/master/
		 * For repository: blog by me (dhoko) on branch master
		 */
		$_base = 'XXX';

		try {
			$json = json_decode($_POST['payload']);
			if(isset($json->pusher->email) && $json->pusher->email !== $GLOBALS['cyaneus']['email_git']) 
				throw new Exception('Wrong email pusher');
			if(isset($json->pusher->name) && $json->pusher->name !== $GLOBALS['cyaneus']['name_git']) 
				throw new Exception('Wrong name pusher');
			if(isset($json->repository->url) && $json->repository->url !== $GLOBALS['cyaneus']['url_git']) 
				throw new Exception('Wrong repository url');
			
			if(!in_array($_SERVER['REMOTE_ADDR'], $_ip))
				throw new Exception('ERROR - Request does not come from github wrong ip: '.$_SERVER['REMOTE_ADDR']);

			$files = array();

			foreach ($json->head_commit->added as $file) {
				klog('GITHUB Try to get content from : '.$_base.$file);
				$folder = explode('/', $file);
				$files[] = array(
					'path'    => $file,
					'folder'  => $folder[0],
					'content' => file_get_contents($_base.$file)
					);
			}

			foreach ($files as $e) {
				mkdir($GLOBALS['cyaneus']['draft'].DIRECTORY_SEPARATOR.$e['folder']);
				file_put_contents($GLOBALS['cyaneus']['draft'].DIRECTORY_SEPARATOR.$e['path'],$e['content'] );
			}

			draftsToHtml();
		} catch (Exception $e) {
			klog($e->getMessage(),"error");
			
		}
	}
}