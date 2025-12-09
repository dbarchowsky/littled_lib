<?php

namespace Littled\Request\Inline;

use Littled\Request\StringInput;


abstract class InlineNameInput extends InlineStringInput
{
    public StringInput $name;
    protected static string $input_property = 'name';

    function __construct()
    {
        parent::__construct();
        $this->name = (new StringInput())
            ->setLabel('Name')
            ->setKey('n')
            ->setSizeLimit(100)
            ->setAsRequired();
    }
}