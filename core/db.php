<?php
class Db {

	/**
	 * Exemple :
	 * Db::init();
	 * Db::create('Posts',array('PathName','Name','last_update'),	 * array('cyaneusdemo332/bonjour.md','Post de test','2013-05-06 19:02:02'));
	 *
	 * $req = Db::read()->prepare('SELECT * FROM Posts');
	 * $req->execute();
	 * $result = $req->fetchAll(PDO::FETCH_CLASS,'ArrayObject');
	 * // $result = $req->fetchAll(PDO::FETCH_ASSOC);
	 * foreach($result as $row) {
	 *   echo "<pre>";
	 *   print_r($row);
	 *   echo "</pre>";
	 * }
	 */
	public static $db = null;
	public static function init() {
		try {
		//open the database
		self::$db = new PDO('sqlite:'.USERDATA.DIRECTORY_SEPARATOR.'cyaneus.sqlite');
		self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//create the database
		self::$db->exec("CREATE TABLE IF NOT EXISTS Posts (
				id INTEGER PRIMARY KEY,
				pathname VARCHAR(255), 
				name VARCHAR(255), 
				added_time DATETIME DEFAULT CURRENT_TIMESTAMP, 
				last_update DATETIME
			);");    
		self::$db->exec("CREATE TABLE IF NOT EXISTS Posts_meta (
				id INTEGER PRIMARY KEY,
				post_id INTEGER, 
				config TEXT
			);");    

		}
		catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	public static function create($table,$keys,$data) {
		try {
			self::$db->exec("INSERT INTO ".$table." (".implode(',', $keys).") VALUES ('".implode("','", $data)."');");
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function read() {
		return self::$db;
	}
}