<?php
namespace LittledTests\DataProvider\Validation;

class CollectStringArrayRequestVarTestData
{
    public array    $expected;
    public string   $key;
    public array    $post_data;
    public ?array   $custom_data;

    public function __construct(array $expected, string $key, array $post_data, ?array $custom_data=null)
    {
        $this->expected = $expected;
        $this->key = $key;
        $this->post_data = $post_data;
        $this->custom_data = $custom_data;
    }
}