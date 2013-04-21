<?php 
/**
 * Main config
 */
define('TITLE_SITE', 'XXX');
define('URL', 'XXXX'); // You must add / at the end -> http://localhost:8042/
define('AUTHOR', 'XXX');
define('GENERATOR', 'XXX 1.0 http://jeunes-science.org/kiwi/');
define('DESCRIPTION', 'Un journal web généré par kiwi, le générateur endémique.');
define('LANGUAGE', 'fr'); #   Langue du journal.
define('DRAFT', 'draft'); #   Langue du journal.
define('ARTICLES', 'articles'); #   Langue du journal.
define('TAGS','title,url,date,tags,description,author');

define('THUMB_W', 600);

define('EMAIL_GIT', "XXX");
define('NAME_GIT', "XXX");
define('URL_GIT', "XXX");
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
	$data_folder = dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
	$list_article = $data_folder.'articles.json';

	define('USERDATA', $data_folder);

	if(!file_exists($data_folder)) mkdir($data_folder,0705);
	// Store an empty string base 64
	if(!file_exists($list_article)) file_put_contents($list_article,base64_encode(json_encode(array())));
	$GLOBALS['archives'] = json_decode(base64_decode(file_get_contents($list_article)),true);
	klog('INIT - Kiwi loaded');
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
	$draftPath      = dirname(__FILE__).DIRECTORY_SEPARATOR.DRAFT.DIRECTORY_SEPARATOR;
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

	// [0] => w ---- [1] => h
	$_info = getimagesize($config['path']);
	$image = new PHPImageWorkshop\ImageWorkshop(array(
		    'imageFromPath' => $config['path'],
	));
	if (THUMB_W < $_info[0]) {
		$image->resizeInPixel(THUMB_W, null, true);
	}else{
		$image->resizeInPixel($_info[0], null, true);
	}

	 //backgroundColor transparent, only for PNG (otherwise it will be white if set null)
	// (file_path,file_name,create_folder,background_color,quality)
	$image->save(ARTICLES.DIRECTORY_SEPARATOR, $config['file'], true, null, 85);
}

/**
 * Loop on each TAGS in order to build an array [tag:value]
 * @param string Header from a post
 * @return Array [tag:value]
 */
function getTags($post) {
	$info = array();
	$kiwi_tags = explode(',', TAGS);
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
	$index_list    = "";
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
		$info['timestamp'] = $d['draft']['build'];
		checkPostToUpdate($info);

		// Build required elements
		$rss .= rssPost($info);
		$index_list .= index($info);
		$archives_list .= archives($info);

		// Attach pictures
		if (!empty($d['pict'])) generatePict($d['pict']);
	}
	klog('SUCCESS - Posts creation');
	// Create default pages
	buildRss($rss);
	buildPage($index_list);
	buildPage($archives_list,'archives');
	klog('SUCCESS - Pages and posts creation');
}

/**
 * Build a configuration JSON store in USERDATA.articles.json
 * We create an index of each artciles, to prevent rebuild each time we have a new one
 * @param Array Information about a post.
 */
function checkPostToUpdate($info) {
	$config = $GLOBALS['archives'];

	if(!isset($config[$info['url']])) {
		$config[$info['url']] = array(
			'added_time' => $info['timestamp'],
			'update'     => $info['timestamp']
			);
		file_put_contents(USERDATA.'articles.json',base64_encode(json_encode($config)));
		klog('Updated archives configuration in '.USERDATA);
		$GLOBALS['archives'] = $config;
		if(empty($info['date'])) $info['date'] = date('d/m/Y',$info['timestamp']);
		createPageHtml($info);
		return true;
	}

	if ($config[$info['url']] !== $info['timestamp']) {
		$config[$info['url']] = $info['timestamp'];
		file_put_contents(USERDATA.'articles.json',base64_encode(json_encode($config)));
		$GLOBALS['archives'] = $config;
		if(empty($info['date'])) $info['date'] = date('d/m/Y',$config[$info['url']]['added_time']);
		createPageHtml($info);
	}
}

/**
 * Create header HTML
 * @param Array information about an article (title&co...)
 * @return string
 */
function head($info) {
    $title = (empty($info['title'])) ? TITLE_SITE : $info['title'].' - '.TITLE_SITE;
    $description = (empty($info['description'])) ? DESCRIPTION : $info['description'];
    $rss = URL.'rss.xml';
    $css = URL.'style.css';

	$str = '<!doctype html>'."\n";
	$str .= '<html lang="%s">'."\n";
	$str .= "\t".'<head>'."\n";
	$str .= "\t\t".'<meta charset="UTF-8">'."\n";
	$str .= "\t\t".'<title>%s</title>'."\n";
	$str .= "\t\t".'<meta name="description" content="%s"/>'."\n";
	$str .= "\t\t".'<meta name="author" content="%s">'."\n";
	$str .= "\t\t".'<link rel="alternate" type="application/rss+xml" href="%s" />'."\n";
	$str .= "\t\t".'<link rel="stylesheet" type="text/css" href="%s" />'."\n";
	$str .= "\t\t".'<link rel="shortcut icon" type="image/png" href="favicon.png" />'."\n";
	$str .= "\t".'</head>'."\n";
	$str .= '<body>'."\n";

	return sprintf($str,LANGUAGE,$title,$description,AUTHOR,$rss,$css);
}
/**
 * Create menu HTML
 * @return string
 */
