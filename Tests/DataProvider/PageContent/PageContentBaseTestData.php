<?php
namespace Littled\Tests\DataProvider\PageContent;


class PageContentBaseTestData
{
    public array    $excluded_keys;
    public array    $expected;
    public bool     $force_update;
    public array    $keys_not_contained;
    public array    $post_data;

    public function __construct(
        array $expected=[],
        array $post_data=[],
        array $excluded_keys=[],
        bool  $force_update=false,
        array $keys_not_contained=[]
    )
    {
        $this->expected = $expected;
        $this->post_data = $post_data;
        $this->excluded_keys = $excluded_keys;
        $this->force_update = $force_update;
        $this->keys_not_contained = $keys_not_contained;
    }
}