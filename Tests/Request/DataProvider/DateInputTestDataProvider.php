<?php

namespace Littled\Tests\Request\DataProvider;

use Littled\Request\DateInput;
use Littled\Request\DateTextField;
use Littled\Tests\Request\DateInputTest;

class DateInputTestDataProvider
{
    public static function escapeSQLProvider(): array
    {
        return array_map(function(DateFormatData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatData('May 23, 2018', '', "'2018-05-23 00:00:00'"),
            new DateFormatData(null, '', "NULL"),
            new DateFormatData('', '', "NULL"),
            new DateFormatData('fdoclxps', '', "NULL")));
    }

    public static function formatDateValueProvider(): array
    {
        return array_map(function(DateFormatData $e) { return $e->dateFormatProvider(); },  array(
            new DateFormatData('', '[use default]', ''),
            new DateFormatData(null, '[use default]', ''),
            new DateFormatData('May 23, 2018', 'Ymd', '20180523'),
            new DateFormatData('May 23, 2018', 'm/d/Y', '05/23/2018'),
            new DateFormatData('06/13/1999', 'F j, Y', 'June 13, 1999')
        ));
    }

    public static function formatDateValueUsingInvalidDateProvider(): array
    {
        $pattern = '/'.DateInputTest::DEFAULT_LABEL.'.*not.*recognized.* format/i';
        return array_map(function(DateFormatData $e) { return $e->dateFormatProvider(); },  array(
            new DateFormatData('fdfsfdsf', '[use default]', $pattern),
            new DateFormatData('dfdfdfjdl', 'Y-m-d', $pattern)
        ));
    }

    public static function renderTestProvider(): array
    {
        return array(
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, false, ''),
                '/<input.* value=\"\"/i',
                '', '',
                'no value; input\'s value attribute has no value'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, false, '04/15/2022'),
                '/<input.* value=\"04\/15\/2022\"/i',
                '', '',
                'value set; input\'s value attribute set to value'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, false, 'Jan 29, 2022'),
                '/<input.* value=\"01\/29\/2022\"/i',
                '', '',
                'value set to plain english value; input\'s value attribute set to standardized value'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, false, ''),
                '/<label.*>'.DateInputTest::DEFAULT_LABEL.'<\/label>/i',
                '', '',
                'field not required; required indicator not displayed'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, true, ''),
                '/<label.*>'.DateInputTest::DEFAULT_LABEL.preg_quote(DateInput::getRequiredIndicator()).'<\/label>/i',
                '', '',
                'field is required; required indicator displayed'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY),
                "/<label for=\"".DateInputTest::DEFAULT_KEY."\"(.|\\n)*<input.* name=\"".DateInputTest::DEFAULT_KEY."\".* id=\"".DateInputTest::DEFAULT_KEY."\"/i",
                '', '',
                'index value not set, label for attribute value'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, false, '', 50, 1),
                "/<label for=\"".DateInputTest::DEFAULT_KEY."-1\"(.|\\n)*<input.* name=\"".DateInputTest::DEFAULT_KEY."\[\]\".* id=\"".DateInputTest::DEFAULT_KEY."-1\"/i",
                '', '',
                'index value set, label for attribute value'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY),
                '/<label.*>My Label<\/label>/i',
                'My Label', '',
                'override label'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY),
                '/<label.*>'.DateInputTest::DEFAULT_LABEL.'<\/label>/i',
                '', '',
                'using internal label value'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY, false, ''),
                '/<div.* class=\"my-class\".*><input /i',
                '', 'my-class',
                'override css class'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY),
                '/<div><input /i',
                '', '',
                'no css class specified'],
            [new DateTextField(DateInputTest::DEFAULT_LABEL, DateInputTest::DEFAULT_KEY),
                '/<input.* maxlength=\"'.DateInput::DEFAULT_SIZE_LIMIT.'\"/i',
                '', '',
                'size limit value'],
        );
    }

    public static function setInputValueProvider(): array
    {
        $m = date('m');
        $d = date('d');
        return array_map(function(DateFormatData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatData('', '', ''),
            new DateFormatData('1/1/2018', '', '2018-01-01'),
            new DateFormatData('2018-01-01', '', '2018-01-01'),
            new DateFormatData('June 13, 1969', '', '1969-06-13'),
            new DateFormatData('dfdfdfd', '', ''),
            new DateFormatData('7269', '', "7269-$m-$d")));
    }

    public static function validateMissingDateValueProvider(): array
    {
        return array_map(function(DateFormatData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatData('[use default]', '', '/test date is required/i'),
            new DateFormatData(null, '', '/'.DateInputTest::DEFAULT_LABEL.' is required/i'),
            new DateFormatData('', '', '/'.DateInputTest::DEFAULT_LABEL.' is required/i')));
    }

    public static function validateInvalidDateFormatsProvider(): array
    {
        return array_map(function(DateFormatData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatData('32-2-2018', '', '/'.DateInputTest::DEFAULT_LABEL.' is not in a recognized date format/i'),
            new DateFormatData('duygoci', '', '/'.DateInputTest::DEFAULT_LABEL.' is not in a recognized date format/i')
        ));
    }

    public static function validateValidValuesProvider(): array
    {
        return array_map(function(DateFormatData $e) { return $e->dateStringProvider(); },  array(
            new DateFormatData('1/15/2018', '', '2018-01-15 00:00:00'),
            new DateFormatData('12/31/1999', '', '1999-12-31 00:00:00'),
            new DateFormatData('1/1/2094', '', '2094-01-01 00:00:00'),
            new DateFormatData('2018-02-14', '', '2018-02-14 00:00:00'),
            new DateFormatData('01/01/2004', '', '2004-01-01 00:00:00'),
            new DateFormatData('11/01/2020', '', '2020-11-01 00:00:00'),
            new DateFormatData('11-1-2020', '', '2020-01-11 00:00:00'),
            new DateFormatData('May 13, 1980', '', '1980-05-13 00:00:00')));
    }
}