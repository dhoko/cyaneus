<?php
class Cyaneus {

	protected $postFilesExt = array('md','markdowm');
	protected $pictFilesExt = array('jpg','jpeg','gif','webp','png','bmp','ico');
	protected $db = array();

	// protected function update(Array $db) {
	// 	$postConfig = USERDATA.DIRECTORY_SEPARATOR.'posts.json';

	// 	if(file_exists($postConfig)) {
	// 		$data = file_get_contents($postConfig);
	// 		if ($data) {
	// 			$info = json_decode(base64_decode($data),true);
	// 			$info[key($db)] = $db[key($db)];
	// 			$update = base64_encode(json_encode($info));
	// 			file_put_contents($postConfig,$update);
	// 			$this->db = $info;
	// 			klog('Update DB successfully');
	// 			return true;
	// 		}else{
	// 			klog('Cannot update DB for '.serialize($db),"error");
	// 			return false;
	// 		}
	// 	}else{
	// 		file_put_contents($postConfig,base64_encode(json_encode($db)));
	// 		klog('Create DB successfully');
	// 	}
		
	// }

	/**
	 * Record data to Posts and Picture table
	 * @param  Array $db list of files [pict,post]
	 */
	protected function insert(Array $db) {
		$pictures = array();
		$post_id = Db::create('Posts',array('pathname','last_update'),$db['post']);
		
		foreach ($db['pict'] as $pict) {
			$pictures[] = array($post_id,$pict);
		}
		Db::create('Picture',array('post_id','pathname'),$pictures);
	}

	/**
	 * Update data to Posts table
	 * @param  Array $db list of files [condition,data]
	 */
	protected function update(Array $db) {
		Db::update('Posts',$db['condition'],$db['data']);
	}

	/**
	 * Delete data to Posts and Picture table
	 * @param  String  $condition 
	 */
	protected function delete($condition) {
		$ids =  array();
		$list = Db::read('SELECT id from Posts WHERE '.$condition);
		foreach ($list as $id) {$ids[] = $id->id;}
		Db::delete('Posts','id IN('.implode(',', $ids).')');
		Db::delete('Picture','post_id IN('.implode(',', $ids).')');
	}

	/**
	 * Create a static files in DRAFT from webHook files.
	 * @param  Array $files Array of files from WebHook
	 */
	protected function build(Array $data) {
		foreach ($data as $files) {
			if(!file_exists(DRAFT.DIRECTORY_SEPARATOR.$files['folder']))
					mkdir(DRAFT.DIRECTORY_SEPARATOR.$files['folder']);
				
			if(file_exists(DRAFT.DIRECTORY_SEPARATOR.$files['path'])) unlink(DRAFT.DIRECTORY_SEPARATOR.$files['path']);
			file_put_contents(DRAFT.DIRECTORY_SEPARATOR.$files['path'],$files['content'] );
			klog('Build file success for '.$files['path']);
		}
	}

	/**
	 * Delete a file if we delete it from a commit
	 * @param  Array $files Array of files from WebHook
	 */
	protected function destroy(Array $files) {
		foreach ($files as $e) {
			if(file_exists(DRAFT.DIRECTORY_SEPARATOR.$e['folder']))
				unlink(DRAFT.DIRECTORY_SEPARATOR.$e['folder']);
			
			if(file_exists(DRAFT.DIRECTORY_SEPARATOR.$e['path'])) unlink(DRAFT.DIRECTORY_SEPARATOR.$e['path']);
			
			klog('Delete file success for '.$files['path']);
		}
	}
}