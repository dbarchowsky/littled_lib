<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class SerializedContentLinkedContentTestData
{
    public string $expected;
    public int $primary_id;
    /** @var null|int|int[] */
    public $foreign_id;
    public string $label;
    public string $name;
    public bool $required;

    public function __construct(string $expected, int $primary_id, $foreign_id, string $name, string $label, bool $required=true)
    {
        $this->expected = $expected;
        $this->primary_id = $primary_id;
        $this->foreign_id = $foreign_id;
        $this->label = $label;
        $this->name = $name;
        $this->required = $required;
    }
}