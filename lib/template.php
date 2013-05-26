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

	public function sitemap(Array $data) {
		$header = '<?xml version="1.0" encoding="UTF-8"?><!-- generator="'.GENERATOR.'" -->';
		$header .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		$url = function($data) {
			// var_dump(date('c',$data['timestamp_upRaw'])); exit();
			$path = URL.$data['post_url'];
			$update = date('c',$data['timestamp_upRaw']);
			$freq = (isset($data['type']) && $data['type'] === 'page') ? 'daily' : 'monthly';
			$priority = (isset($data['type']) && $data['type'] === 'page') ? '0.6' : '0.2';
			if($data['post_url'] === 'index.html') {
				$priority = '1.0';
				$path = URL;
			}
			$url = '<url>'."\n";
			$url .=	"\t".'<loc>%s</loc>'."\n";
			$url .=	"\t".'<lastmod>%s</lastmod>'."\n";
			$url .=	"\t".'<changefreq>%s</changefreq>'."\n";
			$url .=	"\t".'<priority>%.1f</priority>'."\n";
			$url .= '</url>';
			return sprintf($url,$path,$update,$freq,$priority);
		};
		foreach ($data as $element) {
			$header .= "\n".$url($element);
		}

		$header .= "\n".'</urlset>';
		return $header;
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