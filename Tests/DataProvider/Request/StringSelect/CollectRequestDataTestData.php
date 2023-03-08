<?php
namespace Littled\Tests\DataProvider\Request\StringSelect;

class CollectRequestDataTestData
{
    public string   $key;
    public array    $post_data;
    public ?array   $custom_data;

    public function __construct(
        string $key,
        array $post_data,
        ?array $custom_data=null )
    {
        $this->key = $key;
        $this->post_data = $post_data;
        $this->custom_data = $custom_data;
    }
}