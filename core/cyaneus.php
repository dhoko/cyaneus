<?php
class Cyaneus {

	protected $postFilesExt = array('md','markdowm');
	protected $pictFilesExt = array('jpg','jpeg','gif','webp','png','bmp','ico');
	protected $db = array();

	protected function update(Array $db) {
		$postConfig = USERDATA.DIRECTORY_SEPARATOR.'posts.json';

		if(file_exists($postConfig)) {
			$data = file_get_contents($postConfig);
			if ($data) {
				$info = json_decode(base64_decode($data),true);
				$info[key($db)] = $db[key($db)];
				$update = base64_encode(json_encode($info));
				file_put_contents($postConfig,$update);
				$this->db = $info;
				klog('Update DB successfully');
				return true;
			}else{
				klog('Cannot update DB for '.serialize($db),"error");
				return false;
			}
		}else{
			file_put_contents($postConfig,base64_encode(json_encode($db)));
			klog('Create DB successfully');
		}
		
	}
}