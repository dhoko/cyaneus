<?php
/**
 * Some helpers for strings
 */
class String {

	/**
	 * Build a valid url from a title
	 * New Firefox OS app : XBMC remote -> new-firefox-os-app-xbmc-remote
	 * @param string
	 * @return string
	 */
	public static function url($path)
	{
	    $url = str_replace('&', '-and-', $path);
	    $url = trim(preg_replace('/[^\w\d_ -]/si', '', $url));//remove all illegal chars
	    $url = str_replace(' ', '-', $url);
	    $url = str_replace('--', '-', $url);
	    return strtolower($url);
	}

	/**
     * Convert raw content to HTML
     * @param  string $data   Your draft
     * @param  string $format convertion format
     * @return string         html
     * @todo add convertion format
     */
    public static function convert($data,$format = 'markdown')
    {
        return MarkdownExtended($data);
        // return SmartyPants(MarkdownExtended($data));
    }

    /**
     * Loop on each TAGS in order to build an array [tag:value]
     * @param string Header from a post
     * @return Array [tag:value]
     */
    public static function getTags($post)
    {
        $info = [];
        $_tags = explode(',', Cyaneus::config('site')->tags);

        foreach ($_tags as $tag) {
            $info[$tag] = self::findTag($post,$tag);
        }

        // Rebuild some informations
        if(empty($info['url'])) {
            $info['url'] = self::url($info['title']);
        }

        return $info;
    }

    /**
     * Find tags from a post from its header.
     * info('author="dhoko"','author') => dhoko
     * @param string Header of a post
     * @param string Tag tag to find cf TAGS
     * @return string tag value
     */
    private static function findTag($data,$tag)
    {
        preg_match('/"([^"]+)"/',strstr($data,$tag),$match);
        return (isset($match[1])) ? $match[1] : '';
    }



}
