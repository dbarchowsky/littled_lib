<?php

namespace Littled\Tests\Request\DataProvider;


use Littled\Request\StringSelect;

class StringSelectTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			[new StringSelectTestData(
				'/<la'.'bel.*>'.StringSelectTestData::TEST_LABEL.'<\/label>(.|\n)*<select name=\"'.StringSelectTestData::TEST_KEY.'\" id=\"'.StringSelectTestData::TEST_KEY.'\">(.|\n)*<option value=\"foo\">option foo<\/option>(.|\n)*<\/select>/',
				new StringSelect(StringSelectTestData::TEST_LABEL, StringSelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS)],
			[new StringSelectTestData(
				'/<label.*>new special label<\/label>(.|\n)*<select/',
				new StringSelect(StringSelectTestData::TEST_LABEL, StringSelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'new special label')],
			[new StringSelectTestData(
				'/<div class=\"form-cell my-special-class\">(.|\n)*<label(.|\n)*<select/',
				new StringSelect(StringSelectTestData::TEST_LABEL, StringSelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'', 'my-special-class')],
			[new StringSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\"/',
				new StringSelect(StringSelectTestData::TEST_LABEL, StringSelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'', '', true)],
			[new StringSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\" size=\"5\"(.|\n)*<option value=\"2\">option two<\/option>(.|\n)*<\/select>\n/',
				new StringSelect(StringSelectTestData::TEST_LABEL, StringSelectTestData::TEST_KEY),
				StringSelectTestData::TEST_OPTIONS,
				'', '', true, 5)],
		);
	}
}