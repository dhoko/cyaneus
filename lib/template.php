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
				'main' 	  => file_get_contents(TEMPLATEPATH.'index.html')
				'content' => file_get_contents(TEMPLATEPATH.'content-index.html'),
				),
			'post' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'post.html')
				'content' => file_get_contents(TEMPLATEPATH.'content-post.html'),
				),
			'archives' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'archives.html')
				'content' => file_get_contents(TEMPLATEPATH.'content-archives.html'),
				),
			'navigation' => file_get_contents(TEMPLATEPATH.'navigation.html')
			 );

	}

	private function replace($key,$value, $data) {
		return preg_replace('{{'.$key.'}}', $value, $data);
	}

	private function fill($content,$data = null) {
		if(empty($content)) throw new Exception("Cannot fill an empty string");
		$render = $content;

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
		if($content){
			try {
				return $this->fill($content,$data);
			} catch (Exception $e) {
				klog($e->getMessage(),"error");
			}
		}
	}


	private function config(Array $config) {
		$merge = array_merge(array(
			'title'    	  => $this->config['title'],
			'lang'     	  => $this->config['language'],
			'base_url' 	  => $this->config['url'],
			'version'  	  => $this->config['version'],
			'author'   	  => $this->config['author'],
			'description' => $this->config['description'],
			'template'    => $this->config['template'],
			'rss_url'     => $this->config['rss'],
			'css_url'     => $this->config['css'],
			),$data);
		return $merge;
	}

}