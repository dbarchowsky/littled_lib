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
     * {@inheritDoc}
     */
    public function render(string $label='', string $css_label='', array $options=[])
    {
        throw new NotImplementedException("\"".__METHOD__."\" not implemented.");
    }
}