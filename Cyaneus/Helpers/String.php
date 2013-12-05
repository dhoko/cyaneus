<?php
namespace Cyaneus\Helpers;
use Cyaneus\Cyaneus;
use Symfony\Component\Yaml\Parser;

/**
 * Some helpers for strings
 */
class String
{
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
        return \MarkdownExtended($data);
        // return SmartyPants(MarkdownExtended($data));
    }

    /**
     * Replace var in a template from an array [key=>value]
     * @param Array $opt Options of data to bind
     * @param String $string Template string
     * @return String Template with datas
     * @throws InvalidArgumentsExcepption If you pass an empty template
     */
    public static function replace(Array $opt, $string)
    {
        if(empty($string)) {
            throw new \InvalidArgumentException("Cannot fill an empty string");
        }

        $_data = array();
        foreach ($opt as $key => $value) {
            $_data['{{'.$key.'}}'] = $value;
        }
        return strtr($string,$_data);
    }

    /**
     * Parse a YAML string and build a config
     * @param  String $string  Post Configuration
     * @return Array
     */
    public static function parseConfig($string)
    {
        try {
            $info  = [];
            $_tags = explode(',', Cyaneus::config('site')->tags);

            $yaml  = new Parser();
            $value = $yaml->parse($string);

            foreach ($_tags as $tag) {

                if(!isset($value[$tag])) {
                    $value[$tag] = '';

                    if( 'picture' === $tag) {
                        $value[$tag] = [];
                    }
                }
                continue;
            }

            if(empty($value['url'])) {
                $value['url'] = self::url($value['title']);
            }

            unset($yaml);
            return $value;

        } catch (ParseException $e) {
            Log::error($e->getMessage());
            return [];
        }
    }

    public static function pict2Markdown($picture_path, $description = '')
    {
        return '!['.$description.']('.$picture_path.' "'.$description.'")';
    }
}
