<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


use LittledTests\TestHarness\PageContent\Serialized\TestTableTestHarness;

class SerializedContentUpdateIdTestDataProvider
{
    public static function updateIdAfterCommitWithInsertQueryTestProvider(): array
    {
        return array(
            [new SerializedContentUpdateIdTestData(
                true, false, null, 'foo', 5689, 10)],
            [new SerializedContentUpdateIdTestData(
                true, true, null, 'foo', 5689, 10)],
            [new SerializedContentUpdateIdTestData(
                true, false,
                TestTableTestHarness::NONEXISTENT_RECORD_ID, 'foo', 5689, 10)],
            [new SerializedContentUpdateIdTestData(
                false, false,
                TestTableTestHarness::EXISTING_RECORD_ID, 'foo', 5689, 10)],
        );
    }

    public static function updateIdAfterCommitWithProcedureTestProvider(): array
    {
        return array(
            [new SerializedContentUpdateIdTestData(
                true, false, null, 'foo', 5689, 10)],
            [new SerializedContentUpdateIdTestData(
                true, true, null, 'foo', 5689, 10)],
            [new SerializedContentUpdateIdTestData(
                true, false,
                TestTableTestHarness::NONEXISTENT_RECORD_ID, 'foo', 5689, 10)],
            [new SerializedContentUpdateIdTestData(
                false, false,
                TestTableTestHarness::EXISTING_RECORD_ID, 'foo', 5689, 10)],
        );
    }
}