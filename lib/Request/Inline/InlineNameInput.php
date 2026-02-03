<?php

namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Request\StringInput;


abstract class InlineNameInput extends InlineInput
{
    public StringInput $name;
    protected static string $input_property = 'name';

    /**
     * @throws ConfigurationUndefinedException
     */
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