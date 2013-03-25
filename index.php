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
define('AUTEUR', 'dhoko@cyaneus.org');
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

function titre($article) {
    # Cette fonction extraie le titre d'un article.
       
    return $titre;
}



function lien($article) {
    #   Cette fonction retourne le lien relatif d'un article.
    
    $lien = 'articles/' . $article;
    
    return $lien;
}



function url($path) {
    $url = str_replace('&', '-and-', $path);
    $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
    $url = str_replace(' ', '-', $url);
    $url = str_replace('--', '-', $url);
    return strtolower($url);
}



function liste_articles() {
    #   Cette fonction liste les articles publiables dans un tableau.
    #   Les étapes sont les suivantes :
    #   1. L'on récupère tous les fichiers du dossier courant.
    #   2. L'on vérifie que ce sont des articles.
    #   3. L'on vérifie qu'ils soient publiables.
    #   4. L'on inverse l'ordre du tableau
    #      (l'on les veut dans l'ordre antéchronologique, non ?)

    $files = array();
    $iterator = new DirectoryIterator(dirname(__FILE__).DIRECTORY_SEPARATOR.'draft'.DIRECTORY_SEPARATOR);


    foreach ($iterator as $file) {
        if($file->isFile()) {
            $name = explode('.',$file->getfilename());

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
    // echo "<pre>";
    // print_r($files);
    // echo "</pre>";

    return array_reverse($files);
}



function head($title = '') {
    $title = (empty($title)) ? TITRE_DU_JOURNAL : $title.' - '.TITRE_DU_JOURNAL;
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.LANGUE.'">
<head>
    <title>'.$title.'</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="alternate" type="application/rss+xml" href="'.URL_DU_JOURNAL.'?rss" />
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
    #   Cette fonction retourne un article avec toutes ses méta-données.
    
    return '
    <h2>'.$article['title'].'</h2>
    '.lecture($article).'
    <p class="skip">Publié le '.$article['date'].'. <a href="'.lien($article['url']).'" title="Accès permanent à « '.$article["title"].' »">Lien permanent</a>. <a href="#haut">Retourner en haut</a>.</p>';
}

function menu() {
    return '<p id="haut" class="skip"><a href="#menu">Aller au menu</a>. <a href="#contenu">Aller au contenu</a>.</p>
    <h1>'.TITRE_DU_JOURNAL.'</h1>
    <ul id="menu" class="menu">
        <li><a href="'.URL_DU_JOURNAL.'?journal">Journal</a></li>
        <li><a href="'.URL_DU_JOURNAL.'?archives">Archives</a></li>
        <li><a href="'.URL_DU_JOURNAL.'?rss">RSS</a></li>
    </ul>';
}

function affichage_liste($elements = 'titres') {
    #   Cette fonction affiche :
    #   – soit une suite d'articles,
    #   – soit la liste des titres des articles (avec date & lien).
    
    foreach (liste_articles() as $article) :
        if ($elements == 'articles') :
            echo affichage($article);
        else :
            echo '
        <li>'.$article['date'].' : <a href="'.lien($article['url']).'">'.$article['title'].'</a></li>';
        endif;
    endforeach;
}

#   Code principal
echo '<?xml version="1.0" encoding="utf-8"?>';

#   Nous avons quatre cas de figure :
#   – l'accueil (ou journal ; qui liste le titre des articles),
#   – les archives (tous les articles, en entier avec méta-données),
#   – un article (idem que les archives, mais un seul article),
#   – le flux RSS 2.0 (de tous les articles, non tronqués).

if (isset($_GET['rss'])) :
    #   Nous commençons par nous occuper du flux RSS.
    
    echo '
<rss version="2.0">
<channel>
    <title>'.TITRE_DU_JOURNAL.'</title>
    <link>'.URL_DU_JOURNAL.'</link>
    <description>'.DESCRIPTION.'</description>
    <language>'.LANGUE.'</language>
    <generator>kiwi 0.0 http://jeunes-science.org/kiwi/</generator>
</channel>';
    foreach (liste_articles() as $article) :
        echo '
<item>
    <title>'.$article['title'].'</title>
    <link>'.URL_DU_JOURNAL.lien($article['url']).'</link>
    <pubDate>'.date('D, j M Y H:i:s \G\M\T',$article['bdate']).'</pubDate>
    <description><![CDATA['.$article['content'].']]></description>

</item>';
    endforeach;
    echo '
</rss>';
else :
    #   On en a fini avec le RSS, on factorise le xHTML.
    
    echo head().menu();
    #   Puis dans l'ordre : article, archives, 42, journal.
    
    if (isset($_GET['article'])) :
        $article = $_GET['article'];
        echo '<span id="contenu"></span>';
        echo affichage($article);
    elseif (isset($_GET['archives'])) :
        echo '
        <p id="contenu" class="skip">Pour faire une recherche, tapez « Ctrl » et « F » simultanément.</p>';
        affichage_liste('articles');
    elseif (isset($_GET['42'])) : # un petit œuf de Pâques…
        echo '<p id="contenu">Bravo ! vous venez de trouver la réponse à la grande question à propos de la vie, de l\'univers et de tout le reste. Il vous reste à trouver la question…</p>';
    else : # c-à-d idem que if (isset($_GET['journal']))
        echo '
    <ul id="contenu" class="journal">';
        affichage_liste('titres');
        echo '
    </ul>';
     endif;
    echo footer();
endif;

#   En cas de question(s) quant à ce code, n'hésitez pas à me contacter :
#   me[at]aljes[dot]me

?>
