<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


use LittledTests\TestHarness\PageContent\Serialized\TestTableTestHarness;

class SerializedContentUpdateIdTestData
{
    public TestTableTestHarness     $o;
    public bool                     $expect_insert;
    public bool                     $use_lowercase;

    public function __construct(
        bool $expect_insert,
        bool $use_lowercase=false,
        ?int $id=null,
        string $name='',
        ?int $int_val=null,
        ?int $slot=null)
    {
        $this->expect_insert = $expect_insert;
        $this->use_lowercase = $use_lowercase;
        $this->o = new TestTableTestHarness();
        $this->o->id->setInputValue($id);
        $this->o->name->setInputValue($name);
        $this->o->int_col->setInputValue($int_val);
        $this->o->slot->setInputValue($slot);
    }
}