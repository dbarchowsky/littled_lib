<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class OneToOneLinkSerializedContentTestData
{
    public OneToOneLinkSerializedContentTestExpectations $expected;
    public ?int $record_id;

    public function __construct(string $name, ?int $status_id, string $status='', ?int $record_id=null)
    {
        $this->record_id = $record_id;
        $this->expected = new OneToOneLinkSerializedContentTestExpectations($name, $status_id, $status, $record_id);
    }
}