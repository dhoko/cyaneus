<?php
/**
 * Build a site from a hook. It will load the valid component only if we valid the request
 */
class Hook {

	private $type = '';
	private $json = null;

	/**
	 * Init a custom Hook
	 * @param string $type Hook name (From an existing listener)
	 */
	public function __construct($type = 'Github') {
		$this->type = $type;
	}

	/**
	 * Load the JSON from the request
	 * @param  Object $json Payload
	 */
	public function init($json) {
		if(empty($json)) throw new Exception('Empty JSON for the hook');
		$this->json = $json;
		klog('Hook source payload: '.var_export($json,true),'server');
	}

	/**
	 * Determine if an IP is within a specific range.
	 * @param  String  $ip     Current request IP
	 * @param  Array   $ranges Array of ranges or IP
	 * @return boolean
	 */
	public function isValidIp($ip,Array $ranges) {

		foreach ($ranges as $range) {
			if(ip_in_range($ip, $range)) return true;
			continue;
		}
		return false;
	}

	/**
	 * Determine if the request is valid from a custom set of attributes
	 * @param  Array  $options Attributes to valid
	 */
	public function validate(Array $options) {
		foreach ($options as $key => $value) {
			if($this->json[$key][$value['key']] !== $value['value']) {
				throw new Exception($value['msg']);
			}
		}
	}

	/**
	 * Launch the Hook process
	 */
	public function run() {
		$className = $this->type.'Listener';

		klog('Init and security ok. Build '.$this->type.'Listener');
		$hookClass = new $className($this->json);
		return Cyaneus::make($hookClass->get());
	}

}