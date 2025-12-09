<?php

namespace Littled\Request\Inline;

use Littled\Request\BooleanInput;


abstract class InlineStatusInput extends InlineInput
{
    public BooleanInput $enabled;
    protected static string $input_property = 'enabled';


    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->enabled = (new BooleanInput())
            ->setLabel('status')
            ->setKey('status')
            ->setAsRequired();
        $this->validate_properties[] = 'status';
    }
}