<?php
namespace LittledTests\DataProvider\Filters;

class DateContentFilterTestDataProvider
{
    public static function collectValueTestProvider(): array
    {
        return array(
            [null, null, 'filter not set'],
            ['invalid date', '[Unrecognized date value.]', 'invalid date value'],
            ['2022-07-03', '07/03/2022', 'valid date value (YYYY-MM-DD)'],
            ['07/03/2022', '07/03/2022', 'valid date value (MM/DD/YYY)'],
            ['', null, 'empty string'],
        );
    }

    public static function escapeSQLTestProvider(): array
    {
        return array(
            [null, null, 'null value'],
            ['', null, 'empty string'],
            ['6/15/2016', "'2016-06-15'", 'valid string (M/DD/YYYY)'],
            ['fdjkfdjldfld', null, 'invalid date value'],
            [45, null, 'integer value'],
            [true, null, 'boolean value'],
        );
    }
}