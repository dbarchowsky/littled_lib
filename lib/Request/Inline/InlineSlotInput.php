<?php

namespace Littled\Request\Inline;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Request\IntegerInput;


abstract class InlineSlotInput extends InlineInput
{
    public IntegerInput $slot;
    protected static string $input_property = 'slot';

    /**
     * @throws ConfigurationUndefinedException
     */
    public function __construct()
    {
        parent::__construct();
        $this->slot = (new IntegerInput())
            ->setLabel('Slot')
            ->setKey('slt')
            ->setAsRequired();
    }
}