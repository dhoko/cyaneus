<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;
use Cyaneus\Helpers\Factory;

/**
 * Model for a Sitemap
 */
class Sitemap extends AbstractTemplateModel
{

    public function build()
    {
        $tags = $this->getTags([
                    'sitemap_url'       => '',
                    'sitemap_date'      => CYANEUS_DATETIME,
                    'sitemap_frequency' => '',
                    'sitemap_priority'  => '',
                ]);

        foreach ($this->pages as $key => $value) {
            dd($value);
        }
    }

    /**
     * Compute the priority for a page
     * @param  String $type type of page. page || anything
     * @param  String $url  Current page url
     * @return String
     */
    private function computePriority($type, $url)
    {
        if( strstr('index.html', $url) ) {
            return '1.0';
        }

        return ($type === 'page') ? '0.6' : '0.2';
    }

    /**
     * Compute the frequency of update for a page
     * It will be
     *     - daily
     *     - monthly
     * @param  String $type Type of content
     * @return String
     */
    private function computeFrequency($type)
    {
        return ($type === 'page') ? 'daily' : 'monthly';
    }

    /**
     * Compure the URl for your page
     * @param  String $url Current url
     * @return String
     */
    private function computeUrl($url) {

        return ( !strstr('index.html', $url) ) ? $url : Cyaneus::config('path')->url;
    }


    private function get()
    {
        $header = '<?xml version="1.0" encoding="UTF-8"?><!-- generator="'.Cyaneus::config('site')->generator.'" -->';
        $header .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $url = function ($data) {
            // var_dump(date('c',$data['timestamp_upRaw'])); exit();
            $path = $data['post_url'];
            $update = CDate::format($data['post_timestamp']);
            $freq = (isset($data['type']) && $data['type'] === 'page') ? 'daily' : 'monthly';
            $priority = (isset($data['type']) && $data['type'] === 'page') ? '0.6' : '0.2';
            if($data['post_url'] === 'index.html') {
                $priority = '1.0';
                $path = Cyaneus::config('path')->url;
            }
            $url = '<url>'."\n";
            $url .= "\t".'<loc>%s</loc>'."\n";
            $url .= "\t".'<lastmod>%s</lastmod>'."\n";
            $url .= "\t".'<changefreq>%s</changefreq>'."\n";
            $url .= "\t".'<priority>%.1f</priority>'."\n";
            $url .= '</url>';
            return sprintf($url,$path,$update,$freq,$priority);
        };
        foreach ($data as $element) {
            $header .= "\n".$url(array_merge($this->config, $this->buildKeyTemplate($element['config'], $element['html'])));
        }

        $header .= "\n".'</urlset>';
        return $header;
    }


}
