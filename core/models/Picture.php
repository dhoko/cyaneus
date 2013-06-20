<?php 
class Picture extends Db {

	public static function findByDate($from = '') {
		if(empty($from)) $from = date('Y-m-d H:i:s');
		$sql = 'SELECT 
				Pi.* 
				FROM Posts as P 
				INNER JOIN Picture as Pi on P.id=Pi.post_id
				WHERE P.added_time >= "'.$from.'"';
		return parent::read($sql);
	}

	public static function create($pictures,$post_id) {
		$_pictures = [];
		foreach ($pictures as $pict) {
			$_pictures[] = [$post_id,$pict];
		}
		return parent::create('Picture',['post_id','pathname'],$_pictures);
	}
}