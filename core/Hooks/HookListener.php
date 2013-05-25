<?php
/**
* HookListener
* Main class to manipulate Hooks
*/
abstract class HookListener {
	
	protected $base = '';
	protected $postFilesExt = array('md','markdowm');
	protected $pictFilesExt = array('jpg','jpeg','gif','webp','png','bmp','ico');

	abstract public function __construct($payload);
	abstract public function get();
	abstract protected function grabFiles();

	/**
	 * Get the content of each files from the hook
	 * @return Array [post,pict]
	 */
	protected function getContentPostFiles($date) {
		$data = array('pict' => array(),'post' => array());

		$result_post = Post::findByDate($date);
		$result_pict = Picture::findByDate($date);

		foreach ($result_post as $post) {
			$data['post'][] = array(
				'path' => $post->pathname,
				'folder' => current(explode('/', $post->pathname)),
				'content' => file_get_contents($this->base.$post->pathname)
				);
		}
		foreach ($result_pict as $pict) {
			$data['pict'][] = array(
				'path' => $pict->pathname,
				'folder' => current(explode('/', $pict->pathname)),
				'content' => file_get_contents($this->base.$pict->pathname)
				);
		}
		return $data;
	}

	protected function addedFiles() {
		$data = $this->grabFiles();
		klog('HOOK '.$data['total'].' files found from this webhook');
		if($data['total'] > 0) {
			Post::recordWithPicture($data);
			$files = $this->getContentPostFiles($data['date']);
			Factory::build($files['post']);
			Factory::build($files['pict']);
		}
	}

	protected function modifiedFiles() {
		$data = $this->grabFiles('modified');
		klog('HOOK '.$data['total'].' modified files found from this webhook');
		if($data['total'] > 0) {
			$conditions = implode(' AND ', array_map(function($e) {return 'pathname="'.$e[0].'"';}, $data['post']));
			Post::update($conditions,array('last_update'=> $data['date']));
			Factory::destroy($this->listFiles($data));

			$files = $this->getContentPostFiles($data['date']);
			Factory::build($files['post']);
			Factory::build($files['pict']);
		}
	}

	protected function removedFiles() {
		$data = $this->grabFiles('removed');
		klog('HOOK '.$data['total'].' deleted files found from this webhook');
		if($data['total'] > 0) {
			$conditions = implode(' AND ', array_map(function($e) {return 'pathname="'.$e[0].'"';}, $data['post']));
			Post::destroy($conditions);
			Factory::destroy($this->listFiles($data));
		}
	}

	private function listFiles($data) {
		$files = array();
		foreach ($data['pict'] as $pict) {
			$files[] = array(
				'folder' => current(explode('/', $pict)),
				'path' => $pict
				);
		}
		foreach ($data['post'] as $post) {
			$files[] = array(
				'folder' => current(explode('/', $post[0])),
				'path' => $post[0]
				);
		}
		return $files;
	}
}