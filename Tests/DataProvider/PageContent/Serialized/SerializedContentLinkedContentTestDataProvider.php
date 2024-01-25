<?php

namespace LittledTests\DataProvider\PageContent\Serialized;

use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;

class SerializedContentLinkedContentTestDataProvider
{
    public static function collectRequestDataTestProvider(): array
    {
        return array(
            [new SerializedContentLinkedContentTestData( '',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'], 999, 996, 993],
                'foo',
                'bar'
            )],
            [new SerializedContentLinkedContentTestData( '',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                8204,
                'biz',
                'bash'
            )],
        );
    }

    public static function validateInputFailTestDataProvider(): array
    {
        return array(
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                null,
                'foo',
                'bar',
                true
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'],
                'foo',
                '',
                true
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [34, 46, 58],
                '',
                'bar',
                true
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                ['str1', 'str2'],
                'foo',
                'bar',
                true
            )],
        );
    }

    public static function validateInputPassTestDataProvider(): array
    {
        return array(
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'],
                'foo',
                'bar',
                true
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'],
                'foo',
                'bar',
                false
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [34, 46, 58],
                'foo',
                'bar',
                true
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [34, 46, 58],
                'foo',
                'bar',
                false
            )],
            [new SerializedContentLinkedContentTestData('',
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                null,
                'foo',
                'bar',
                false
            )],
        );
    }
}