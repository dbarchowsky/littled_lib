<?php

namespace Littled\Tests\Request\DataProvider;

use Littled\Request\DateInput;
use Littled\Request\DateTextField;

class DateInputTestDataProvider
{
    public static function escapeSQLProvider(): array
    {
        return array_map(function(DateFormatTestData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatTestData('May 23, 2018', '', "'2018-05-23 00:00:00'"),
            new DateFormatTestData(null, '', "NULL"),
            new DateFormatTestData('', '', "NULL"),
            new DateFormatTestData('fdoclxps', '', "NULL")));
    }

    public static function formatDateValueProvider(): array
    {
        return array_map(function(DateFormatTestData $e) { return $e->dateFormatProvider(); },  array(
            new DateFormatTestData('', '[use default]', ''),
            new DateFormatTestData(null, '[use default]', ''),
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
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false, ''),
                '/<input.* value=\"\"/i',
                '', '',
                'no value; input\'s value attribute has no value'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false, '04/15/2022'),
                '/<input.* value=\"04\/15\/2022\"/i',
                '', '',
                'value set; input\'s value attribute set to value'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false, 'Jan 29, 2022'),
                '/<input.* value=\"01\/29\/2022\"/i',
                '', '',
                'value set to plain english value; input\'s value attribute set to standardized value'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false, ''),
                '/<label.*>'.DateFormatTestData::DEFAULT_LABEL.'<\/label>/i',
                '', '',
                'field not required; required indicator not displayed'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, true, ''),
                '/<label.*>'.DateFormatTestData::DEFAULT_LABEL.preg_quote(DateInput::getRequiredIndicator()).'<\/label>/i',
                '', '',
                'field is required; required indicator displayed'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY),
                "/<label for=\"".DateFormatTestData::DEFAULT_KEY."\"(.|\\n)*<input.* name=\"".DateFormatTestData::DEFAULT_KEY."\".* id=\"".DateFormatTestData::DEFAULT_KEY."\"/i",
                '', '',
                'index value not set, label for attribute value'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false, '', 50, 1),
                "/<label for=\"".DateFormatTestData::DEFAULT_KEY."-1\"(.|\\n)*<input.* name=\"".DateFormatTestData::DEFAULT_KEY."\[\]\".* id=\"".DateFormatTestData::DEFAULT_KEY."-1\"/i",
                '', '',
                'index value set, label for attribute value'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY),
                '/<label.*>My Label<\/label>/i',
                'My Label', '',
                'override label'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY),
                '/<label.*>'.DateFormatTestData::DEFAULT_LABEL.'<\/label>/i',
                '', '',
                'using internal label value'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY, false, ''),
                '/<div.* class=\"my-class\".*><input /i',
                '', 'my-class',
                'override css class'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY),
                '/<div><input /i',
                '', '',
                'no css class specified'],
            [new DateTextField(DateFormatTestData::DEFAULT_LABEL, DateFormatTestData::DEFAULT_KEY),
                '/<input.* maxlength=\"'.DateInput::DEFAULT_SIZE_LIMIT.'\"/i',
                '', '',
                'size limit value'],
        );
    }

    public static function setInputValueProvider(): array
    {
        $m = date('m');
        $d = date('d');
        return array_map(function(DateFormatTestData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatTestData('', '', ''),
            new DateFormatTestData('1/1/2018', '', '2018-01-01'),
            new DateFormatTestData('2018-01-01', '', '2018-01-01'),
            new DateFormatTestData('June 13, 1969', '', '1969-06-13'),
            new DateFormatTestData('dfdfdfd', '', ''),
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