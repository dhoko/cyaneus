<?php 
class Post extends Db {

	public static function findByDate($from = '') {
		if(empty($from)) $from = date('Y-m-d H:i:s');
		$sql = 'SELECT pathname FROM Posts WHERE added_time >= "'.$from.'"';
		return parent::read($sql);
	}
}