function menu() {
	$str = '<nav class="navigation">'."\n";
	$str .= "\t".'<ul>'."\n";
	$str .= "\t\t".'<li><a href="'.URL.'">Home</a></li>'."\n";
	$str .= "\t\t".'<li><a href="'.URL.'archives.html">Archives</a></li>'."\n";
	$str .= "\t\t".'<li><a href="'.URL.'rss.xml">RSS</a></li>'."\n";
	$str .= "\t".'</ul>'."\n";
	$str .= '</nav>'."\n";
	return $str;
}
/**
 * Create content for a post
 * @param Array information about an article (title&co...)
 * @return string
 */
function content($html) {

	$str = '<article class="post">'."\n";
	$str .= "\t".'<header>'."\n";
	$str .= "\t\t".'<h1 class="post-title">%s</h1>'."\n";
	$str .= "\t\t".'<time class="post-date">%s</time>'."\n";
	$str .= "\t".'</header>'."\n";
	$str .= "\t".'%s'."\n";
	$str .= "\t".'<footer>'."\n";
	$str .= "\t\t".'By <strong>%s</strong>'."\n";
	$str .= "\t".'</footer>'."\n";
	$str .= '</article>'."\n";
	return sprintf($str,$html['title'],$html['date'],$html['content'],$html['author']);
}
/**
 * Create footer HTML
 * @return string
 */
function footer() {
	$str = '<p class="skip"><a href="#haut">Retourner en haut</a></p>';
	$str .= "\n".'</body>';
	$str .= "\n".'</html>';
    return $str;
}
/**
 * Create index content HTML
 * @param Array information about an article (title&co...)
 * @return string
 */
function index($info) {

	$url = ARTICLES.DIRECTORY_SEPARATOR.$info['url'].".html";
	$str = '<article class="post post-index">'."\n";
	$str .= "\t".'<header>'."\n";
	$str .= "\t\t".'<h1 class="post-title">'."\n";
	$str .= "\t\t\t".'<a href="%s" title="%s">%s</a>'."\n";
	$str .= "\t\t".'</h1>'."\n";
	$str .= "\t\t".'<time class="post-date">%s</time>'."\n";
	$str .= "\t".'</header>'."\n";
	$str .= '</article>'."\n";
	return sprintf($str,$url,$info['title'],$info['title'],$info['date']);
}

/**
 * Create Archives content HTML
 * @param Array information about an article (title&co...)
 * @return string
 */
function archives($info) {
	return index($info);
}

/**
 * Create header HTML
 * @param Array information about an article (title&co...)
 * @return string
 */
function rssPost($info) {

	$url  = URL.ARTICLES.DIRECTORY_SEPARATOR.$info['url'].'.html';
	$date = date('D, j M Y H:i:s \G\M\T',$info['timestamp']);

	$rss = '<item>';
	$rss .= '<title>%s</title>';
	$rss .= '<link>%s</link>';
	$rss .= '<pubDate>%s</pubDate>';
	$rss .= '<description><![CDATA[%s]]></description>';
	$rss .= '</item>';
	return sprintf($rss,$info['title'],$url,$date,$info['content']);
}

/**
 * Create RSS header
 * @param Array information about an article (title&co...)
 * @return string
 */
function rssHead() {
	$rss ='<?xml version="1.0" encoding="UTF-8"?>';
    $rss .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">';
    $rss .= '<channel>';
    $rss .= '<title>'.TITLE_SITE.'</title>';
    $rss .= '<link>'.URL.'</link>';
    $rss .= '<description>'.DESCRIPTION.'</description>';
    $rss .= '<language>'.LANGUAGE.'</language>';
    $rss .= '<generator>'.GENERATOR.'</generator>';
    $rss .= '</channel>';
    return $rss;
}

/**
 * Build the rss file -> ./rss.xml
 * @param string Rss stringify
 * @return Bool
 */
function buildRss($rss) {
	klog('Build Rss file');
	$str = rssHead().$rss.'</rss>';
	$path = '.'.DIRECTORY_SEPARATOR;
    return file_put_contents($path.'rss.xml',$str);
}
/**
 * Build a page for the site
 * @param string content html stringify
 * @param string page to build
 * @return Bool
 */
function buildPage($content,$page='index') {

	klog('Build page '.$page);
	$title = ($page !== 'index') ? $page : '';
	$str = head(array('title'=>$title)).menu().$content.footer();
	$path = '.'.DIRECTORY_SEPARATOR;
    return file_put_contents($path.$page.'.html',$str);
}
/**
 * Build the post
 * @param string content html stringify
 * @return Bool
 */
function createPageHtml($info) {

	klog('Create a page : '.$info['url']);
	$html = head($info).menu().content($info).footer();
	$file = dirname(__FILE__).DIRECTORY_SEPARATOR.ARTICLES.DIRECTORY_SEPARATOR.$info['url'].'.html';
	return file_put_contents($file, $html);
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
        }
    }
}

init();
echo "<h2> Le debug de config</h2>";
echo "<pre>";
var_dump(getDrafts());
echo "</pre>";
echo "<h2> Le debug de html</h2>";
echo "<pre>";
draftsToHtml();

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
			if(isset($json->pusher->email) && $json->pusher->email !== EMAIL_GIT) 
				throw new Exception('Wrong email pusher');
			if(isset($json->pusher->name) && $json->pusher->name !== NAME_GIT) 
				throw new Exception('Wrong name pusher');
			if(isset($json->repository->url) && $json->repository->url !== URL_GIT) 
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
				mkdir(DRAFT.DIRECTORY_SEPARATOR.$e['folder']);
				file_put_contents(DRAFT.DIRECTORY_SEPARATOR.$e['path'],$e['content'] );
			}

			draftsToHtml();
		} catch (Exception $e) {
			klog($e->getMessage(),"error");
			
		}
	}
}