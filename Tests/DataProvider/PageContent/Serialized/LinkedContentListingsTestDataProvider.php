<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class LinkedContentListingsTestDataProvider
{
    public static function mergeArgListsTestProvider(): array
    {
        return array(
            [[1], [1]],
            [[3,6,2], [3,6,2]],
            [[3], [], 3],
            [[4, null], [4], null],
            [[4, 18, 32, 66], [4], 18, 32, 66],
        );
    }

    public static function mergeArgTypeStringTestProvider(): array
    {
        return array(
            ['foobar', 'foo', 'bar'],
            ['foo', 'foo', ''],
            ['bar', '', 'bar'],
            ['foo', 'foo', null],
            ['bar', null, 'bar'],
        );
    }
}