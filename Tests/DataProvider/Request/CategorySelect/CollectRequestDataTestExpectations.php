<?php
namespace Littled\Tests\DataProvider\Request\CategorySelect;

class CollectRequestDataTestExpectations
{
    /** @var string|array|null */
    public $category_input;
    public string $new_category;
    public array $terms;

    public function __construct(array $terms=[], $category_input=null, string $new_category='')
    {
        $this->terms = $terms;
        $this->category_input = $category_input;
        $this->new_category = $new_category;
    }
}