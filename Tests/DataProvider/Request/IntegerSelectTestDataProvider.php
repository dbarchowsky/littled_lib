<?php
namespace Littled\Tests\DataProvider\Request;

use Littled\Request\IntegerSelect;


class IntegerSelectTestDataProvider
{
	public static function renderTestProvider(): array
	{
		return array(
			[new IntegerSelectTestData(
				'/<la'.'bel.*>'. SelectTestData::TEST_LABEL.'<\/label>(.|\n)*<select name=\"'. SelectTestData::TEST_KEY.'\" id=\"'. SelectTestData::TEST_KEY.'\">(.|\n)*<option value=\"88\">88<\/option>(.|\n)*<\/select>/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS)],
			[new IntegerSelectTestData(
				'/<label.*>new special label<\/label>(.|\n)*<select/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'new special label')],
			[new IntegerSelectTestData(
				'/<div class=\"form-cell my-special-class\">(.|\n)*<label(.|\n)*<select/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', 'my-special-class')],
			[new IntegerSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\"/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', '', true)],
			[new IntegerSelectTestData(
				'/<div class=\"form-cell\">(.|\n)*<label(.|\n)*<select.*multiple=\"multiple\" size=\"5\"(.|\n)*<option value=\"2\">2<\/option>(.|\n)*<\/select>\n/',
				new IntegerSelect(SelectTestData::TEST_LABEL, SelectTestData::TEST_KEY),
				IntegerSelectTestData::TEST_OPTIONS,
				'', '', true, 5)],
		);
	}
}