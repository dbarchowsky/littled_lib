<?php
namespace LittledTests\DataProvider\Validation;

class CollectBooleanRequestVarTestData
{
    public ?bool $expected;
    public array $get_data;
    public string $key;
    public array $post_data;

    public function __construct(
        ?bool $expected=null,
        string $key='',
        array $get_data=[],
        array $post_data=[])
    {
        $this->expected = $expected;
        $this->key = $key;
        $this->get_data = $get_data;
        $this->post_data = $post_data;
    }
}