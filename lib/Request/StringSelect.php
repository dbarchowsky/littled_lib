<?php
namespace Littled\Request;


use Littled\Exception\NotImplementedException;

/**
 * Class StringSelect
 * @package Littled\Request
 */
class StringSelect extends StringInput
{
    /**
     * @param string[optional] $label
     * @param null[optional] $css_label
     * @param array[optional] $options
     * @throws NotImplementedException
     */
    public function render($label=null, $css_label=null, $options=[])
    {
        throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
    }
}