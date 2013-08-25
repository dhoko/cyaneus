<?php
class Cyaneus {

	/**
	 * Init Cyaneus - it will build required folders :
	 * - Site destination folder
	 * - Post destination folder
	 * - Move your CSS template to your site folder
	 */
	public static function init() {

		if(!file_exists(USERDATA.DIRECTORY_SEPARATOR)) {
			mkdir(USERDATA.DIRECTORY_SEPARATOR);
			klog('Create user data folder');
		}
		if(!file_exists(REPOSITORY.DIRECTORY_SEPARATOR)) {
			mkdir(REPOSITORY.DIRECTORY_SEPARATOR);
			klog('Create site folder');
		}
		if(!file_exists(REPOSITORY.DIRECTORY_SEPARATOR.POST)) {
			mkdir(REPOSITORY.DIRECTORY_SEPARATOR.POST);
			klog('Create site folder for posts');
		}

		if(!file_exists(DRAFT)) {
			mkdir(DRAFT);
			klog('Create site folder for drafts');
		}

		if(!file_exists(REPOSITORY.DIRECTORY_SEPARATOR.'style.css')) {
			copy(TEMPLATEPATH.'style.css',REPOSITORY.DIRECTORY_SEPARATOR.'style.css');
			klog('Moving CSS file to defautl path');
		}else{
			unlink(REPOSITORY.DIRECTORY_SEPARATOR.'style.css');
		}

		if(!file_exists(USERDATA.DIRECTORY_SEPARATOR.'cyaneus.sqlite')) {
			Db::createInstance();
			klog('Create a new DB instance');
		}

		klog('Init DB connection');
		Db::init();
	}
	/**
	 * Build new HTML pages
	 * @param  Array $data Return of a hook or anything else [status,msg,files,timestamp]
	 * @return array       build status [status,msg,path]
	 */
	public static function make(Array $data) {
		try {
			// if(!empty($data['files']['removed'])) return self::rebuild();
			$posts = self::buildPosts($data);
			$pages = self::buildPages();
			
			return array(
					'status' => 'success',
					'msg' => 'this build is a success',
					'info' => array(
						'pages' => $posts['msg'],
						'posts' => $pages['msg']
						)
				);
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return [
				'status' => 'error',
				'msg' => $e->getMessage()
				];
		}
	}

	/**
	 * Build each pages of your site
	 * - index.html
	 * - archives.html
	 * - rss.xml
	 * - sitemap.xml
	 * @return Array [status,msg]
	 */
	public static function buildPages() {
		try {
			$sitemap = [];
			$list    = [];
			// Remove previous version of these files
			Factory::destroy([
				['path' => REPOSITORY.'index.html'],
				['path' => REPOSITORY.'archives.html'],
				['path' => REPOSITORY.'rss.xml'],
				['path' => REPOSITORY.'sitemap.xml'],
			],'main');

			// Find all post
			$posts = Post::all();

			foreach ($posts as $post) {
				$info = Factory::getContent(DRAFT.DIRECTORY_SEPARATOR.$post->path);
				$config = $info['config'] + [
					'added_time' => $post->added_time,
					'last_update' => $post->last_update,
					'content' => Factory::convert($info['raw'])
					];
				// Build an array of each key we need to build templates
				$list[] = self::buildKeyTemplate($config);
			}

			$sitemap[] = [
				'type'            => 'page',
				'post_url'        => 'index.html',
				'timestamp_upRaw' => date('U')
				];
			$sitemap[] = [
				'type'            => 'page',
				'post_url'        => 'archives.html',
				'timestamp_upRaw' => date('U')
				];
			$sitemap = array_merge($sitemap,$list);
			// Build a page
			Factory::page($list);

			/// Build a site map
			Factory::sitemap($sitemap);
			return [
				'status' => 'success',
				'msg' => 'Page build is a success'
				];
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return [
				'status' => 'error',
				'msg' => $e->getMessage()
				];
		}

	}

	/**
	 * Build a html file for each post we can find or those we need to update
	 * @param  Array  $data List of post
	 * @return Array  [status,msg]
	 */
	private static function buildPosts(Array $data) {
		try {
			$list = [];
			$pict =  [];
			// Find all post from the current update
			$posts = Post::all($data['timestamp']);

			foreach ($posts as $post) {

				$info = Factory::getContent(DRAFT.DIRECTORY_SEPARATOR.$post->path);
				$config = $info['config'] + [
					'added_time'  => $post->added_time,
					'last_update' => $post->last_update,
					'content'     => Factory::convert($info['raw'])
					];
				// Build an array of each key we need to build templates
				$list[] = self::buildKeyTemplate($config);
				$pict = array_merge_recursive($pict,$post->picture);
				Post::update('id='.$post->id,['name' => $config['title']]);
			}

			// Build each post
			Factory::post($list);
			// Build pictures for a post
			Factory::pictures($pict);

			return [
				'status' => 'success',
				'msg'    => 'Post build is a success'
				];
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return [
				'status' => 'error',
				'msg'    => $e->getMessage()
				];
		}
	}

	/**
	 * Rebuild from scrach your website
	 * It will be usefull for example if you change a template etc...
	 * @return Array [status,msg,(rebuild)]
	 */
	public static function rebuild() {

		try {
			Factory::drop();
			self::init();
			klog('Rebuild the website - files and folder updated');

			// Find each post we have
			$data = Factory::find();
			klog('Rebuild the website - Find some files');

			// For each one we make a record in the database
			foreach ($data as $key => $file) {

				// Detect if we have an attachement file or not
				if(isset($file['pict'])) {
					$file['draft'] = [$file['draft']];
					Post::recordWithPicture($file);
					klog('Rebuild the website - Find a post with an image');
				}else {
					Post::create([$file['draft']]);
					klog('Rebuild the website - Find a post');
				}
			}

			// Rebuild the site : let's go !
			klog('Rebuild the website - init phase done.');
			self::make(['timestamp' => '1970-01-01']);

			return [
				'status'  => 'success',
				'msg'     => 'Drop project is a success',
				];
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return [
				'status' => 'error',
				'msg'    => $e->getMessage()
				];
		}

	}

	/**
	 * Main configuration for Template's keys
	 * These keys are available in a template
	 * @param  Array $info Default configuration
	 * @return Array       template keys
	 */
	private static function buildKeyTemplate($info) {
		return [
			'post_url'         => POST.DIRECTORY_SEPARATOR.$info['url'].".html",
			'post_title'       => $info['title'],
			'post_date'        => (new DateTime($info['added_time']))->format(DATE_FORMAT),
			'post_update'      => (new DateTime($info['last_update']))->format(DATE_FORMAT),
			'post_date_rss'    => date('D, j M Y H:i:s \G\M\T',(new DateTime($info['last_update']))->format('U')),
			'post_description' => $info['description'],
			'post_content'     => $info['content'],
			'post_author'      => $info['author'],
			'post_tags'        => $info['tags'],
			'timestamp'        => $info['added_time'],
			'timestamp_up'     => $info['last_update'],
			'timestamp_upRaw'  => (new DateTime($info['last_update']))->format('U'),
		];
	}
}