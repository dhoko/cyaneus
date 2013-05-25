<?php 
class Post extends Db {

	public static function findByDate($from = '') {
		if(empty($from)) $from = date('Y-m-d H:i:s');
		$sql = 'SELECT pathname FROM Posts WHERE added_time >= "'.$from.'"';
		return parent::read($sql);
	}

	public static function create($post) {
		return parent::create('Posts',array('pathname','last_update'),$post);
	}

	public static function update($conditions,$data) {
		return parent::update('Posts',$conditions,$data);
	}

	/**
	 * Delete data to Posts and Picture table
	 * @param  String  $condition 
	 */
	public static function destroy($conditions) {
		$list = parent::read('SELECT id from Posts WHERE '.$conditions);
		$ids = array_map(function($e) {return $e->id;},$list);
		parent::delete('Posts','id IN('.implode(',', $ids).')');
		parent::delete('Picture','post_id IN('.implode(',', $ids).')');
	}

	public static function recordWithPicture($data) {
		$post_id = self::create($data['post']);
		Picture::create($data['pict'],$post_id);
	}
}