<?php 
/**
* Main class to build generate pages from templates
*/
class Template {

	private $template = array();
	private $config = array();

	/**
	 * Build our basic configuration for a template, such as default config var and template string 
	 * @param Array $config Cyaneus COnfig
	 */
	public function __construct() {

		$this->template = array(
			'index' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'index.html'),
				'content' => file_get_contents(TEMPLATEPATH.'content-index.html'),
				),
			'post' => file_get_contents(TEMPLATEPATH.'post.html'),
			'archives' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'index.html'),
				'content' => file_get_contents(TEMPLATEPATH.'content-index.html'),
				),
			'rss' => array(
				'main' 	  => file_get_contents(TEMPLATEPATH.'rss.html'),
				'content' => file_get_contents(TEMPLATEPATH.'content-rss.html'),
				),
			'navigation' => file_get_contents(TEMPLATEPATH.'navigation.html')
			 );

	}

	/**
	 * Replace var in a template from an array [key=>value]
	 * @param Array $opt Options of data to bind
	 * @param String $string Template string
	 * @return String Template with datas
	 */
	private function replace(Array $opt, $string) {
		if(empty($string)) throw new Exception("Cannot fill an empty string");
		$_data = array();
		foreach ($opt as $key => $value) {
			$_data['{{'.$key.'}}'] = $value;
		}
		return strtr($string,$_data);
	}

	/**
	 * Build loop element such as content on a home page
	 * @param String $context Template to build
	 * @param Array  $data Options of data to bind
	 * @return String Template with datas
	 */
	public function loop($context,Array $data) {
		$data    = $this->config($data);
		$content = $this->template[$context]['content'];
		if($content){
			try {
				return $this->replace($data,$content);
			} catch (Exception $e) {
				klog($e->getMessage(),"error");
			}
		}
	}

	public function navigation() {
		return $this->replace($this->config(array()), $this->template['navigation']);
	}

	/**
	 * Build a page
	 * @param String $context Template to build
	 * @param Array  $data Options of data to bind
	 * @return String Template with datas
	 */
	public function page($context,Array $data){
		$_content = '';
		$content = $this->template[$context]['main'];
		foreach ($data['content'] as $post_find) {
			$_content .= $this->loop($context,$post_find);
		}
		$data['content'] = $_content;
		$data['navigation'] = $this->navigation();
		$data = $this->config($data);
		if($content){
			try {
				return $this->replace($data,$content);
			} catch (Exception $e) {
				klog($e->getMessage(),"error");
			}
		}
	}

	/**
	 * Build a post
	 * @param String $context Template to build
	 * @param Array  $data Options of data to bind
	 * @return String Template with datas
	 */
	public function post(Array $data){
		$_content = '';
		$content = $this->template['post'];
		$data['navigation'] = $this->navigation();
		$data = $this->config($data);
		if($content){
			try {
				return $this->replace($data,$content);
			} catch (Exception $e) {
				klog($e->getMessage(),"error");
			}
		}
	}

	/**
	 * Build configuration from tge default one
	 * @param Array  $data Options of data to bind
	 * @return Array Configuration var to bind
	 */
	private function config(Array $data) {
		$merge = array_merge(array(
			'lang'     	  	=> LANGUAGE,
			'site_url' 	  	=> URL,
			'site_title'  	=> NAME,
			'site_description' => DESCRIPTION,
			'generator'  	  	=> GENERATOR,
			'author'   	  	=> AUTHOR,
			'template'    => TEMPLATE_NAME,
			'rss_url'     => RSS,
			'css_url'     => CSS,
			),$data);
		return $merge;
	}
}