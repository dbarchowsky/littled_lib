<?php

namespace Littled\Tests\DataProvider\Validation;

class CheckSourceValueTestData
{
    public ?array                           $custom_data;
    public CheckSourceValueTestExpectations $expected;
    public string                           $key;
    public array                            $post_data;

    public function __construct(
        CheckSourceValueTestExpectations $expected,
        string $key,
        array $post_data=[],
        ?array $custom_data=null )
    {
        $this->expected = $expected;
        $this->key = $key;
        $this->post_data = $post_data;
        $this->custom_data = $custom_data;
    }
}