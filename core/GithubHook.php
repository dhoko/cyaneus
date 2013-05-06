<?php
class GithubHook extends Cyaneus {

	private $json = null;
	private $post = array();

	/**
	 * Init a hook with the datas send by Github
	 * @param Array $payload Gihub JSON from $_POST['payload']
	 */
	public function __construct($payload) {
		$this->json = $payload;
	}

	/**
	 * Get a post and images attach to a webhook
	 * @return Array [post,pict] content for a post
	 */
	public function get() {

		try {
			$this->grabFiles();
			return $this->post;
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return array();
		}
	}

	/**
	 * Read each files from a hook to build them in DRAFT and find the post
	 */
	private function grabFiles() {

		$files = array();
		$db = array();
		$_base = 'https://raw.github.com/dhoko/blog/master/';
		foreach ($this->json['head_commit']['added'] as $file) {
			klog('GITHUB Try to get content from : '.$_base.$file);
			$_timeStamp = (new DateTime($this->json['head_commit']['timestamp']))->format('U');
			$folder = explode('/', $file);
			$files[] = array(
				'path'    => $file,
				'folder'  => $folder[0],
				'content' => file_get_contents($_base.$file),
				);
			if(!isset($db['s'.$_timeStamp])) $db['s'.$_timeStamp] = $folder[0];
			if (in_array(pathinfo($file, PATHINFO_EXTENSION), $this->postFilesExt)) {
				$this->post['post'] = file_get_contents($_base.$file);
				klog('HOOK - Post content found');
			}

			if (in_array(pathinfo($file, PATHINFO_EXTENSION), $this->pictFilesExt)) {
				$this->post['pict'][] = $file;
				klog('HOOK - Picture found');
			}
		}

		if(empty($this->post['post'])) 
			throw new Exception('No Post found for the commit: '.$this->json->compare);

		$this->build($files);
		$this->update($db);
	}
	
	/**
	 * Create a static files in DRAFT from webHook files.
	 * @param  Array $files Array of files from WebHook
	 */
	private function build(Array $files) {
		foreach ($files as $e) {
			if(!file_exists(DRAFT.DIRECTORY_SEPARATOR.$e['folder']))
				mkdir(DRAFT.DIRECTORY_SEPARATOR.$e['folder']);
			
			file_put_contents(DRAFT.DIRECTORY_SEPARATOR.$e['path'],$e['content'] );
		}
	}
}