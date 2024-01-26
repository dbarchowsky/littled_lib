<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class OneToOneLinkSerializedContentTestExpectations
{
    public ?int         $record_id;
    public string       $name;
    public ?int         $status_id;
    public string       $status;

    public function __construct(string $name, ?int $status_id, string $status='', ?int $record_id=null)
    {
        $this->record_id = $record_id;
        $this->name = $name;
        $this->status_id = $status_id;
        $this->status = $status;
    }
}