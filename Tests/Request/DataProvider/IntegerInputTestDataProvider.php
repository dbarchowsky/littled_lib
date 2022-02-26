<?php

namespace Littled\Tests\Request\DataProvider;


use Littled\Database\MySQLConnection;

class IntegerInputTestDataProvider
{
	public static function collectRequestDataTestProvider(): array
	{
		return array(
			[null, null],
			[1, '1'],
			[0, '0'],
			[1, 1],
			[0, 0],
			[7640, 7640],
			[784, '784'],
			[null, 'foobar'],
			[3, 2.9],
			[0, 0.0041],
			[0, '0.0041'],
            [2, '1.55'],
			[7692, '7692.0071'],
		);
	}

    public static function escapeSQLTestProvider(): array
    {
        $conn = new MySQLConnection();
        $conn->connectToDatabase();
        $mysqli = $conn->getMysqli();
        return array(
            ['NULL', '[use default]', $mysqli, 'input value: [not set]'],
            ['NULL', true, $mysqli, 'input value: true'],
            ['NULL', 'true', $mysqli, 'input value: "true"'],
            ['1', '1', $mysqli, 'input value: "1"'],
            ['1', 1, $mysqli, 'input value: 1'],
            ['NULL', false, $mysqli, 'input value: false'],
            ['NULL', 'false', $mysqli, 'input value: "false"'],
            ['0', '0', $mysqli, 'input value: "0"'],
            ['0', 0, $mysqli, 'input value: 0'],
            ['45', 45, $mysqli, 'input value: 45'],
            ['56', '56', $mysqli, 'input value: "56"'],
            ['3', 3.005, $mysqli, 'input value: 3.005'],
            ['3', '3.07', $mysqli, 'input value: "3.07"'],
            ['4', 3.51, $mysqli, 'input value: 3.51'],
            ['NULL', 'foobar', $mysqli, 'input value: "foobar"'],
        );
    }

	public static function saveInFormTestProvider(): array
	{
		return array(
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/',
                'NULL', null)],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/',
                '1 as number', 1)],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/',
                '1 as string', '1')],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/',
                '0 as number', 0)],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/',
                '0 as string', '0')],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/',
                'non-numeric string', 'foo')],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/',
                'decimal < 1 as string', '0.45')],
            [new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/',
                'decimal < 1', 0.54)],
			[new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"25\" \/>/',
                'decimal as string', '24.84')],
            [new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/',
                'true as bool', true)],
            [new IntegerInputTestData(null,
                '/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/',
                'false as bool', false)],
		);
	}

	public static function setInputValueTestProvider(): array
	{
		return array(
			[null, '[use default]', 'input value: "[use default]"'],
			[0, true, 'input value: true'],
			[null, 'true', 'input value: "true"'],
			[1, '1', 'input value: "1"'],
			[1, 1, 'input value: 1'],
			[0, false, 'input value: false'],
			[null, 'false', 'input value: "false"'],
			[0, '0', 'input value: "0"'],
			[0, 0, 'input value: 0'],
			[45, 45, 'input value: 45'],
			[45, '45', 'input value: "45"'],
			[33, 32.7, 'input value: 32.7'],
			[33, '32.7', 'input value: "32.7"'],
			[null, 'funky chicken', 'input value: "funky chicken"'],
			[null, null, 'input value: null'],
		);
	}

	public static function renderTestProvider(): array
	{
		return array(
			[new IntegerInputTestData('',
                '/<div(.|\n)*?<label.*?>'.IntegerInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input type=\"number\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\".* \/>/',
                'default', '[use default]')],
			[new IntegerInputTestData('',
                '/<div class=\"form-cell\">(.|\n)*<input.*value=\"45\".* \/>/',
                '45 as number', 45)],
            [new IntegerInputTestData('',
                '/<div class=\"form-cell\">(.|\n)*<input.*value=\"46\".* \/>/',
                'decimal as number', 45.75)],
            [new IntegerInputTestData('',
                '/<div class=\"my-container-class my-custom-class\">(.|\n)*<input.*value=\"\".* class=\"my-input-class\".* \/>/',
                'input & container & custom css classes',
                null, false, null,
            'my-input-class', 'my-container-class', 'my-custom-class')],
		);
	}

    public static function renderInputTestProvider(): array
    {
        return array(
            [new IntegerInputTestData('',
                '/<input type=\"number\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" id=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"\" title=\"'.IntegerInputTestData::DEFAULT_LABEL.'\" maxlength=\"50\" \/>/',
                'default',
                '[use default]')],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* \/>/',
                '45 as number',
                45)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* \/>/',
                'decimal value',
                45.37)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* title=\"my special label\".* \/>/',
                'label override',
                45, false, null, '', '', '', 'my special label')],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* title=\"my special label\".* placeholder=\"my special label\" \/>/',
                'placeholder with custom label',
                45, false, null,
                '', '', '', 'my special label', true)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* title=\"'.IntegerInputTestData::DEFAULT_LABEL.'\".* placeholder=\"'.IntegerInputTestData::DEFAULT_LABEL.'\" \/>/',
                'display placeholder without custom label',
                45, false, null, '', '', '', '', true)],
            [new IntegerInputTestData('',
                '/<input.* name=\"'.IntegerInputTestData::DEFAULT_KEY.'\[0\]\" id=\"'.IntegerInputTestData::DEFAULT_KEY.'-0\" value=\"45\".* \/>/',
                '0 index',
                45, false, 0)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* maxlength=\"50\" \/>/',
                'css override',
                45, false, null, '', '', 'my-special-class')],
        );
    }
}