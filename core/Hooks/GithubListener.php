<?php
class GithubListener extends HookListener {

	private $json = null;
	private $post = array();

	/**
	 * Init a hook with the datas send by Github
	 * @param Array $payload Gihub JSON from $_POST['payload']
	 */
	public function __construct($payload) {
		$this->base = 'https://raw.'.URL_GIT;
		$this->json = $payload;
	}

	/**
	 * Get new updates from github
	 * @return Array [status,msg] status = success||error
	 */
	public function get() {
		
		try {
			$this->addedFiles();
			// $this->modifiedFiles();
			// $this->removedFiles();
			return array('status'=>'success','msg'=>'');
		} catch (Exception $e) {
			klog($e->getMessage(),'error');
			return array('status'=>'error','msg'=>$e->getMessage());
		}
	}

	/**
	 * Read each files from a hook to build them in DRAFT and find the post
	 * @param String $status 
	 * @return Array [post,pict,total]
	 */
	protected function grabFiles($status = 'added') {

		$db = array();
		$pict = array();
		$timestamp = (new DateTime($this->json['head_commit']['timestamp']))->format('Y-m-d H:i:s');

		foreach ($this->json['head_commit'][$status] as $file) {
			if (in_array(pathinfo($file, PATHINFO_EXTENSION), $this->postFilesExt)) {
				$db[] = array($file,$timestamp);
				klog('HOOK - Post content found');
			}
			if (in_array(pathinfo($file, PATHINFO_EXTENSION), $this->pictFilesExt)) {
				$pict[] = $file;
				klog('HOOK - Picture found');
			}
		}
		return array(
			'post' => $db,
			'pict' => $pict,
			'total' => count($db),
			'date' => $timestamp
			);
	}
}