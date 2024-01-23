<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;

class LinkedContentTestDataProvider
{
    public static function collectRequestTestDataProvider(): array
    {
        return array(
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'],
                'foo')],
        );
    }

    public static function validateInputFailDataTestProvider(): array
    {
        return array(
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                null,
                'foo',
                true)],
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [],
                'foo',
                true)],
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                '',
                'foo',
                true)],
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                'garbage_string',
                'foo',
                true)],
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                ['array', 'of','strings'],
                'foo',
                true)],
            [new LinkedContentTestData(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [3, 98, 45],
                '',
                true)],
            [new LinkedContentTestData(null,
                [3, 98, 45],
                '',
                true)],
        );
    }

    public static function validateInputPassDataTestProvider(): array
    {
        return array(
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                null,
                'foo',
                false)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                '',
                'foo',
                false)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [],
                'foo',
                false)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'],
                'foo',
                false)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'],
                'foo',
                true)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'], 75, 39, 27],
                'foo',
                false)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'], 75, 39, 27],
                'foo',
                true)],
            [new LinkedContentTestData(
                LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'],
                [LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2'], 75, 'random_str', 39, 27],
                'foo',
                true)],
        );
    }
}