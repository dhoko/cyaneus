<?php
class Hook {

	private $type = '';
	private $json = null;

	public function __construct($type = 'Github') {
		$this->type = $type;
	}

	public function init($json) {
		if(empty($json)) throw new Exception('Empty JSON for the hook');
		$this->json = $json;
	}

	public function isValidIp($server,$ip) {
		return in_array($_SERVER['REMOTE_ADDR'], $ip);
	}

	public function validate(Array $options) {
		foreach ($options as $key => $value) {
			if($this->json[$key][$value['key']] !== $value['value']) {
				throw new Exception($value['msg']);
			}
		}
	}

	public function run() {
		$className = $this->type.'Listener';
		$hookClass = new $className($this->json);
		return Cyaneus::make($hookClass->get());
	}

}