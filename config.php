<?php 
$cyaneus = array(
	/**
	 * Informations about your site
	 */
	
	// Site name
	'name' => 'Le colibri libre',
	// Site main url, must be end with /
	'url' => 'http://localhost:8000/',
	// Language of your site
	'language' => 'fr_FR',
	// Webmaster
	'author' => 'dhoko',
	// SIte description
	'description' => 'Le blog d\'un intégrateur intégriste',
	// Template Name
	'template_name' => 'base',
	// Thumbnail width
	'thumb_w' => 600,
	// Date format to display
	'date_format' => 'd/m/Y',

	/**
	 * Cyaneus Confiuration
	 */
	
	// Tags you want to edit in your posts
	'tags' => 'title,url,date,tags,description,author',
	// The generator
	'generator' => 'Cyaneus 1.0 B2',
	// Put your drafts in this folder
	'draft' => 'draft',
	// Your posts builds will be store in this folder
	'articles' => 'articles',
	// Template folder
	'template' => 'template',
	// To change the destination of your site.
	// . = Same folder as your config.php
	'folder_main_path' => 'hookonsdesbois',


	/**
	 * Rebuild Key
	 * You can rebuild your site online but to prevent other to do it you must set a password here.
	 */
	'rebuild_key' => 'test',

	/**
	 * Git Hook Mechanism
	 * You build your site from Github and use webhook to pull your content to cyaneus.
	 * You must specify some informations to verify the hook origin
	 */
	// Your registred mail from git
	'email_git' => 'aurelien@procheo.fr',
	// Your pseudo on git
	'name_git' => 'dhoko',
	// url of the repos on github
	'url_git' => 'github.com/dhoko/blog/master/'
);