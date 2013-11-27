<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

/**
 * Model for a Sitemap
 */
class Sitemap extends AbstractTemplateModel
{

    const PAGE_FREQUENCY = 'daily';
    const POST_FREQUENCY = 'monthly';

    public function build()
    {
        $tags = $this->getTags(['content' => $this->makePages() . $this->makePosts()]);
        return $this->bindParams($this->template['main'],$tags));
    }

    /**
     * Build the content for a post
     * @return String HTML template
     */
    private function makePosts()
    {
        $content = '';
        $_tags = [
            'sitemap_url'       => '',
            'sitemap_date'      => '',
            'sitemap_frequency' => '',
            'sitemap_priority'  => '',
        ];

        foreach ($this->posts as $post) {

            $_tags['sitemap_url']       = $this->computeUrl(Cyaneus::postUrl($post['config']['url']));
            $_tags['sitemap_date']      = CDate::atom($post['config']['added_time']);
            $_tags['sitemap_frequency'] = self::POST_FREQUENCY;
            $_tags['sitemap_priority']  = $this->computePriority('post', $post['config']['url']);

            $content .= $this->bindParams($this->template['content'],$this->getTags($_tags));
        }

        return $content;
    }

    /**
     * Build the content for a post
     * @param  String String to bind as {{content}}
     * @return String HTML template
     */
    private function makePages()
    {
        $_content = '';
        $_tags = [
            'sitemap_url'       => '',
            'sitemap_date'      => CYANEUS_DATETIME,
            'sitemap_frequency' => self::PAGE_FREQUENCY,
            'sitemap_priority'  => '',
        ];

        foreach ($this->pages as $page) {

            $_tags['sitemap_url']      = $this->computeUrl(Cyaneus::pageUrl($page));
            $_tags['sitemap_priority'] = $this->computePriority('page', $page.'.html');

            $_content .= $this->bindParams($this->template['content'],$this->getTags($_tags));
        }

        return $_content;
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
     * Compure the URl for your page
     * @param  String $url Current url
     * @return String
     */
    private function computeUrl($url)
    {
        return ( !strstr($url,'index.html') ) ? $url : Cyaneus::config('path')->url;
    }
}
