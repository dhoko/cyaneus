<?php 
class Factory {

	/**
	 * Build a page for the site
	 * @param Array $content config array of each post
	 * @param string page to build
	 * @return Bool
	 */
	public static function page(Array $content) {
		
		$build = [];
		$template = new Template();

		// Config array for a page
		$config =  function($name)  use($template, $content) {
			$ext = ($name !== 'rss') ? 'html' : 'xml';
			return [
				'folder' => FOLDER_MAIN_PATH,
				'path' => FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.$name.'.'.$ext,
				'content' => $template->page($name,['content' => $content])
			];
		};

		$build[] = $config('index');
		$build[] = $config('archives');
		$build[] = $config('rss');

		self::build($build,'post');
	}

	public static function sitemap($data) {
		$template = new Template();
		self::build([[
			'folder' => FOLDER_MAIN_PATH,
			'path' => FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.'sitemap.xml',
			'content' => $template->sitemap($data)
			]],'post');
	}

	/**
	 * Build a page for each post
	 * @param Array $content config array of each post
	 * @param string page to build
	 * @return Bool
	 */
	public static function post(Array $content) {
		$build = array();
		$template = new Template();
		foreach ($content as $e) {
			$build[] = array(
				'folder' => FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.POST,
				'path' => FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.$e['post_url'],
				'content' => $template->post($e)	
				);
		}
		self::build($build,'post');
	}

	/**
	 * Create a static files in DRAFT from webHook files.
	 * @param  Array $files Array of files from WebHook
	 * @param  String $type Files to build
	 */
	public static function build(Array $data,$type = 'draft') {
		$elemets = ($type === 'draft') ? DRAFT : rtrim(STORE,DIRECTORY_SEPARATOR);
		foreach ($data as $files) {
			if(!file_exists($elemets.DIRECTORY_SEPARATOR.$files['folder']))
					mkdir($elemets.DIRECTORY_SEPARATOR.$files['folder']);
				
			if(file_exists($elemets.DIRECTORY_SEPARATOR.$files['path'])) unlink($elemets.DIRECTORY_SEPARATOR.$files['path']);
			file_put_contents($elemets.DIRECTORY_SEPARATOR.$files['path'],$files['content'] );
			klog('Build file success for '.$files['path']);
		}
	}

	/**
	 * Delete a file if we delete it from a commit
	 * @param  Array $files Array of files from WebHook
	 * @param  String $type Files to destroy
	 */
	public static function destroy(Array $files,$type = 'draft') {
		$elemets = ($type === 'draft') ? DRAFT : STORE.POST;
		if($type === 'main') $elemets = SITE;
		foreach ($files as $e) {
			if(file_exists($elemets.DIRECTORY_SEPARATOR.$e['path'])) unlink($elemets.DIRECTORY_SEPARATOR.$e['path']);
			
			klog('Delete file success for '.$e['path']);
		}
	}

