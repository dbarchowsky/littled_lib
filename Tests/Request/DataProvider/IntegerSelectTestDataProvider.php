<?php
namespace Littled\Tests\Request\DataProvider;

use Littled\Request\IntegerSelect;


class IntegerSelectTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			[new IntegerSelectTestData(
				'/<la'.'bel.*>'.IntegerSelectTestData::TEST_LABEL.'<\/label>(.|\n)*<select name=\"'.IntegerSelectTestData::TEST_KEY.'\" id=\"'.IntegerSelectTestData::TEST_KEY.'\">(.|\n)*<option value=\"88\">88<\/option>(.|\n)*<\/select>/',
				new IntegerSelect(IntegerSelectTestData::TEST_LABEL, IntegerSelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS)],
			[new IntegerSelectTestData(
				'/<label.*>new special label<\/label>(.|\n)*<select/',
				new IntegerSelect(IntegerSelectTestData::TEST_LABEL, IntegerSelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'new special label')],
			[new IntegerSelectTestData(
				'/<div class=\"form-cell my-special-class\">(.|\n)*<label(.|\n)*<select/',
				new IntegerSelect(IntegerSelectTestData::TEST_LABEL, IntegerSelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', 'my-special-class')],
			[new IntegerSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\"/',
				new IntegerSelect(IntegerSelectTestData::TEST_LABEL, IntegerSelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', '', true)],
			[new IntegerSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\" size=\"5\"(.|\n)*<option value=\"2\">2<\/option>(.|\n)*<\/select>\n/',
				new IntegerSelect(IntegerSelectTestData::TEST_LABEL, IntegerSelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', '', true, 5)],
		);
	}
}