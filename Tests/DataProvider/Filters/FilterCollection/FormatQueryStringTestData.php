<?php
namespace Littled\Tests\DataProvider\Filters\FilterCollection;


class FormatQueryStringTestData
{
    public array                                $excluded_keys;
    public FormatQueryStringTestExpectations    $expected;
    public array                                $test_values;

    public function __construct(
        FormatQueryStringTestExpectations   $expected,
        array                               $test_values=[],
        array                               $excluded_keys=[])
    {
        $this->excluded_keys    = $excluded_keys;
        $this->expected         = $expected;
        $this->test_values      = $test_values;
    }
}