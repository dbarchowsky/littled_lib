<?php
namespace LittledTests\DataProvider\Filters\FilterCollection;

class FormatQueryStringTestExpectations
{
    public array $not_contains;
    public array $values;

    public function __construct(array $values=[], array $not_contains=[])
    {
        $this->values = $values;
        $this->not_contains = $not_contains;
    }
}