<?php
namespace LittledTests\DataProvider\Filters;

class BooleanContentFilterTestDataProvider
{
    public static function escapeSQLTestProvider(): array
    {
        return array(
            array(null, null, 'null value'),
            array('1', 1, '1 as int'),
            array('0', 0, '0 as int'),
            array('1', true, 'true as boolean'),
            array('0', false, 'false as boolean'),
            array(null, 'true', 'true as string'),
            array(null, 'false', 'false as string'),
            array(null, '1', '1 as string'),
            array(null, '0', '0 as string'),
            array(null, 'foo', 'string that is not a boolean value'),
        );
    }

    public static function formatQueryStringTestProvider(): array
    {
        return array(
            array('', null, 'null value'),
            array('', '', 'empty string'),
            array('', '1', '1 as string'),
            array('', '86', 'integer value as string'),
            array('key=1', 1, '1 as integer'),
            array('', 845, 'integer value'),
            array('', 'FOO', 'non-numeric string'),
            array('key=0', 0, '0 as integer'),
            array('key=1', true, 'true as boolean'),
            array('key=0', false, 'false as boolean'),
            array('', 'true', 'true as string'),
            array('', 'false', 'false as string'),
            array('', '1', '1 as string'),
            array('', '0', '0 as string'),
        );
    }
}
