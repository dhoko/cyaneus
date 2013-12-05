<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;
use Cyaneus\Cyaneus;
use Cyaneus\Helpers\CDate;

/**
 * Model for a SinglePage
 */
class SinglePage extends AbstractTemplateModel
{

    public function build()
    {
        $_tags               = $this->buildCustomtags($this->singlePage['config'],$this->singlePage['text']);
        $_tags['navigation'] = $this->navigation;
        $_tags               = $this->getTags($_tags);

        return $this->bindParams($this->template['main'],$_tags);
    }

}
