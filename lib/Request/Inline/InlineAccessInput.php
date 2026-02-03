<?php

namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Request\IntegerSelect;


abstract class InlineAccessInput extends InlineInput
{
    public IntegerSelect $access;
    protected static string $input_property = 'access';

    /**
     * @throws ConfigurationUndefinedException
     */
    public function __construct()
    {
        parent::__construct();
        $this->access = (new IntegerSelect())
            ->setLabel('Access')
            ->setKey('aid')
            ->setSizeLimit(20)
            ->setAsRequired();
    }
}