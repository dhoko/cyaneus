<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

/**
 * Model for a Rss
 */
class Rss extends AbstractTemplateModel
{

    public function build()
    {
        $tags = $this->getTags(['content' => $this->makePosts()]);
        return $this->bindParams($this->template['main'],$tags);
    }

    /**
     * Build the content for a post
     * @return String HTML template
     */
    private function makePosts()
    {
        $content = '';
        $_tags = [
            'rss_page_title'   => '',
            'rss_page_url'     => '',
            'rss_page_date'    => '',
            'rss_page_author'  => '',
            'rss_page_content' => '',
        ];

        foreach ($this->posts as $post) {

            $_tags['rss_page_title']   = $post['config']['title'];
            $_tags['rss_page_url']     = Cyaneus::postUrl($post['config']['url']);
            $_tags['rss_page_author']  = $post['config']['author'];
            $_tags['rss_page_date']    = CDate::rss($post['config']['added_time']);
            $_tags['rss_page_content'] = $post['text'];

            $content .= $this->bindParams($this->template['content'],$this->getTags($_tags));
        }

        return $content;
    }

}
