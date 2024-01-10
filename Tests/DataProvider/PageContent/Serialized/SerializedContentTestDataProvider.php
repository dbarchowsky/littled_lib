<?php
namespace LittledTests\DataProvider\PageContent\Serialized;

class SerializedContentTestDataProvider
{
    public static function formatDatabaseColumnListBooleanValuesTestProvider(): array
    {
        return array(
            array(null, null),
            array(null, ''),
            array(null, 'foobar'),
            array(true, 1),
            array(false, 0),
            array(null, 27.65),
            array(false, 'false'),
            array(true, 'true'),
            array(false, false),
            array(true, true),
            array(null, 'one'),
            array(true, 1),
            array(false, 0),
            array(false, 'off'),
            array(true, 'on'),
            array(false, 'no'),
            array(true, 'yes'),
        );
    }

    public static function formatDatabaseColumnListDateValuesTestProvider(): array
    {
        return array(
            array(null, null),
            array(null, ''),
            array('1998-02-25 00:00:00', '2/25/1998'),
            array('2023-02-26 00:00:00', '2023-02-26'),
            array('2008-06-19 15:45:00', '6/19/2008 3:45pm'),
        );
    }

    public static function formatDatabaseColumnListIntegerValuesTestProvider(): array
    {
        return array(
            array(null, null),
            array(null, ''),
            array(null, 'foobar'),
            array(722, 722),
            array(722, '722'),
            array(27, 27.45),
            array(28, 27.65),
            array(null, 'false'),
            array(null, 'true'),
            array(null, false),
            array(null, true),
            array(null, 'one'),
            array(1, 1),
            array(0, 0),
        );
    }

    public static function formatDatabaseColumnListStringValuesTestProvider(): array
    {
        return array(
            array('', null),
            array('', ''),
            array('foobar', 'foobar'),
            array('722', 722),
            array('722', '722'),
            array('27.45', 27.45),
            array('false', 'false'),
            array('true', 'true'),
            array('one', 'one'),
            array('1', 1),
            array('0', 0),
        );
    }
}