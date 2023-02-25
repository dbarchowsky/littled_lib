<?php
namespace Littled\Tests\DataProvider\Request;

use TypeError;
use Littled\Request\DateInput;


class DateInputTestDataProvider
{
    public static function escapeSQLProvider(): array
    {
        return array(
            array(new DateFormatTestData('May 23, 2018', '', '2018-05-23 00:00:00', null, null, 'source: "F d, Y"/format: ""')),
            array(new DateFormatTestData(null, '', '', null, null, 'source: null/format: ""')),
            array(new DateFormatTestData('', '', '', null, null, 'source: ""/format: ""')),
            array(new DateFormatTestData('fdoclxps', '', '', null, null, 'source: [invalid date string]/format: ""'))
        );
    }

    public static function formatDateValueProvider(): array
    {
        return array_map(function(DateFormatTestData $e) { return $e->dateFormatProvider(); },  array(
            new DateFormatTestData('', '[use default]', null),
            new DateFormatTestData(null, '[use default]', null),
            new DateFormatTestData('May 23, 2018', 'Ymd', '20180523'),
            new DateFormatTestData('May 23, 2018', 'm/d/Y', '05/23/2018'),
            new DateFormatTestData('06/13/1999', 'F j, Y', 'June 13, 1999')
        ));
    }

    public static function formatDateValueUsingInvalidDateProvider(): array
    {
        $pattern = '/'.DateFormatTestData::DEFAULT_LABEL.'.*not.*recognized.* format/i';
        return array_map(function(DateFormatTestData $e) { return $e->dateFormatProvider(); },  array(
            new DateFormatTestData('fdfsfdsf', '[use default]', $pattern),
            new DateFormatTestData('dfdfdfjdl', 'Y-m-d', $pattern)
        ));
    }

    public static function renderTestProvider(): array
    {
        return array(
            array(new DateTextFieldTestData(
                '/<input.* value=\"\"/i',
                'add default values')),
            array(new DateTextFieldTestData(
                '/<input.* value=\"2022-04-15\"/i',
                'date value to MM/DD/YYYY',
                '04/15/2022')),
            array(new DateTextFieldTestData(
                '/<input.* value=\"2022-01-29\"/i',
                'date in Mon DD, YYYY format',
                'Jan 29, 2022')),
            array(new DateTextFieldTestData(
                '/<label.*>'.DateTextFieldTestData::TEST_INPUT_LABEL.'<\/label>/i',
                'field not required confirming required indicator is not displayed',
                '', false)),
            array(new DateTextFieldTestData(
                '/<label.*>'.DateTextFieldTestData::TEST_INPUT_LABEL.preg_quote(DateInput::getRequiredIndicator()).'<\/label>/i',
                'field is required; confirming the required indicator is displayed',
                '', true)),
            array(new DateTextFieldTestData(
                "/<label for=\"".DateTextFieldTestData::TEST_INPUT_KEY."\"(.|\\n)*<input.* name=\"".DateTextFieldTestData::TEST_INPUT_KEY."\".* id=\"".DateTextFieldTestData::TEST_INPUT_KEY."\"/i",
                'index value not set, confirm label element\'s "for" attribute value',
                '', false, null)),
            array(new DateTextFieldTestData(
                "/<label for=\"".DateTextFieldTestData::TEST_INPUT_KEY."-50\"(.|\\n)*<input.* name=\"".DateTextFieldTestData::TEST_INPUT_KEY."\[\]\".* id=\"".DateTextFieldTestData::TEST_INPUT_KEY."-50\"/i",
                'index value set, confirm label element\'s "for" attribute value',
                1, false, 50)),
            array(new DateTextFieldTestData(
                '/<label.*>My Label<\/label>/i',
                'override label',
                '', false, null, '', '', '', 'My Label')),
            array(new DateTextFieldTestData(
                '/<label.*>'.DateTextFieldTestData::TEST_INPUT_LABEL.'<\/label>/i',
                'confirming internal default label value')),
            array(new DateTextFieldTestData(
                '/<div class=\"my-custom-class\">\s*<label.*>\s*<div><input type=\"date\" class=\"datepicker\"/',
                'override container css class',
                '', false, null, '', '', 'my-custom-class')),
            array(new DateTextFieldTestData(
                '/<div>\s*<label.*>\s*<div><input /',
                'no css class specified')),
            array(new DateTextFieldTestData(
                '/<div>\s*<label.*>\s*<div><input type=\"date\" class=\"my-input-class\"/',
                'input class set',
            '', false, null, 'my-input-class')),
            array(new DateTextFieldTestData(
                '/<div class=\"my-container-class\">\s*<label.*>\s*<div><input type=\"date\" class=\"my-input-class\"/',
                'input & container class set',
                '', false, null, 'my-input-class', 'my-container-class')),
            array(new DateTextFieldTestData(
                '/<input.* maxlength=\"'.DateInput::DEFAULT_SIZE_LIMIT.'\"/i',
                'size limit value')),
        );
    }

    public static function setInputValueProvider(): array
    {
        $m = date('m');
        $d = date('d');
        return array_map(function(DateFormatTestData $e) { return $e->mapSetInputValueData(); },  array(
            new DateFormatTestData(null, '', null),
            new DateFormatTestData('', '', null),
            new DateFormatTestData('1/1/2018', '', '2018-01-01'),
            new DateFormatTestData('2018-01-01', '', '2018-01-01'),
            new DateFormatTestData('June 13, 1969', '', '1969-06-13'),
            new DateFormatTestData('dfdfdfd', '', null),
            new DateFormatTestData('7269', '', "7269-$m-$d")));
    }

    public static function validateMissingDateValueProvider(): array
    {
        return array_map(function(DateFormatTestData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatTestData('[use default]', '', '/test date is required/i'),
            new DateFormatTestData(null, '', '/'.DateFormatTestData::DEFAULT_LABEL.' is required/i'),
            new DateFormatTestData('', '', '/'.DateFormatTestData::DEFAULT_LABEL.' is required/i')));
    }

    public static function validateInvalidDateFormatsProvider(): array
    {
        return array_map(function(DateFormatTestData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatTestData('32-2-2018', '', '/'.DateFormatTestData::DEFAULT_LABEL.' is not in a recognized date format/i'),
            new DateFormatTestData('duygoci', '', '/'.DateFormatTestData::DEFAULT_LABEL.' is not in a recognized date format/i')
        ));
    }

    public static function validateValidValuesProvider(): array
    {
        return array_map(function(DateFormatTestData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatTestData('1/15/2018', '', '2018-01-15 00:00:00'),
            new DateFormatTestData('12/31/1999', '', '1999-12-31 00:00:00'),
            new DateFormatTestData('1/1/2094', '', '2094-01-01 00:00:00'),
            new DateFormatTestData('2018-02-14', '', '2018-02-14 00:00:00'),
            new DateFormatTestData('01/01/2004', '', '2004-01-01 00:00:00'),
            new DateFormatTestData('11/01/2020', '', '2020-11-01 00:00:00'),
            new DateFormatTestData('11-1-2020', '', '2020-01-11 00:00:00'),
            new DateFormatTestData('May 13, 1980', '', '1980-05-13 00:00:00')));
    }
}