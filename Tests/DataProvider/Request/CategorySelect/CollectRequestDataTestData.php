<?php
namespace LittledTests\DataProvider\Request\CategorySelect;

class CollectRequestDataTestData
{
    public CollectRequestDataTestExpectations   $expected;
    public array                                $post_data;
    public bool                                 $allow_multiple;

    public function __construct(CollectRequestDataTestExpectations $expected, array $post_data, bool $allow_multiple=true)
    {
        $this->expected = $expected;
        $this->post_data = $post_data;
        $this->allow_multiple = $allow_multiple;
    }
}