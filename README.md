# Cyaneus

A static PHP blog generator from Github WebHook

## How to

1. Download Cyaneus : [zipfile](https://github.com/dhoko/cyaneus/zipball/master)
2. `cd cyaneus/` and `composer install`
3. Change the default configuration in **config.php**
4. Add your repository Zipball URL in the config at the key **repositoryURL** : ex `https://github.com/dhoko/cyaneus/zipball/master`. The last param (master here), is the name of a branch
5. Upload Cyaneus
6. Create a repository on Github and attach a webhook to `yousite.me/upload-folder/?github`
7. Done

> You must use this script if you have : 
- Wget on your system, yup it does not work on a Windows Server yet.


## Write a post

You can write a post with the Markdown syntax. 

### Basic draft for cyaneus

You write these informations with [YAML](http://wikipedia.org/wiki/YAML).

```
title: The Hitchhiker's Guide to the Galaxy
tags: 42,h2g2,Science fiction
author: Douglas Adams
description: The Hitchhiker's Guide to the Galaxy is a comic science fiction series created by Douglas Adams.
plang: en
picture: 
  trollidee:
   file: IMG_20130630_235316.jpg
   width: 150
   height: 150
   crop: true
   description: Une image de test
  image2:
   file: IMG_20130630_235316.jpg
   width: 500


==POST==


Put here your post content and add your image with {{picture_trollidee}} or the second picture with 
{{picture_image2}}
```

- **plang** : Language of your post (if one day you write a french post and your blog not in french.)

> The minimal setup for a post is `title: One Ring to rule them all, One Ring to find them`. 

> You must specify **==POST==**, Cyaneus will extract informations before it and the post after.

### Name of the file

The filename have to be like : `2013-11-27-blablabla.md` (replace blablabla :) )

#### Insert an image in your post

You can add picture in your post, you have to declare them in your markdown file.

```yaml
picture: 
  trollidee:
   file: IMG_20130630_235316.jpg
   width: 150
   height: 150
   crop: true
   description: Une image de test
  image2:
   file: IMG_20130630_235316.jpg
   width: 500
```

With this code you have configure 2 picture. You can call them in your markdown with `{{picture_NAME}}`, the *NAME* is trollidee or image2 in this exemple.


## Templating

To build a new template, you just have to duplicate *base*.

### Custom var accessible in a template

You can access to many variables in your template with this syntax : `{{my_var}}`

#### About the site, accessible in every template

- site_lang
- site_author
- site_content
- site_generator
- site_title
- site_url
- site_description
- site_css_url
- site_rss_url

#### About a post accessible in content-* and post.html

- **post_url** : Full url of your post
- **post_title** 
- **post_date** : Same format specify in config.php
- **post_lang** : Language of the post (cf plang @todo modify for RC2)
- **post_update** : (RC1 - Same as post_date)
- **post_date_rss** : RSS format for post_date
- **post_description** : The description tou set in your post
- **post_content** 
- **post_author** 
- **post_tags** 
- **post_timestamp** : Datetime (Dafuq ? @todo modify for RC2)
- **post_timestamp_up** : Datetime (Dafuq ? @todo modify for RC2)
- **post_timestamp_upRaw** : Real timestamp

### Exemple of a template

```html
<!doctype html>
<html lang="{{lang}}">
<head>
    <meta charset="UTF-8">
  <title>{{post_title}} - {{site_title}}</title>
  <meta name="description" content="{{post_description}}"/>
  <meta name="author" content="{{post_author}}">
  <link rel="alternate" type="application/rss+xml" href="{{site_rss_url}}" />
  <link rel="stylesheet" type="text/css" href="{{site_css_url}}" />
  <link rel="shortcut icon" type="image/png" href="favicon.png" />
</head>
<body>
<section id="main">
  <header>
    <h1>
    <a href="{{site_url}}" title="{{site_description}}">{{site_title}}</a></h1>
  </header>
{{navigation}}
<article class="post">
  <header>
    <h1 class="post-title">{{post_title}}</h1>
    <time class="post-date">{{post_date}}</time>
  </header>
  {{post_content}}
  <footer>
  By <strong>{{post_author}}</strong>
  </footer>
</article>
<p class="skip"><a href="#haut">Retourner en haut</a></p>
</section>
</body>
</html>
```

This is the default template for a post. 

### Construction

Each page have a base it's *name*html and content-*name*.html:

- main file such as `index.html`
- loop file such as `content-index.html`

We loop on content-index to build a string. This string will be append to `{{content}}`.

Ex: 

```html
<section id="main">
  <header>
    <h1>
    <a href="{{site_url}}" title="{{site_description}}">{{site_title}}</a></h1>
  </header>
  {{navigation}}
  {{content}}
<p class="skip"><a href="#haut">Retourner en haut</a></p>
</section>
```

### So long and thanks for all the fish !

Cyaneus RC1 builds :

- index.html
- archive.html
- sitemap.xml
- rss.xml
- page per post

Fork from **Kiwi** : [Kiwi](http://jeunes-science.org/kiwi/) - [Repository](http://darcsden.com/aljes/kiwi-0)

Thanks [@aljes](https://twitter.com/aljes)

