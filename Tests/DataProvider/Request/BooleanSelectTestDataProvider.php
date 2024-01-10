<?php

namespace LittledTests\DataProvider\Request;


class BooleanSelectTestDataProvider
{
    public static function lookupValueInSelectedValuesTestProvider(): array
    {
        return array(
            array(true, true, true),
            array(false, true, false),
            array(false, false, true),
            array(true, true, 1),
            array(true, true, '1'),
            array(true, true, 'true'),
            array(true, true, 'yes'),
            array(true, true, 'on'),
            array(false, true, 0),
            array(false, true, '0'),
            array(false, true, 'false'),
            array(false, true, 'no'),
            array(false, true, 'off'),
            array(false, false, 1),
            array(false, false, '1'),
            array(false, false, 'true'),
            array(false, false, 'yes'),
            array(false, false, 'on'),
            array(true, false, 0),
            array(true, false, '0'),
            array(true, false, 'false'),
            array(true, false, 'no'),
            array(true, false, 'off'),
        );
    }

    public static function renderInputTestProvider(): array
    {
        return array_map(
            function($e) { return array($e); }, array(
                new BooleanSelectTestData(
                    '/^\W*<select '.
                    'name="'.BooleanInputTestData::DEFAULT_KEY.'" '.
                    'id="'.BooleanInputTestData::DEFAULT_KEY.'">\s*'.
                    '<option value=""> <\/option>[\s\S]*'.
                    '<option value="1">enabled<\/option>[\s\S]*'.
                    '<option value="0">disabled<\/option>\s*'.
                    '<\/select>/',
                    '[use default]',
                    array('' => ' ', '1' => 'enabled', '0' => 'disabled')),
            new BooleanSelectTestData(
                '/^\W*<select '.
                'name="'.BooleanInputTestData::DEFAULT_KEY.'" '.
                'id="'.BooleanInputTestData::DEFAULT_KEY.'">\s*'.
                '<option value=""> <\/option>[\s\S]*'.
                '<option value="1" selected="selected">enabled<\/option>[\s\S]*'.
                '<option value="0">disabled<\/option>\s*'.
                '<\/select>/',
                true,
                array('' => ' ', '1' => 'enabled', '0' => 'disabled')),
            new BooleanSelectTestData(
                '/^\W*<select '.
                'name="'.BooleanInputTestData::DEFAULT_KEY.'" '.
                'id="'.BooleanInputTestData::DEFAULT_KEY.'">\s*'.
                '<option value=""> <\/option>[\s\S]*'.
                '<option value="1">enabled<\/option>[\s\S]*'.
                '<option value="0" selected="selected">disabled<\/option>\s*'.
                '<\/select>/',
                false,
                array('' => ' ', '1' => 'enabled', '0' => 'disabled')),
        ));
    }
}