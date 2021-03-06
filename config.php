<?php
$cyaneus = [
  /**
   * Informations about your site
   */

  // Site name
  'name' => '',
  // Site main url, must be end with /
  'url' => '',
  // Language of your site
  'language' => 'fr-FR',
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
  // Your timezone
  'timezone' => "Europe/Paris",
  // Use comments
  'comments' => true,
  /**
   * Cyaneus Confiuration
   */

  // Tags you want to edit in your posts
  'tags' => 'title,url,date,tags,description,author,plang,picture',
  // The generator
  'generator' => 'Cyaneus 1.0',
  // Put your drafts in this folder
  'draft' => 'draft',
  // Your posts builds will be store in this folder
  'articles' => 'articles',
  // You pages will be build in this folder
  'pages' => 'pages',
  // Template folder
  'template' => 'template',
  // To change the destination of your site.
  'folder_main_path' => 'cyaneus',
  // Default class for a picture in a post
  'picture_class' => 'post-img',


  /**
   * Password
   * Another security to prevent someone to regenerate your site each time he try to contact the URL (if you do not customize the url)
   */
  'hook_password' => 'test',

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
  'repositoryUrl' => ''
];
