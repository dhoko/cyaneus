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

	/**
	 * Create our connection and create tables if they don't exist
	 * @return Object PDO connection
	 */
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
			return true;
		}
		catch(PDOException $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
			return false;
		}
	}

	/**
	 * Create an insert or many inserts in the dB
	 * @param  String $table Name of the table to record data
	 * @param  Array  $keys  keys to update
	 * @param  Array  $data  data to put
	 */
	public static function create($table,Array $keys,Array $data) {
		try {
			self::$db->exec("INSERT INTO ".$table." (".implode(',', $keys).") VALUES ('".implode("','", $data)."');");
		} catch (Exception $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
		}
	}

	/**
	 * Read data store in db
	 * @param  string $sql SQL request to execute
	 * @return Array      Array of ArrayObject
	 */
	public static function read($sql = '') {
		try {
			$req = self::$db->prepare($sql);
			$req->execute();
			return $req->fetchAll(PDO::FETCH_CLASS,'ArrayObject');
		} catch (Exception $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
		}
	}

	/**
	 * Update an element from the DB
	 * @param  String  $table Table name to update data
	 * @param  Integer $id    id of the row to update
	 * @param  Array   $data  data to update
	 */
	public static function update($table,$id,Array $data) {
		try {
			$update = array();
			foreach ($data as $key => $value) {
				$update[] = trim($key).'="'.trim($value).'"';
			}
			$sql = sprintf('UPDATE %s SET %s WHERE id=%d',$table,implode(',', $update),$id);
			self::$db->exec("UPDATE ".$table." SET (".implode(',', $keys).") VALUES ('".implode("','", $data)."');");
		} catch (Exception $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
		}
	}
}