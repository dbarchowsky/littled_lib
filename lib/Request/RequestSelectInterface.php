<?php
namespace Littled\Request;


interface RequestSelectInterface
{
    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * Test if the supplied value matches any of the current internal selected category values. Returns TRUE if
     * $value matches a selected category value.
     * @param mixed $value
     * @return bool
     */
    public function lookupValueInSelectedValues(mixed $value): bool;
}