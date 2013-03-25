<?php

#   ©2012 JSI & al.jes, certains droits réservés…
#   http://jeunes-science.org
#   http://aljes.me
#   Copyleft : cette œuvre est libre, vous pouvez la copier, la diffuser et la modifier selon les termes de la Licence Art Libre :
#   http://www.artlibre.org
#   Pour plus d'information, visitez la documentation en ligne :
#   http://jeunes-science.org/kiwi/

#   Variables de configuration
$titre_du_journal = 'Titre du journal';
$url_du_journal = 'http://exemple.tld/';
$auteur = 'auteur@courriel.tld'
$description = 'Un journal web généré par kiwi, le générateur endémique.';
$langue = 'fr'; #   Langue du journal.
$feuille_de_style = 'style.css'; #   URL de la feuille de style.
$favicon = 'favicon.png'; # URL de la favicon ; PNG uniquement.

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

function lecture($article) {
    #   Cette fonction lit le fichier d'un article.
    #   Les étapes sont les suivantes :
    #   1. L'on reconstruit l'URL du fichier.
    #   2. L'on extraie le fichier dans une chaîne.
    #   3. L'on transforme le texte en xHTML.
    
    $url = './'.$article.'.blog.markdown';
    
    $article = file_get_contents($url);
    
    $article = transforme($article);
    
    return $article;
}

function titre($article) {
    # Cette fonction extraie le titre d'un article.
    
    $titre = preg_replace('#^([0-9]{4}-){2}(.*)$#', "$2", $article);
    
    return $titre;
}

function date_pub($article, $format = "d.m.Y") {
    #   Cette fonction retourne la date de publication d'un article.
    #   Les étapes sont les suivantes :
    #   1. L'on extraie la date du nom de l'article.
    #   2. L'on isole l'année, le mois et le jour.
    #   3. L'on construit le timestamp.
    #   4. L'on applique le format.
    
    $date = preg_replace('#^([0-9]{4}-[0-9]{4})-.*#', "$1", $article);
    
    $annee = preg_replace('#^([0-9]{4})-.*#', "$1", $date);
    $mois = preg_replace('#^[0-9]{4}-([0-9]{2}).*#', "$1", $date);
    $jour = preg_replace('#^[0-9]{4}-[0-9]{2}([0-9]{2}).*#', "$1", $date);
    $annee = (int) $annee; $mois = (int) $mois; $jour = (int) $jour; 
    
    $timestamp = mktime(0, 0, 0, $mois, $jour, $annee);
    
    $date = date($format, $timestamp);
    
    return $date;
}

function lien($article) {
    #   Cette fonction retourne le lien relatif d'un article.
    
    $lien = './index.php?article=' . $article;
    
    return $lien;
}

function publication($article) {
    #   Cette fonction décide si l'on peut publier un article.
    
    if ((int) date_pub($article, "Ymd") <= (int) date("Ymd")) :
        return true;
    else :
        return false;
    endif;
}

function liste_articles() {
    #   Cette fonction liste les articles publiables dans un tableau.
    #   Les étapes sont les suivantes :
    #   1. L'on récupère tous les fichiers du dossier courant.
    #   2. L'on vérifie que ce sont des articles.
    #   3. L'on vérifie qu'ils soient publiables.
    #   4. L'on inverse l'ordre du tableau
    #      (l'on les veut dans l'ordre antéchronologique, non ?)
    
    $fichiers = scandir('./');
    $articles = array();
    
    $regex = '#^([0-9]{4}-[0-9]{4}-[a-zA-Z0-9].*)\.blog\.markdown$#';
    #   Le format recherché est le suivant :
    #   AAAA-MMJJ-titre de l'article.blog.markdown
    
    foreach ($fichiers as $fichier) :
        if (is_file($fichier)) :
            if (preg_match($regex, $fichier)) :
                if (publication($fichier) == true) :
                    $fichier = preg_replace($regex, "$1", $fichier);
                    $articles[] = $fichier;
                endif;
            endif;
        endif;
    endforeach;
    
    $articles = array_reverse($articles);
    
    return $articles;
}

function affichage($article) {
    #   Cette fonction retourne un article avec toutes ses méta-données.
    
    return '
    <h2>'.titre($article).'</h2>
    '.lecture($article).'
    <p class="skip">Publié le '.date_pub($article).'. <a href="'.lien($article).'" title="Accès permanent à « '.titre($article).' »">Lien permanent</a>. <a href="#haut">Retourner en haut</a>.</p>';
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
        <li>'.date_pub($article).' : <a href="'.lien($article).'">'.titre($article).'</a></li>';
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
    <title>'.$titre_du_journal.'</title>
    <link>'.$url_du_journal.'</link>
    <description>'.$description.'</description>
    <language>'.$langue.'</language>
    <generator>kiwi 0.0 http://jeunes-science.org/kiwi/</generator>
</channel>';
    foreach (liste_articles() as $article) :
        echo '
<item>
    <title>'.titre($article).'</title>
    <link>'.lien($article).'</link>
    <pubDate>'.date_pub($article, DATE_RSS).'</pubDate>
    <description>
        '.htmlspecialchars(lecture($article)).'
    </description>
</item>';
    endforeach;
    echo '
</rss>';
else :
    #   On en a fini avec le RSS, on factorise le xHTML.
    
    echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$langue.'">
<head>
    <title>'.$titre_du_journal.'</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="alternate" type="application/rss+xml" href="?rss" />
    <link rel="stylesheet" type="text/css" href="'.$feuille_de_style.'" />
    <link rel="shortcut icon" type="image/png" href="'.$favicon.'" />
</head>
<body>
    <p id="haut" class="skip"><a href="#menu">Aller au menu</a>. <a href="#contenu">Aller au contenu</a>.</p>
    <h1>'.$titre_du_journal.'</h1>
    <ul id="menu" class="menu">
        <li><a href="?journal">Journal</a></li>
        <li><a href="?archives">Archives</a></li>
        <li><a href="?rss">RSS</a></li>
    </ul>';
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
    </ul>
    <p class="skip"><a href="#haut">Retourner en haut</a></p>';
    endif;
    echo '
    <p class="colophon">©'.$titre_du_journal.',certains droits réservés…<br />
    Réagissez ! Écrivez-moi : '.$auteur.'
    <a href="http://jeunes-science.org/kiwi/" title="kiwi, le générateur endémique"><img src="http://jeunes-science.org/kiwi/colophon.png" alt="kiwi"></a></p>
</body>
</html>';
endif;

#   En cas de question(s) quant à ce code, n'hésitez pas à me contacter :
#   me[at]aljes[dot]me

?>
