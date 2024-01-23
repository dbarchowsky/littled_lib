<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class LinkedContentTestData
{
    public ?int     $primary_id;
    /** @var null|int|array */
    public          $foreign_id;
    public string   $label;
    public bool     $required;

    public function __construct(?int $primary_id, $foreign_id, string $label, bool $required=true)
    {
        $this->primary_id = $primary_id;
        $this->foreign_id = $foreign_id;
        $this->label = $label;
        $this->required = $required;
    }

}