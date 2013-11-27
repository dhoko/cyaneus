<?php
$cyaneus = [
  /**
   * Informations about your site
   */

  // Site name
  'name' => 'Le Colibri libre',
  // Site main url, must be end with /
  'url' => 'http://localhost:8000/',
  // Language of your site
  'language' => 'fr-FR',
  // Webmaster
  'author' => '',
  // SIte description
  'description' => 'le troll volant',
  // Template Name
  'template_name' => 'dhoko-me',
  // Thumbnail width
  'thumb_w' => 600,
  // Date format to display
  'date_format' => 'd/m/Y',
  // Your timezone
  'timezone' => "Europe/Paris",

  /**
   * Cyaneus Confiuration
   */

  // Tags you want to edit in your posts
  'tags' => 'title,url,date,tags,description,author,plang',
  // The generator
  'generator' => 'Cyaneus 1.0 RC2s',
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
   * Specify the path of your zipball for your repository
   * You can find the path if you copy the link on the download Zip button
   * on github (At the bottom of the sidebar, rigth of the window)
   *
   * The structure is :
   *   - https://github.com/ + PSEUDO + REPOSITORY + /zipball/ + BRANCH
   * The default branch is master
   */
  'repositoryUrl' => 'https://github.com/dhoko/blog/zipball/angular_post'
];
