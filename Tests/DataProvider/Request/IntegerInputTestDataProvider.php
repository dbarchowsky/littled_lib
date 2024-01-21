<?php

namespace LittledTests\DataProvider\Request;


use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;

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

    /**
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public static function escapeSQLTestProvider(): array
    {
        $conn = new MySQLConnection();
        $conn->connectToDatabase();
        $mysqli = $conn->getMysqli();
        return array(
            [null, '[use default]', $mysqli, 'input value: [not set]'],
            [null, true, $mysqli, 'input value: true'],
            [null, 'true', $mysqli, 'input value: "true"'],
            [1, '1', $mysqli, 'input value: "1"'],
            [1, 1, $mysqli, 'input value: 1'],
            [null, false, $mysqli, 'input value: false'],
            [null, 'false', $mysqli, 'input value: "false"'],
            [0, '0', $mysqli, 'input value: "0"'],
            [0, 0, $mysqli, 'input value: 0'],
            [45, 45, $mysqli, 'input value: 45'],
            [56, '56', $mysqli, 'input value: "56"'],
            [3, 3.005, $mysqli, 'input value: 3.005'],
            [3, '3.07', $mysqli, 'input value: "3.07"'],
            [4, 3.51, $mysqli, 'input value: 3.51'],
            [null, 'foobar', $mysqli, 'input value: "foobar"'],
        );
    }

    public static function hasDataTestProvider(): array
    {
        return array(
            [false, null],
            [true, 1],
            [true, 0],
            [false, true],
            [false, false],
            [false, 'true'],
            [false, 'false'],
            [false, 'crap'],
            [false, ''],
            [true, 22],
            [true, 22.6],
            [false, []],
            [false, [4, 7, 9]],
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

	public static function renderHiddenTestProvider(): array
	{
		return array(
			[new HiddenInputTestData('',
				'/<'.'input type="hidden" name="'. IntegerInputTestData::DEFAULT_KEY.'" id="'. IntegerInputTestData::DEFAULT_KEY.'" value="" \/>/',
				'default', '[use default]')],
			[new HiddenInputTestData('',
				'/<'.'input.* value="45" \/>/',
				'45 as number', 45)],
			[new HiddenInputTestData('',
				'/<'.'input.* value="46" \/>/',
				'decimal as number', 45.75)],
			[new HiddenInputTestData('',
				'/<'.'input.* value="5" \/>/',
				'decimal as number', 3,
				false, null, '', '', '', '', false,
				5 )],
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

    public static function setInputValueAsArrayTestProvider(): array
    {
        return array(
            [[], [], 'empty array'],
            [[3], [3], 'single element'],
            [[1], [1], 'single element value 1'],
            [[0], [0], 'single element value 0'],
            [[0, 1, 3], [0, 1, 3], 'multiple integers'],
            [[6, 8, 9, 10], [6, 8.2, 9, 10], 'multiple mixed float and integer'],
            [[6, 9, 10], [6, 'two', 9, 10], 'multiple mixed float and string'],
        );
    }

    public static function validateTestProvider(): array
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