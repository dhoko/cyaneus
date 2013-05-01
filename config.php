<?php 
$cyaneus = array(
	/**
	 * Informations about your site
	 */
	
	// Site name
	'name' => '',
	// Site main url, must be end with /
	'url' => '',
	// Language of your site
	'language' => '',
	// Webmaster
	'author' => '',
	// SIte description
	'description' => '',
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
	'folder_main_path' => 'cyaneus',


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
	'email_git' => '',
	// Your pseudo on git
	'name_git' => '',
	// url of the repos on github
	// Do not add the https:// 
	// ex: https://github.com/dhoko/blog/ => github.com/dhoko/blog/master/
	'url_git' => ''
);