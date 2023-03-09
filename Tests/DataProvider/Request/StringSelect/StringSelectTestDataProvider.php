<?php
namespace Littled\Tests\DataProvider\Request\StringSelect;

use Littled\Exception\ContentValidationException;
use Littled\Request\StringSelect;
use Littled\Tests\DataProvider\Request\SelectTestData;


class StringSelectTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			[new StringSelectTestData(
				'/<la'.'bel.*>'. SelectTestData::TEST_LABEL.'<\/label>(.|\n)*<select name=\"'. SelectTestData::TEST_KEY.'\" id=\"'. SelectTestData::TEST_KEY.'\">(.|\n)*<option value=\"foo\">option foo<\/option>(.|\n)*<\/select>/',
				new StringSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS)],
			[new StringSelectTestData(
				'/<label.*>new special label<\/label>(.|\n)*<select/',
				new StringSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'new special label')],
			[new StringSelectTestData(
				'/<div class=\"form-cell my-special-class\">(.|\n)*<label(.|\n)*<select/',
				new StringSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'', 'my-special-class')],
			[new StringSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\"/',
				new StringSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'', '', true)],
			[new StringSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\" size=\"5\"(.|\n)*<option value=\"2\">option two<\/option>(.|\n)*<\/select>\n/',
				new StringSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'', '', true, 5)],
		);
	}

    public static function setInputValueTestProvider(): array
    {
        return array(
            array([], []),
            array([], ''),
            array(['foo'], 'foo'),
            array(['foo'], ['foo']),
            array(['1', '0', '62.34'], [1, 0, 62.34]),
            array(['foo', 'bar'], ['', 'foo', '', 'bar', '']),
        );
    }

    public static function validateTestProvider(): array
    {
        return array_map(function($e) { return array($e); }, array(
            new ValidateTestData(
                new ValidateTestExpectations(ContentValidationException::class, '/is required/i', 0),
                true,
                true,
                'ssKey',
                []
            ),
            new ValidateTestData(
                new ValidateTestExpectations(ContentValidationException::class, '/is required/i', 0),
                false,
                true,
                'ssKey',
                []
            ),
            new ValidateTestData(
                new ValidateTestExpectations('', '', 3),
                true,
                true,
                'ssKey',
                array('ssKey' => ['a', 'b', 'c'])
            ),
        ));
    }
}