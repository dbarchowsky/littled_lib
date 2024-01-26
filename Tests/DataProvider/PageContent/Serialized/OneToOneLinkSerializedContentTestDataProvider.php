<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class OneToOneLinkSerializedContentTestDataProvider
{
    public static function readOneToOneLinkTestProvider(): array
    {
        return array(
            [new OneToOneLinkSerializedContentTestData('new test', 1, 'new', 1)],
            [new OneToOneLinkSerializedContentTestData('pending test', 2, 'pending', 4)],
            [new OneToOneLinkSerializedContentTestData('archived test', 4, 'archived', 9)],
            [new OneToOneLinkSerializedContentTestData('no status', null, '', 10)],
        );
    }
}