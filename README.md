# Cyaneus

A static PHP blog generator

## How to

1. Download Cyaneus : [zipfile](https://github.com/dhoko/cyaneus/archive/master.zip)
2. Upload Cyaneus
3. Change the default configuration in **index.php***
4. Done


### Configuration 


```PHP
define('TITLE_SITE', 'XXX');
define('URL', 'XXX'); // You must add / at the end -> http://localhost:8042/
define('AUTHOR', 'XXX');
define('GENERATOR', 'XXX 1.0 http://jeunes-science.org/kiwi/');
define('DESCRIPTION', 'Un journal web généré par kiwi, le générateur endémique.');
define('LANGUAGE', 'fr'); #   Langue du journal.
define('DRAFT', 'draft'); #   Langue du journal.
define('ARTICLES', 'articles'); #   Langue du journal.
define('TAGS','title,url,date,tags,description,author');

define('THUMB_W', 600);
define('REBUILD_KEY', 'regenerate');

define('EMAIL_GIT', "XXX");
define('NAME_GIT', "XXX");
define('URL_GIT', "XXX");
```

#### TITLE_SITE

Your site name

#### URL

Main url (Your must put  **/** at the end of your url)
> Cyaneus is uploaded to / -> http://custom-domaim.com/ 
> Cyaneus is uploaded to bloc/ -> http://custom-domaim.com/blog/

#### AUTHOR

Chuck Norris

#### GENERATOR

Cyaneus build your site so...

#### DESCRIPTION

Describe your site

#### LANGUAGE

Ex: en_US [more information](http://xml.coverpages.org/iso639a.html)

#### DRAFT

Folder to put your drafts (markdown files, **.md or .markdown**)

#### ARTICLES

Folder to generate your html posts

#### TAGS

List of allowed tags to put in <head>

#### THUMB_W

Thumbnail with

#### REBUILD_KEY

This key will be used for rebuild your site 

#### EMAIL_GIT

Your Github email

#### NAME_GIT

Your Github name

#### URL_GIT

URL of github repository where you commit your posts

Fork from **Kiwi** : [Kiwi](http://jeunes-science.org/kiwi/) - [Repository](http://darcsden.com/aljes/kiwi-0)

Thanks [@aljes](https://twitter.com/aljes)

