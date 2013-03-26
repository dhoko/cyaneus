<?php

#   ©2012 JSI & al.jes, certains droits réservés…
#   http://jeunes-science.org
#   http://aljes.me
#   Copyleft : cette œuvre est libre, vous pouvez la copier, la diffuser et la modifier selon les termes de la Licence Art Libre :
#   http://www.artlibre.org
#   Pour plus d'information, visitez la documentation en ligne :
#   http://jeunes-science.org/kiwi/

#   Variables de configuration
define('TITRE_DU_JOURNAL', 'Le colibri libre');
define('URL_DU_JOURNAL', 'http://localhost:8042/');
define('AUTEUR', 'dhoko');
define('DESCRIPTION', 'Un journal web généré par kiwi, le générateur endémique.');
define('LANGUE', 'fr'); #   Langue du journal.
define('FEUILLE_DE_STYLE', 'style.css'); #   URL de la feuille de style.
define('FAVICON', 'favicon.png'); # URL de la favicon ; PNG uniquement.

#   Fonctions
function transforme($texte) {
    #   Cette fonction transforme du Markdown en xHTML.
    #   En prime, elle corrige la ponctuation.
    
    include_once ('./markdown.php');
    include_once ('./smartypants.php');
    # Attention : les fichiers appelés sont nécessaires !
    
    $texte = SmartyPants(Markdown($texte));
    
    return $texte;
}

function lecture($url) {
    #   Cette fonction lit le fichier d'un article.
    #   Les étapes sont les suivantes :
    #   1. L'on reconstruit l'URL du fichier.
    #   2. L'on extraie le fichier dans une chaîne.
    #   3. L'on transforme le texte en xHTML.
    return transforme(file_get_contents($url));
}


function lien($article) {
    return 'articles/' . $article;
}

function url($path) {
    $url = str_replace('&', '-and-', $path);
    $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
    $url = str_replace(' ', '-', $url);
    $url = str_replace('--', '-', $url);
    return strtolower($url);
}

function liste_articles() {
    $files    = array();
    $iterator = new DirectoryIterator(dirname(__FILE__).DIRECTORY_SEPARATOR.'draft'.DIRECTORY_SEPARATOR);

    foreach ($iterator as $file) {
        if($file->isFile()) {
            $name    = explode('.',$file->getfilename());
            $content = lecture($file->getPath().DIRECTORY_SEPARATOR.$file->getfilename());
            $files[] = array(
                'bdate'   => $file->getCTime(),
                'date'    => date("d/m/Y",$file->getCTime()),
                'url'     => url($name[0]).'.html',
                'title'   => $name[0],
                'content' => $content,
                'html'    => toHtml(url($name[0]),$content,$name[0])
            );
        }
    }
    return array_reverse($files);
}

function head($title = '') {
    $title = (empty($title)) ? TITRE_DU_JOURNAL : $title.' - '.TITRE_DU_JOURNAL;
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.LANGUE.'">
<head>
    <title>'.$title.'</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="alternate" type="application/rss+xml" href="'.URL_DU_JOURNAL.'rss.xml" />
    <link rel="stylesheet" type="text/css" href="'.URL_DU_JOURNAL.FEUILLE_DE_STYLE.'" />
    <link rel="shortcut icon" type="image/png" href="'.FAVICON.'" />
</head><body>';
}

function footer() {
    return '<p class="skip"><a href="#haut">Retourner en haut</a></p><p class="colophon">©'.TITRE_DU_JOURNAL.',certains droits réservés…<br />Réagissez ! Écrivez-moi : '.AUTEUR.'
<a href="http://jeunes-science.org/kiwi/" title="kiwi, le générateur endémique"><img src="http://jeunes-science.org/kiwi/colophon.png" alt="kiwi"></a></p>
 </body>
</html>';
}

