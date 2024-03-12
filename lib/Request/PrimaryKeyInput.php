<?php

namespace Littled\Request;


use Littled\App\LittledGlobals;

/**
 * Input class designed to collect and commit primary key input.
 */
class PrimaryKeyInput extends IntegerInput
{
    public bool $required = true;
    public bool $allow_multiple = false;

    /**
     * @inheritDoc
     */
    public function __construct(
        string $label,
        string $key=LittledGlobals::ID_KEY,
        bool $required = false,
        $value = null,
        int $size_limit = 0,
        ?int $index = null)
    {
        parent::__construct($label, $key, $required, $value, $size_limit, $index);
    }
}