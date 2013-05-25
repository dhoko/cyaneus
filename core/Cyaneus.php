<?php
class Cyaneus {

	protected $postFilesExt = array('md','markdowm');
	protected $pictFilesExt = array('jpg','jpeg','gif','webp','png','bmp','ico');
	protected $db = array();

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
}