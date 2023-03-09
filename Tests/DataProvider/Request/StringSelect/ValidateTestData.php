<?php

namespace Littled\Tests\DataProvider\Request\StringSelect;

class ValidateTestData
{
    public bool                     $allow_multiple;
    public ValidateTestExpectations $expected;
    public bool                     $required;
    public string                   $key;
    public array                    $post_data;

    public function __construct(
        ValidateTestExpectations $expected,
        bool $allow_multiple,
        bool $required,
        string $key,
        array $post_data)
    {
        $this->allow_multiple = $allow_multiple;
        $this->expected = $expected;
        $this->required = $required;
        $this->key = $key;
        $this->post_data = $post_data;
    }
}