function toHtml($file,$content,$title) {
    $path = 'articles'.DIRECTORY_SEPARATOR;
    $content = head($title).menu().$content.footer();
    if(!file_exists($path.$file.'.html')) {
        return file_put_contents($path.$file.'.html',$content);
    }
    return false;
}

function affichage($article) {
 
    return '<h2>'.$article['title'].'</h2>
    '.lecture($article).'
    <p class="skip">Publié le '.$article['date'].'. <a href="'.lien($article['url']).'" title="Accès permanent à « '.$article["title"].' »">Lien permanent</a>. <a href="#haut">Retourner en haut</a>.</p>';
}

function menu() {
    return '<p id="haut" class="skip"><a href="#menu">Aller au menu</a>. <a href="#contenu">Aller au contenu</a>.</p>
    <h1>'.TITRE_DU_JOURNAL.'</h1>
    <ul id="menu" class="menu">
        <li><a href="'.URL_DU_JOURNAL.'">Journal</a></li>
        <li><a href="'.URL_DU_JOURNAL.'archives.html">Archives</a></li>
        <li><a href="'.URL_DU_JOURNAL.'rss.xml">RSS</a></li>
    </ul>';
}

function build() {
    getHome();
    getArchive();
    getRss();
    echo "Done";
}

function update() {
    cleanFiles();
    build();
    echo "Done";
}

function cleanFiles() {
    $current = dirname(__FILE__).DIRECTORY_SEPARATOR;
    // var_dump($current); exit;
    $iterator = new RecursiveDirectoryIterator($current,RecursiveIteratorIterator::CHILD_FIRST);
    echo "<pre>";
    foreach(new RecursiveIteratorIterator($iterator) as $file) {
        if($file->isFile()) {
            if(in_array($file->getExtension(), array('xml','html'))) {
                unlink($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename());
            }
        }
    }
    echo "</pre>";
}

function getArchive() {
    $path = '.'.DIRECTORY_SEPARATOR;
    $content = head('Page d\'archive').menu().getPostsList('articles').footer();
    if(!file_exists($path.'archives.html')) {
        return file_put_contents($path.'archives.html',$content);
    }
    return false;
}

function getRss() {

    $rss ='<?xml version="1.0" encoding="utf-8"?>
    <rss version="2.0">
    <channel>
        <title>'.TITRE_DU_JOURNAL.'</title>
        <link>'.URL_DU_JOURNAL.'</link>
        <description>'.DESCRIPTION.'</description>
        <language>'.LANGUE.'</language>
        <generator>kiwi 0.0 http://jeunes-science.org/kiwi/</generator>
    </channel>';
    foreach (liste_articles() as $article) {
         $rss .= '
            <item>
                <title>'.$article['title'].'</title>
                <link>'.URL_DU_JOURNAL.lien($article['url']).'</link>
                <pubDate>'.date('D, j M Y H:i:s \G\M\T',$article['bdate']).'</pubDate>
                <description><![CDATA['.$article['content'].']]></description>

            </item>';
    }
    $rss.= '</rss>';

    $path = '.'.DIRECTORY_SEPARATOR;
    if(!file_exists($path.'rss.xml')) {
        return file_put_contents($path.'rss.xml',$rss);
    }
    return false;
}

function getHome() {
    $path = '.'.DIRECTORY_SEPARATOR;
    $content = head().menu().getPostsList().footer();
    if(!file_exists($path.'index.html')) {
        return file_put_contents($path.'index.html',$content);
    }
    return false;
}

function getPostsList($elements = 'titres') {
    
    $data = array();
    $data[] = '<ul id="contenu" class="journal">';
    foreach (liste_articles() as $article) {
        if($elements == 'articles') {
            $data[] = affichage($article);
            continue;
        }
        $data[] =  '<li>'.$article['date'].' : <a href="'.lien($article['url']).'">'.$article['title'].'</a></li>';
    }
    $data[] = '</ul>';
    return implode("",$data);
}

if(isset($_GET['build'])) build();
if(isset($_GET['rebuild'])) update();

?>
