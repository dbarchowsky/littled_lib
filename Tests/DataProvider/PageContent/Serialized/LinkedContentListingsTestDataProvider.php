<?php

namespace LittledTests\DataProvider\PageContent\Serialized;


class LinkedContentListingsTestDataProvider
{
    public static function fillLinkInputFromListingsDataTestProvider(): array
    {
        return array(
            [[45], [
                (object)['id' => 45, 'name' => 'foo']]],
            [[45,62,139], [
                (object)['id' => 45, 'name' => 'foo'],
                (object)['id' => 62, 'name' => 'bar'],
                (object)['id' => 139, 'name' => 'biz'],
                ]],
            [[73,92,103], [
                (object)['parent2_id' => 73, 'name' => 'bash'],
                (object)['parent2_id' => 92, 'name' => 'ipsum'],
                (object)['parent2_id' => 103, 'name' => 'dolor'],
            ]],
            [[], [
                (object)['bogus_id' => 94, 'name' => 'bash'],
                (object)['bogus_id' => 6, 'name' => 'ipsum'],
                (object)['bogus_id' => 282, 'name' => 'dolor'],
            ]],
        );
    }

    public static function lookupIdPropertyNameTestProvider(): array
    {
        return array(
            ['id', (object)['id' => 44]],
            ['id', (object)['' => 'name', 'id' => 12]],
            ['parent2_id', (object)['' => 'name', 'parent2_id' => 16, 'bogus_id' => 13]],
            ['', (object)['' => 'name', 'bogus_id' => 32]],
        );
    }

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