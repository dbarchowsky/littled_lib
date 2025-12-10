<?php

namespace Littled\Request\Inline;

use Littled\Request\IntegerInput;


abstract class InlinePageInput extends InlineInput
{
    public IntegerInput $page;
    protected static string $input_property = 'page';

    public function __construct()
    {
        parent::__construct();
        $this->page = (new IntegerInput())
            ->setLabel('Page')
            ->setKey('p')
            ->setAsRequired();
    }
}