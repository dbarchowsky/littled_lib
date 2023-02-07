<?php

namespace Littled\Tests\DataProvider\PageContent\Serialized;

use Littled\Tests\TestHarness\PageContent\Serialized\KeywordTestHarness;

class ReadListTestDataProvider
{
    public string       $class_name;
    public string       $property_name;
    public int          $record_id;
    public array        $records;

    function __construct($property_name='', string $class_name='', ?int $record_id=null, array $records=[])
    {
        $this->property_name = $property_name;
        $this->class_name = $class_name;
        $this->record_id = $record_id;
        $this->records = $records;
    }

    public static function readListProvider(): array
    {
        return [
            [new ReadListTestDataProvider(
                'keyword_list',
                '\Littled\Tests\TestHarness\PageContent\Serialized\KeywordTestHarness',
                641,
                [
                    new KeywordTestHarness(80435, 'sketchbooks', 641, 11),
                    new KeywordTestHarness(80436, '2014', 641, 11),
                    new KeywordTestHarness(80437, 'watercolor', 641, 11)
                ]
            )],
            [new ReadListTestDataProvider(
                'keyword_list',
                '\Littled\Tests\TestHarness\PageContent\Serialized\KeywordTestHarness',
                17,
                [
                    new KeywordTestHarness(80069, 'sketchbooks', 17, 11),
                    new KeywordTestHarness(80070, 'writing', 17, 11)
                ]
            )],
            [new ReadListTestDataProvider(
                'keyword_list',
                '\Littled\Tests\TestHarness\PageContent\Serialized\KeywordTestHarness',
                134,
                []
            )]
        ];
    }
}