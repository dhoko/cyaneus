<?php 
/**
 * Main config
 */
define('TITLE_SITE', 'Le colibri libre');
define('URL', 'http://'.$_SERVER['HTTP_HOST'].'/');
define('AUTHOR', 'dhoko');
define('GENERATOR', 'kiwi 0.0 http://jeunes-science.org/kiwi/');
define('DESCRIPTION', 'Un journal web généré par kiwi, le générateur endémique.');
define('LANGUAGE', 'fr'); #   Langue du journal.
define('DRAFT', 'draft'); #   Langue du journal.
define('ARTICLES', 'articles'); #   Langue du journal.
define('TAGS','title,url,date,tags,description,author');

include_once ('./markdown.php');
include_once ('./smartypants.php');

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

	foreach(new RecursiveIteratorIterator($iterator) as $file) {
		if($file->isFile() && in_array($file->getExtension(), $readable_draft)) {
			$files[] = array(
				'build' => $file->getMTime(),
				'file'  => $file->getfilename(),
				'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
			);
		} 
	}
	return $files;
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
  */
function draftsToHtml() {
	$rss           = "";
	$index_list    = "";
	$archives_list = "";
	$drafts        = getDrafts();

	foreach ($drafts as $d) {
		// We extract headers from the draft
		$config = strstr(file_get_contents($d['path']),'==POST==', true );
		// Remove headers from the draft to keep the content
		$article = str_replace('==POST==','',strstr(file_get_contents($d['path']),'==POST=='));

		$info = getTags($config); // Build TAGS array
		$info['content'] = SmartyPants(Markdown($article));
		// Rebuild some informations
		if(empty($info['url'])) $info['url'] = url($info['title']);
		$info['timestamp'] = $d['build'];
		checkPostToUpdate($info);
		$rss .= rssPost($info);
		$index_list .= index($info);
		$archives_list .= archives($info);

	}
	// Create default pages
	buildRss($rss);
	buildPage($index_list);
	buildPage($archives_list,'archives');
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
draftsToHtml();

if(isset($_POST)) {
	file_put_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'log.txt', var_export($_POST));
}