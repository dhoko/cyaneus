<?php
class Cyaneus {


	public static function init() {
		if(!file_exists(REPOSITORY.DIRECTORY_SEPARATOR)) {
			klog('Create site folder');
			mkdir(REPOSITORY.DIRECTORY_SEPARATOR);
		}
		if(!file_exists(REPOSITORY.DIRECTORY_SEPARATOR.POST)) {
			klog('Create site folder for posts');
			mkdir(REPOSITORY.DIRECTORY_SEPARATOR.POST);
		}

		if(!file_exists(REPOSITORY.DIRECTORY_SEPARATOR.'style.css')) {
			klog('Moving CSS file to defautl path');
			copy(TEMPLATEPATH.'style.css',REPOSITORY.DIRECTORY_SEPARATOR.'style.css');
		}
	}
	/**
	 * Build new HTML pages
	 * @param  Array $data Return of a hook or anything else [status,msg,files,timestamp]
	 * @return array       build status [status,msg,path]
	 */
	public static function make(Array $data) {
		try {
			if(!empty($data['files']['removed'])) return self::rebuild();
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
			return array(
				'status' => 'error',
				'msg' => $e->getMessage()
				);
		}
	}

	public static function buildPages() {
		try {
			$sitemap = array();
			Factory::destroy(array(
				array('path' => REPOSITORY.'index.html'),
				array('path' => REPOSITORY.'archives.html'),
				array('path' => REPOSITORY.'rss.xml'),
				array('path' => REPOSITORY.'sitemap.xml'),
			),'main');

			$posts = Post::all();

			foreach ($posts as $post) {
				$info = Factory::getContent(DRAFT.DIRECTORY_SEPARATOR.$post->path);
				$config = $info['config'] + array(
					'added_time' => $post->added_time,
					'last_update' => $post->last_update,
					'content' => Factory::convert($info['raw'])
					);
				// Build an array of each key we need to build templates
				$list[] = self::buildKeyTemplate($config);
			}

			$sitemap[] = array(
				'type' => 'page',
				'post_url' => 'index.html',
				'timestamp_upRaw' => date('U')
				);
			$sitemap[] = array(
				'type' => 'page',
				'post_url' => 'archives.html',
				'timestamp_upRaw' => date('U')
				);
			$sitemap = array_merge($sitemap,$list);
			Factory::page($list);
			Factory::sitemap($sitemap);
			return array(
					'status' => 'success',
					'msg' => 'Page build is a success'
				);
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return array(
				'status' => 'error',
				'msg' => $e->getMessage()
				);
		}

	}

	private static function buildPosts(Array $data) {
		try {
			$list = array();
			$pict =  array();
			$posts = Post::all($data['timestamp']);

			foreach ($posts as $post) {
				$info = Factory::getContent(DRAFT.DIRECTORY_SEPARATOR.$post->path);
				$config = $info['config'] + array(
					'added_time' => $post->added_time,
					'last_update' => $post->last_update,
					'content' => Factory::convert($info['raw'])
					);
				// Build an array of each key we need to build templates
				$list[] = self::buildKeyTemplate($config);
				$pict = array_merge_recursive($pict,$post->picture);
				Post::update('id='.$post->id,array('name' => $config['title']));
			}

			Factory::post($list);
			Factory::pictures($pict);

			return array(
					'status' => 'success',
					'msg' => 'Post build is a success'
				);
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return array(
				'status' => 'error',
				'msg' => $e->getMessage()
				);
		}
	}

	public static function rebuild() {

		try {
			Factory::drop();
			self::init();
			return array(
				'status' => 'success',
				'msg' => 'Drop project is a success',
				'rebuild' => self::make(array('timestamp' => '1970-01-01'))
				);
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return array(
				'status' => 'error',
				'msg' => $e->getMessage()
				);
		}

	}

	private static function buildKeyTemplate($info) {
		return array(
			'post_url' => POST.DIRECTORY_SEPARATOR.$info['url'].".html",
			'post_title' => $info['title'],
			'post_date' => (new DateTime($info['added_time']))->format(DATE_FORMAT),
			'post_update' => (new DateTime($info['last_update']))->format(DATE_FORMAT),
			'post_date_rss' => date('D, j M Y H:i:s \G\M\T',(new DateTime($info['last_update']))->format('U')),
			'post_description' => $info['description'],
			'post_content' =>  $info['content'],
			'post_author' =>  $info['author'],
			'post_tags' =>  $info['tags'],
			'timestamp' => $info['added_time'],
			'timestamp_up' => $info['last_update'],
			'timestamp_upRaw' => (new DateTime($info['last_update']))->format('U'),
		);
	}
}