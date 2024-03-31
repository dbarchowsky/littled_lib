<?php

namespace Littled\Request;


/**
 * Input class designed to collect and commit foreign key input.
 */
class ForeignKeyInput extends IntegerSelect
{
    public bool $required = true;
    public bool $allow_multiple = false;

    /**
     * @inheritDoc
     */
    public function hasData(): bool
    {
        return parent::hasData() && $this->value > 0;
    }
}