<?php
namespace Cyaneus\Template\Models;
use Cyaneus\Template\Models\AbstractTemplateModel;

/**
 * Model for a Navigation
 */
class Navigation extends AbstractTemplateModel
{

    public function build()
    {
        return $this->bindParams($this->template['main'],$this->getTags());
    }

}
