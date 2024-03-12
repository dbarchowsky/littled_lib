<?php

namespace Littled\Request;


/**
 * Input class designed to collect and commit foreign key input.
 */
class ForeignKeyInput extends IntegerSelect
{
    public bool $required = true;
    public bool $allow_multiple = false;
}