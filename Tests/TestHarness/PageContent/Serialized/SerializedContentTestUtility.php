<?php
namespace Littled\Tests\TestHarness\PageContent\Serialized;


class SerializedContentTestUtility
{
    public static function lookupColumnListValue(array $fields, string $key)
    {
        $fn_filter_value = function ($e) use ($key) {
            return ($e->key == $key);
        };
        return array_values(array_filter($fields, $fn_filter_value))[0]->value;
    }
}