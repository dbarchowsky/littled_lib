<?php
namespace Littled\Tests\DataProvider\Request\StringSelect;

class CollectRequestDataSingleTestData extends CollectRequestDataTestData
{
    public ?string $expected;

    public function __construct(
        ?string $expected,
        string $key,
        array $post_data,
        ?array $custom_data=null )
    {
        parent::__construct($key, $post_data, $custom_data);
        $this->expected = $expected;
    }

    public function mapTestProvider(): array
    {
        return array(
            $this->expected,
            $this->key,
            $this->post_data,
            $this->custom_data
        );
    }
}