<?php 
class Post extends Db {

	public static function all($from = '1970-01-01') {

		$posts = parent::read('SELECT
			Po.id,
			Po.name as name,
			Po.pathname as path,
			Po.added_time as added_time,
			Po.last_update as last_update,
			Pi.id as pict_id,
			Pi.pathname as pict_path,
			Pi.thumbnail as pict_thumbnail,
			Pi.added_time as pict_added_time
			FROM Posts as Po
			INNER JOIN Picture as Pi on Pi.post_id = Po.id
			WHERE Po.last_update >= "'.$from.'"');
		$list = array();

		foreach ($posts as $post) {
			$md5 = md5($post->id);
			if(!isset($list[$md5])) $list[$md5] = new stdClass();

			if(empty($list[$md5]->name)) 
				$list[$md5]->name = $post->name;

			if(empty($list[$md5]->id)) 
				$list[$md5]->id = $post->id;

			if(empty($list[$md5]->path)) 
				$list[$md5]->path = $post->path;

			if(empty($list[$md5]->added_time)) 
				$list[$md5]->added_time = $post->added_time;

			if(empty($list[$md5]->last_update)) 
				$list[$md5]->last_update = $post->last_update;
			$fileInfo = pathinfo($post->pict_path);
			$list[$md5]->picture[] = (object)array(
				'id' => $post->pict_id,
				'path' => $post->pict_path,
				'basename' => $fileInfo['basename'],
				'thumbnail' => (bool)$post->pict_thumbnail,
				'added_time' => $post->pict_added_time,
				);
		}
		return $list;
	}

	public static function findByDate($from = '') {
		if(empty($from)) $from = date('Y-m-d H:i:s');
		$sql = 'SELECT * FROM Posts WHERE added_time >= "'.$from.'"';
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

	public static function drop() {
		parent::$db->exec('DELETE From Posts');
		parent::$db->exec('DELETE From Picture');
	}
}