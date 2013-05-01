<?php 
/**
* 
*/
class Template {

	private $template = array();
	private $config = array();

	public function __construct(Array $config) {
		$this->config = $config;
		$this->template = array(
			'index' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'index.html'),
				'content' => file_get_contents(TEMPLATEPATH.'content-index.html'),
				),
			'post' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'post.html'),
				'content' => file_get_contents(TEMPLATEPATH.'content-post.html'),
				),
			'archives' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'index.html'),
				'content' => file_get_contents(TEMPLATEPATH.'content-index.html'),
				),
			'navigation' => file_get_contents(TEMPLATEPATH.'navigation.html')
			 );

	}

	private function replace($opt, $string) {
		$_data = array();
		foreach ($opt as $key => $value) {
			$_data['{{'.$key.'}}'] = $value;
		}
		return strtr($string,$_data);
	}

	private function fill($content,$data = array()) {
		if(empty($content)) throw new Exception("Cannot fill an empty string");
		return $this->replace($data,$content);

		if(!empty($data)){
			foreach ($data as $key => $value) {
				if(!is_array($value)) $render = $this->replace($key,$value,$render);
				klog('[TEMPLATE] : build {{'.$key.'}} to '.$value);
			}
		}
		return $render;
	}

	public function loop($context,Array $data) {
		$data    = $this->config($data);
		$content = $this->template[$context]['content'];
		if($content){
			try {
				return $this->fill($content,$data);
			} catch (Exception $e) {
				klog($e->getMessage(),"error");
			}
		}
	}

	public function navigation() {
		
	}

	public function page($context,Array $data){
		$content = $this->template[$context]['main'];
		$data    = $this->config($data);
		if($content){
			try {
				return $this->fill($content,$data);
			} catch (Exception $e) {
				klog($e->getMessage(),"error");
			}
		}
	}


	private function config(Array $data) {
		$merge = array_merge(array(
			'lang'     	  	=> $this->config['language'],
			'site_url' 	  	=> $this->config['url'],
			'site_title'  	=> $this->config['name'],
			'site_description' => $this->config['description'],
			'version'  	  	=> $this->config['generator'],
			'author'   	  	=> $this->config['author'],
			'template'    => $this->config['template_name'],
			'rss_url'     => $this->config['rss'],
			'css_url'     => $this->config['css'],
			),$data);
		return $merge;
	}
}