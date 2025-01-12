<?php

namespace Littled\Request;


use Littled\App\LittledGlobals;

/**
 * Input class designed to collect and commit foreign key input.
 */
class ForeignKeyInput extends IntegerSelect
{
    public bool $required = true;
    public bool $allow_multiple = false;

    /**
     * @inheritDoc
     * Overrides default value for key property assignment.
     */
    public function __construct(
        string      $label          = '',
        string      $key            = LittledGlobals::ID_KEY,
        bool        $required       = false,
        mixed       $value          = null,
        int         $size_limit     = 0,
        ?int        $index          = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return parent::hasData() && $this->value > 0;
    }
}