<?php

namespace Littled\Validation;


/**
 * Assorted static validation routines.
 */
class Validation extends EmailValidation
{
    /**
     * Tests if $sub is a subclass of $base. Both parameters can the qualified names of the classes as strings.
     * @param $sub
     * @param string $base
     * @return bool
     */
    public static function isSubclass($sub, string $base): bool
    {
        return (
            (is_object($sub) && get_class($sub) == $base) ||
            ($sub === $base) ||
            (is_subclass_of($sub, $base)));
    }
}
