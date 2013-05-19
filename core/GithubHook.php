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
	 * Get new updates from github
	 * @return Array [status,msg] status = success||error
	 */
	public function get() {

		try {
			// $this->addedFiles();
			$this->modifiedFiles();
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
	private function grabFiles($status = 'added') {

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

	/**
	 * Get the content of each files from the hook
	 * @return Array [post,pict]
	 */
	private function getContentPostFiles($date) {
		$_base = 'https://raw.github.com/dhoko/blog/master/';
		$data = array('pict' => array(),'post' => array());
		$sql_post = 'SELECT pathname FROM Posts WHERE added_time >= "'.$date.'"';
		$sql_pict = 'SELECT 
					Pi.pathname 
					FROM Posts as P 
					INNER JOIN Picture as Pi on P.id=Pi.post_id
					WHERE P.added_time >= "'.$date.'"';

		$result_post = Db::read($sql_post);
		$result_pict = Db::read($sql_pict);

		foreach ($result_post as $post) {
			$data['post'][] = array(
				'path' => $post->pathname,
				'folder' => current(explode('/', $post->pathname)),
				'content' => file_get_contents($_base.$post->pathname)
				);
		}
		foreach ($result_pict as $pict) {
			$data['pict'][] = array(
				'path' => $pict->pathname,
				'folder' => current(explode('/', $pict->pathname)),
				'content' => file_get_contents($_base.$pict->pathname)
				);
		}
		return $data;
	}

	private function addedFiles() {
		$data = $this->grabFiles();
		klog('HOOK '.$data['total'].' files found from this webhook');
		if($data['total'] > 0) {
			$this->insert($data);
			$files = $this->getContentPostFiles($data['date']);
			$this->build($files['post']);
			$this->build($files['pict']);
		}
	}
	private function modifiedFiles() {
		$data = $this->grabFiles('modified');
		klog('HOOK '.$data['total'].' modified files found from this webhook');
		if($data['total'] > 0) {
			$this->update(array(
				'data' => array('last_update'=> $data['date']),
				'condition' => $this->updateCondition($data['post'])
				));
			$this->destroy($this->listFiles($data));
			$files = $this->getContentPostFiles($data['date']);
			$this->build($files['post']);
			$this->build($files['pict']);
		}
	}
	private function removedFiles() {
		$data = $this->grabFiles('removed');
		klog('HOOK '.$data['total'].' deleted files found from this webhook');
		if($data['total'] > 0) {
			$posts = array();
			foreach ($data['post'] as $post) {$posts[] = 'pathname="'.$post[0].'"';}
			$conditions = implode(' AND ', $posts);
			$this->delete($conditions);
			$this->destroy($this->listFiles($data));
		}
	}

	private function updateCondition($data) {
		$condition = array();
		foreach ($data as $e) {
			$condition[] = 'pathname="'.$e[0].'"';
		}
		return implode(' AND ', $condition);
	}

}