	/**
	 * Drop all compiled files from your site in order to rebuild it
	 * @return bool 
	 */
	public static function drop() {
		
		klog('Drop project site');
		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(REPOSITORY,FilesystemIterator::SKIP_DOTS),
             RecursiveIteratorIterator::CHILD_FIRST);
		$ext = ['css','xml','html','htm','jpg','png'.'jpeg','webp','gif','bmp'];
		foreach($files as $file) {
			if(!$file->isFile()) continue;

			if(in_array($file->getExtension(), $ext)) {
				klog('Remove file : '.$file->getRealPath());
				unlink($file->getRealPath());
			}
		}
	}

	/**
	 * Will find each drafts from DRAFT. 
	 * File must have these extensions : md|markdown
	 * @return Array array of ['build':timestamp,file,path]
	 */
	public static function find() {
		$files          = []; 
		$readable_draft = ['md','markdown'];
		$draftPath      = dirname(__FILE__).DIRECTORY_SEPARATOR.DRAFT.DIRECTORY_SEPARATOR;
		$iterator       = new RecursiveDirectoryIterator($draftPath,RecursiveIteratorIterator::CHILD_FIRST);

		klog('Looking for drafts');
		foreach(new RecursiveIteratorIterator($iterator) as $file) {
			if($file->isFile()) {
				$md5 = md5($file->getPath());
				if (in_array($file->getExtension(), $readable_draft)) {
					$files[$md5]['draft'] = [
						'build' => $file->getMTime(),
						'file'  => $file->getfilename(),
						'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
					];
				}
				if( in_array($file->getExtension(), ["jpg",'png','gif','jpeg']) ) {
					$files[$md5]['pict'] = [
						'build' => $file->getMTime(),
						'file'  => $file->getfilename(),
						'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
					];
				}

				if(empty($files[$md5]['draft'])) unset($files[$md5]);
			} 
		}
		return $files;
	}

	public static function getContent($file) {
		if(file_exists($file)) {
			klog('New draft found : '.$file);
			$content = file_get_contents($file);
			// We extract headers from the draft
			$config = strstr($content,'==POST==', true );
			// Remove headers from the draft to keep the content
			$article = str_replace('==POST==','',strstr($content,'==POST=='));

			return [
				'config' => self::getTags($config),
				'raw' => $article
			];
		}
		return array();
		
	}

	/**
	 * Loop on each TAGS in order to build an array [tag:value]
	 * @param string Header from a post
	 * @return Array [tag:value]
	 */
	private static function getTags($post) {
		$info = [];
		$kiwi_tags = explode(',', TAGS);
		foreach ($kiwi_tags as $tag) {
			$info[$tag] = self::info($post,$tag);
		}
		// Rebuild some informations
		if(empty($info['url'])) $info['url'] = self::url($info['title']);
		return $info;
	}

	/**
	 * Find tags from a post from its header.
	 * info('author="dhoko"','author') => dhoko
	 * @param string Header of a post
	 * @param string Tag tag to find cf TAGS
	 * @return string tag value
	 */
	private static function info($data,$tag) {
		preg_match('/"([^"]+)"/',strstr($data,$tag),$match);
		return (isset($match[1])) ? $match[1] : '';
	}

	/**
	 * Convert raw content to HTML
	 * @param  string $data   Your draft
	 * @param  string $format convertion format
	 * @return string         html
	 * @todo add convertion format
	 */
	public static function convert($data,$format = 'markdown') {
		return SmartyPants(Markdown($data));
	}

	/**
	 * Build a valid url from a title
	 * New Firefox OS app : XBMC remote -> new-firefox-os-app-xbmc-remote
	 * @param string 
	 * @return string
	 */
	public static function url($path) {
	    $url = str_replace('&', '-and-', $path);
	    $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
	    $url = str_replace(' ', '-', $url);
	    $url = str_replace('--', '-', $url);
	    return strtolower($url);
	}

	public static function pictures(Array $content) {
		foreach ($content as $picture) {
			self::picture((array)$picture);
		}
	}
	/**
	 * Build attachement picture for a post
	 * @param Array $config Configuration for an image
	 * @return bool
	 */
	public static function picture(Array $config) {

		klog('Find an image attach to the current post');
		// [0] => w ---- [1] => h
		$_info = getimagesize(DRAFT.DIRECTORY_SEPARATOR.$config['path']);
		$image = new PHPImageWorkshop\ImageWorkshop(array(
			    'imageFromPath' => DRAFT.DIRECTORY_SEPARATOR.$config['path'],
		));
		if (THUMB_W < $_info[0]) {
			$image->resizeInPixel(THUMB_W, null, true);
		}else{
			$image->resizeInPixel($_info[0], null, true);
		}
		 //backgroundColor transparent, only for PNG (otherwise it will be white if set null)
		klog('Record file config '.var_export($config,true));
		klog('Record file '.STORE.FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.POST.DIRECTORY_SEPARATOR.$config['basename']);
		// (file_path,file_name,create_folder,background_color,quality)
		return $image->save(STORE.FOLDER_MAIN_PATH.DIRECTORY_SEPARATOR.POST.DIRECTORY_SEPARATOR, $config['basename'], true, null, 85);
	}

}