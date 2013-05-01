# Cyaneus

A static PHP blog generator

## How to

1. Download Cyaneus : [zipfile](https://github.com/dhoko/cyaneus/archive/master.zip)
2. Upload Cyaneus
3. Change the default configuration in **config.php**
4. Done

## Write a post

You can write a post with the Markdown syntax. 

### Basic draft for cyaneus

```
title="This is the end"
tags="css3,demosthène,lui"
author="dhoko"
description="To be or not to be ?"

==POST==


Put here your post content

```

> title="XXX"&cie are custom elements from TAGS. You must specify at least title='XXX'.

> ==POST== You must write this between the header conf and your content,

## Templating

To build a new template, you just have to duplicate *base*.

### Custom var accessible in a template

You can access to many variables in your template with this syntax : {{my_var}}

#### About the site, accessible in every template

- lang
- author
- content
- generator
- site_title
- site_url
- site_description
- css_url
- rss_url

#### About a post accessible in content-* and post.html

- post_url
- post_title
- post_content
- post_description
- post_author
- post_tags
- post_date
- post_date_rss

### Exemple of a template

```html
<!doctype html>
<html lang="{{lang}}">
<head>
  <meta charset="UTF-8">
	<title>{{post_title}} - {{site_title}}</title>
	<meta name="description" content="{{post_description}}"/>
	<meta name="author" content="{{post_author}}">
	<link rel="alternate" type="application/rss+xml" href="{{rss_url}}" />
	<link rel="stylesheet" type="text/css" href="{{css_url}}" />
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

Each page have a base :
- main file such as `index.html`
- loop file such as content-index.html

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

### Version 1.0 Final - enhancement

You will be able to build custom templates for other pages. Today we only have:

- archives
- index (home)
- rss

It's static.

## Informations

Cyaneus logs some informations in **data/**
- log.txt 
- log_error.txt 
- log_server.txt 

Fork from **Kiwi** : [Kiwi](http://jeunes-science.org/kiwi/) - [Repository](http://darcsden.com/aljes/kiwi-0)

Thanks [@aljes](https://twitter.com/aljes)

