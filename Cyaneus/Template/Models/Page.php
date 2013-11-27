<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

/**
 * Model for a Page
 */
class Page extends AbstractTemplateModel
{

    public function build()
    {
        return $this->makePages();
    }

    /**
     * Build the content for a page
     * @return String HTML template
     */
    private function makePages()
    {
        $content = '';
        $_tags   = [];
        $_tags   = $this->getTags(['content' => $this->makePosts()]);
        $_tags['navigation'] = $this->navigation;

        return $this->bindParams($this->template['main'],$_tags);
    }

    /**
     * Build the content for a post
     * @return Array list of tags per post
     */
    private function makePosts()
    {
        $content = '';
        $_tags   = [];

        foreach ($this->posts as $post) {

            $_tags[$post['config']['added_time']] = $this->getTags($this->buildCustomtags($post['config'],$post['text']));
        }

        // To be sure that we order from the latest
        ksort($_tags);

        foreach ($_tags as $tags) {
            $content .= $this->bindParams($this->template['content'],$tags);
        }
        return $content;
    }

}
