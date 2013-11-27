<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

/**
 * Model for a Post
 */
class Post extends AbstractTemplateModel
{

    public function build()
    {
        return $this->makePosts();
    }

    /**
     * Build the content for a post
     * @return String HTML template
     */
    private function makePosts()
    {
        $content = [];
        $_tags   = [];

        foreach ($this->posts as $post) {

            $_tags = $this->buildCustomtags($post['config'],$post['text']);
            $_tags['navigation'] = $this->navigation;
            $content[$post['config']['url']] = $this->bindParams($this->template['main'],$this->getTags($_tags));
        }

        return $content;
    }
}
