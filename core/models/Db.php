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
			self::$db->exec("CREATE TABLE IF NOT EXISTS Picture (
					id INTEGER PRIMARY KEY,
					post_id INTEGER, 
					pathname VARCHAR(255),
					thumbnail INTEGER DEFAULT 0,
					added_time DATETIME DEFAULT CURRENT_TIMESTAMP
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
	public static function build($table,Array $keys,Array $data) {
		try {
			klog('SQLLITE Insert data to DB - '.$table);

			foreach ($data as $value) {
				$toSql[] = '("'.implode('","', $value).'")';
			}
			$sql = "INSERT INTO ".$table." (".implode(',', $keys).") VALUES ".implode(",", $toSql).";";
			klog('SQLLITE Insert data to DB - '.$sql);
			self::$db->exec($sql);
			return self::$db->lastInsertId();
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
			klog('SQLLITE Read data from DB - '.$sql);
			$req = self::$db->prepare($sql);
			$req->execute();
			return $req->fetchAll(PDO::FETCH_OBJ);
			// return $req->fetchAll(PDO::FETCH_CLASS,'ArrayObject');
		} catch (Exception $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
		}
	}

	/**
	 * Update an element from the DB
	 * @param  String  $table Table name to update data
	 * @param  String  $condition 
	 * @param  Array   $data  data to update
	 */
	public static function put($table,$condition,Array $data) {
		try {
			$update = [];
			foreach ($data as $key => $value) {
				$update[] = trim($key).'="'.trim($value).'"';
			}
			$sql = sprintf('UPDATE %s SET %s WHERE %s',$table,implode(',', $update),$condition);
			klog('SQLLITE Update data from DB - '.$sql);
			self::$db->exec($sql);
		} catch (Exception $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
		}
	}

	/**
	 * Delete elements from the DB
	 * @param  String  $table Table name to update data
	 * @param  String  $condition 
	 */
	public static function delete($table,$condition) {
		try {
			$sql = 'DELETE * FROM '.$table.' WHERE '.$condition;
			klog('SQLLITE Update data from DB - '.$sql);
			self::$db->exec($sql);
		} catch (Exception $e) {
			klog("SQLLITE ".$e->getMessage(),'error');
		}
	}
}