<?php
namespace LittledTests\DataProvider\Request;

use Littled\Request\IntegerSelect;
use LittledTests\TestHarness\PageContent\PageContentTestHarness;
use Exception;


class IntegerSelectTestDataProvider
{
    public static function collectRequestDataTestProvider(): array
    {
        return array(
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => null]],
            [0, 'POST', false, [IntegerSelectTestData::TEST_KEY => 0]],
            [1, 'POST', false, [IntegerSelectTestData::TEST_KEY => 1]],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => true]],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => false]],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => 'str']],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => 'one']],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => 'true']],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => 'false']],
            [null, 'POST', false, [IntegerSelectTestData::TEST_KEY => [3,5]]],
            [[3,5], 'POST', true, [IntegerSelectTestData::TEST_KEY => [3,5]]],
            [[0,1,4,8], 'POST', true, [IntegerSelectTestData::TEST_KEY => [0,'zero', null, 'one', 1, 'false', 4, 8]]],
            [[3,6,9], 'POST', true, [IntegerSelectTestData::TEST_KEY => [3, 6.4, 9]]],
            [7, 'GET', false, [IntegerSelectTestData::TEST_KEY => 7]],
            [[3,5], 'GET', true, [IntegerSelectTestData::TEST_KEY => [3,5]]],
            [7, 'manual', false, [IntegerSelectTestData::TEST_KEY => 7]],
            [[3,5], 'manual', true, [IntegerSelectTestData::TEST_KEY => [3,5]]],
        );
    }

    public static function hasDataAsArrayTestProvider(): array
    {
        return array(
            [false, []],
            [true, [1]],
            [true, [0]],
            [true, [1,2,3]],
            [true, [65,23,99]],
            [true, [6.1, 8.2]],
            [false, 'nan'],
            [true, ['nan']],
            [true, ['a', 'b', 'c']],
            [true, ['foo' => 'bar']],
            [true, 73],
        );
    }

    public static function lookupValueInSelectedValuesTestProvider(): array
    {
        return array(
            array(true, [4,6,2], true, 6),
            array(false, [4,6,2], true, 7),
            array(false, 8, false, 7),
            array(true, 8, false, 8),
            array(false, null, false, 8),
            array(false, [], false, 9),
	        array(false, [4,5,6,7], false, null),
        );
    }

    /**
     * @throws Exception
     */
    public static function renderTestProvider(): array
	{
		return array(
			[new IntegerSelectTestData(
				'/<'.'label.*>'. SelectTestData::TEST_LABEL.'<\/label>(.|\n)*<'.'select name=\"'. SelectTestData::TEST_KEY.'\" id=\"'. SelectTestData::TEST_KEY.'\">(.|\n)*<'.'option value=\"88\">88<\/option>(.|\n)*<\/select>/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS)],
			[new IntegerSelectTestData(
				'/<'.'label.*>new special label<\/label>(.|\n)*<'.'select/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'new special label')],
			[new IntegerSelectTestData(
				'/<'.'div class=\"form-cell my-special-class\">(.|\n)*<'.'label(.|\n)*<select/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', 'my-special-class')],
			[new IntegerSelectTestData(
				'/<'.'div class=\"form-cell\">(.|\n)*<'.'label(.|\n)*<'.'select.*multiple=\"multiple\"/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', '', true)],
            [new IntegerSelectTestData(
                '/<'.'div class=\"form-cell\">(.|\n)*<'.'label(.|\n)*<'.'select.*multiple=\"multiple\" size=\"5\"(.|\n)*<'.'option value=\"2\">2<\/option>(.|\n)*<\/select>\n/',
                new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
                IntegerSelectTestData::TEST_OPTIONS,
                '', '', true, 5)],
            [new IntegerSelectTestData(
                '/<'.'div class=\"form-cell\">(.|\n)*<'.
                'label(.|\n)*<'.
                'select.*multiple=\"multiple\" size=\"5\"(.|\n)*<'.
                'option value=\"2\" selected="selected">2<\/option>(.|\n)*<'.
                'option value=\"65\">65<\/option>(.|\n)*<'.
                'option value=\"5\" selected="selected">5<\/option>(.|\n)*'.
                '<\/select>\n/',
                new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
                IntegerSelectTestData::TEST_OPTIONS,
                '', '', true, 5,
                [2,5,88])],
		);
	}

    /**
     * @throws Exception
     */
    public static function renderUsingProcedureTestProvider(): array
    {
        $p = new PageContentTestHarness();
        $query = 'SELECT id, name as `option` '.
            'FROM test_table '.
            'WHERE LOWER(name) LIKE \'%hello%\' '.
            'ORDER BY IFNULL(`slot`, 999999), `name`';
        return array(
            [new IntegerSelectTestData(
                '/<'.'div class=\"form-cell\">(.|\n)*<'.'label(.|\n)*<'.
                'select.*>(.|\n)*<'.'option value=\"2215\">hello<\/option>(.|\n)*<\/select>\n/',
                new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
                $p->fetchOptions($query))],
            [new IntegerSelectTestData(
                '/<'.'div class="form-cell">(.|\n)*<'.'label(.|\n)*<'.
                'select.*>(.|\n)*<'.
                'option value="2215" selected="selected">hello<\/option>(.|\n)*<'.
                'option value="2217" selected="selected">hello hello hello<\/option>(.|\n)*'.
                '<\/select>\n/',
                new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
                $p->fetchOptions($query),
                '', '', false, null,
                [2215, 2217])],

        );
    }

    public static function validateArrayWithAllowMultipleTestProvider(): array
    {
        return array(
            ['', '[use default]', false],
            ['/is required/', '[use default]', true],
            ['', [], false],
            ['/required/', [], true],
            ['', [4, 5, 16], false],
            ['', [4, 5, 16], true],
            ['/unrecognized format/i', ['foo', 'bar'], false],
            ['/is required/', ['foo', 'bar'], true],
        );
    }

    public static function validateArrayWithoutAllowMultipleTestProvider(): array
    {
        return array(
            ['', '[use default]', false],
            ['/is required/', '[use default]', true],
            ['', '', false],
            ['/bad value/i', [], false],
            ['/bad value/i', [], true],
            ['/bad value/i', [4, 5, 16], false],
            ['/bad value/i', [4, 5, 16], true],
        );
    }

    public static function validateSingleValueTestProvider(): array
    {
        return array(
            ['', '[use default]', false],
            ['/is required/', '[use default]', true],
            ['', null, false],
            ['/is required/', null, true],
            ['', '', false],
            ['/is required/', '', true],
            ['', ' ', false],
            ['/is required/', ' ', true],
            ['', 1, false],
            ['', 1, true],
            ['', '1', false],
            ['', '1', true],
            ['', 765, false],
            ['', 0, false],
            ['', 0, true],
            ['', '0', false],
            ['', '0', true],
            ['', 5248, true],
            ['', '8356', true],
            ['', 12.6, false],
            ['', 12.6, true],
            ['/unrecognized format/', 'foo', false],
            ['/unrecognized format/', 'foo', true],
        );
    }
}