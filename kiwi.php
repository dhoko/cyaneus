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
	if(!file_exists($list_article)) file_put_contents($list_article,'{}');
	$GLOBALS['archives'] = json_decode(file_get_contents($list_article),true);
}

function info($data,$tag) {
	preg_match('/"([^"]+)"/',strstr($data,$tag),$match);
	return (isset($match[1])) ? $match[1] : '';
}

function url($path) {
    $url = str_replace('&', '-and-', $path);
    $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
    $url = str_replace(' ', '-', $url);
    $url = str_replace('--', '-', $url);
    return strtolower($url);
}

function getDrafts() {
	$files          = array(); 
	$readable_draft = array('md','markdown');
	$draftPath      = dirname(__FILE__).DIRECTORY_SEPARATOR.DRAFT.DIRECTORY_SEPARATOR;
	$iterator       = new RecursiveDirectoryIterator($draftPath,RecursiveIteratorIterator::CHILD_FIRST);

	foreach(new RecursiveIteratorIterator($iterator) as $file) {
		if($file->isFile() && in_array($file->getExtension(), $readable_draft)) {
			$name = explode('.',$file->getfilename());
			$files[] = array(
				'build' => $file->getMTime(),
				'title' => $name[0],
				'file'  => $file->getfilename(),
				'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
			);
		} 
	}
	return $files;
}

function getTags($post) {
	$info = array();
	$kiwi_tags = explode(',', TAGS);
	foreach ($kiwi_tags as $tag) {
		$info[$tag] = info($post,$tag);
	}
	return $info;
}

function markdownToHtml($txt) {
    return SmartyPants(Markdown($txt));
}

function draftsToHtml() {
	$rss           = "";
	$index_list    = "";
	$archives_list = "";
	$drafts        = getDrafts();

	foreach ($drafts as $d) {

		$config = strstr(file_get_contents($d['path']),'==POST==', true );
		$article = str_replace('==POST==','',strstr(file_get_contents($d['path']),'==POST=='));

		$info = getTags($config);
		$info['content'] = markdownToHtml($article);

		if(empty($info['url'])) $info['url'] = url($info['title']);
		if(empty($info['date'])) $info['date'] = date('d/m/Y',$d['build']);
		$info['timestamp'] = $d['build'];

		createPageHtml($info);
		$rss .= rssPost($info);
		$index_list .= index($info);
		$archives_list .= archives($info);

		checkPostToUpdate($info);
	}
	buildRss($rss);
	buildPage($index_list);
	buildPage($archives_list,'archives');
}

function checkPostToUpdate($info) {
	$config = $GLOBALS['archives'];
	if(!isset($config[$info['url']]) && $config[$info['url']]!== $info['timestamp']) {
		$config[$info['url']] = $info['timestamp'];

		file_put_contents(USERDATA.'articles.json',base64_encode(json_encode($config)));
		$GLOBALS['archives'] = $config;
	}
}

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

function footer() {
	$str = '<p class="skip"><a href="#haut">Retourner en haut</a></p>';
	$str .= "\n".'</body>';
	$str .= "\n".'</html>';
    return $str;
}

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

function archives($info) {
	return index($info);
}

function rssPost($info) {

	$url  = URL.ARTICLES.DIRECTORY_SEPARATOR.$info['url'].'.html';
	$date = date('D, j M Y H:i:s \G\M\T',$info['timestamp']);

	$rss = '<item>';
	$rss .= '<title>%s</title>';
	$rss .= '<link>%s</link>';
	$rss .= '<pubDate>%s</pubDate>';
	$rss .= ' <description><![CDATA[%s]]></description>';
	$rss .= '</item>';
	return sprintf($rss,$info['title'],$url,$date,$info['content']);
}

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

function buildRss($rss) {
	$str = rssHead().$rss.'</rss>';
	$path = '.'.DIRECTORY_SEPARATOR;
    return file_put_contents($path.'rss.xml',$str);
}

function buildPage($content,$page='index') {
	$title = ($page !== 'index') ? $page : '';
	$str = head(array('title'=>$title)).menu().$content.footer();
	$path = '.'.DIRECTORY_SEPARATOR;
    return file_put_contents($path.$page.'.html',$str);
}

function createPageHtml($info) {
	$html = head($info).menu().content($info).footer();
	$file = dirname(__FILE__).DIRECTORY_SEPARATOR.ARTICLES.DIRECTORY_SEPARATOR.$info['url'].'.html';
	file_put_contents($file, $html);
}

init();
draftsToHtml();
echo "ok";
debug(getDrafts());
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

if(isset($_GET['clean'])) cleanFiles();
function debug($var,$title="") {
	echo "<div>";
		if(!empty($title)) echo "<h2>{$title}</h2>";
		echo "<pre>";
			print_r($var);
		echo "</pre>";
	echo "</div>";
}

debug(dirname(__FILE__));