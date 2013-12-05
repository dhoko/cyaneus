<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;

/**
 * Model for a Picture
 */
class Picture extends AbstractTemplateModel
{

    public function build()
    {
        $_tags   = $this->customTags();
        return $this->bindParams($this->template['main'],$_tags);
    }

    /**
     * Build the params to bind in the view for a picture
     * List of params :
     *     - picture_class
     *     - picture_src
     *     - picture_alt
     *     - picture_description
     *
     * @return Array
     */
    private function customTags()
    {
        $alt         = (isset($this->tags['alt'])) ? : '';
        $description = (isset($this->tags['description'])) ? : $alt;
        $className   = Cyaneus::config('site')->picture_class;

        if(isset($this->tags['class'])) {
            $className .= $this->tags['class'];
        }

        return [
            'picture_class'       => $className,
            'picture_src'         => Cyaneus::config('path')->postUrl.$this->tags['name'].'.'.$this->tags['ext'],
            'picture_alt'         => $alt,
            'picture_description' => $description,
        ];
    }

}
