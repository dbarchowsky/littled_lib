<?php

namespace Littled\Tests\Request\DataProvider;


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
        return array(
            [new IntegerInputTestData('NULL','', '[use default]')],
            [new IntegerInputTestData('NULL','', true)],
            [new IntegerInputTestData('NULL','', 'true')],
            [new IntegerInputTestData('1','', '1')],
            [new IntegerInputTestData('1','', 1)],
            [new IntegerInputTestData('NULL','', false)],
            [new IntegerInputTestData('NULL','', 'false')],
            [new IntegerInputTestData('0','', '0')],
            [new IntegerInputTestData('0','', 0)],
            [new IntegerInputTestData('45','', 45)],
            [new IntegerInputTestData('56','', '56')],
            [new IntegerInputTestData('3','', 3.005)],
            [new IntegerInputTestData('3','', '3.07')],
            [new IntegerInputTestData('4','', 3.51)],
            [new IntegerInputTestData('NULL','', 'foobar')],
        );
    }

	public static function saveInFormTestProvider(): array
	{
		return array(
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', null)],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', 1)],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', '1')],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', 0)],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', '0')],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', 'foo')],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', '0.45')],
            [new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', 0.54)],
			[new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"25\" \/>/', '24.84')],
            [new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', true)],
            [new IntegerInputTestData(null,'/<input type=\"hidden\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', false)],
		);
	}

	public static function setInputValueTestProvider(): array
	{
		return array(
			[new IntegerInputTestData(null, '', '[use default]')],
			[new IntegerInputTestData(0, '', true)],
			[new IntegerInputTestData(null, '', 'true')],
			[new IntegerInputTestData(1, '', '1')],
			[new IntegerInputTestData(1, '', 1)],
			[new IntegerInputTestData(0, '', false)],
			[new IntegerInputTestData(null, '', 'false')],
			[new IntegerInputTestData(0, '', '0')],
			[new IntegerInputTestData(0, '', 0)],
			[new IntegerInputTestData(45, '', 45)],
			[new IntegerInputTestData(45, '', '45')],
			[new IntegerInputTestData(33, '', 32.7)],
			[new IntegerInputTestData(33, '', '32.7')],
			[new IntegerInputTestData(null, '', 'some arbitrary string')],
			[new IntegerInputTestData(null, '', null)],
		);
	}

	public static function renderTestProvider(): array
	{
		return array(
			[new IntegerInputTestData('', '/<div(.|\n)*?<label.*?>'.IntegerInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input type=\"number\" step=\"0.01\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\".* \/>/', '[use default]')],
			[new IntegerInputTestData('', '/<div class=\"form-cell\">(.|\n)*<input.*value=\"45\".* \/>/', 45)],
            [new IntegerInputTestData('', '/<div class=\"form-cell\">(.|\n)*<input.*value=\"46\".* \/>/', 45.75)],
		);
	}

    public static function renderInputTestProvider(): array
    {
        return array(
            [new IntegerInputTestData('',
                '/<input type=\"number\" step=\"0.01\" name=\"'.IntegerInputTestData::DEFAULT_KEY.'\" id=\"'.IntegerInputTestData::DEFAULT_KEY.'\" value=\"\" title=\"'.IntegerInputTestData::DEFAULT_LABEL.'\" maxlength=\"50\" \/>/',
                '[use default]')],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* \/>/',
                45)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* \/>/',
                45.37)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* title=\"my special label\".* \/>/',
                45, false, null, 'my special label')],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* title=\"my special label\".* placeholder=\"my special label\" \/>/',
                45, false, null, 'my special label', '', true)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* title=\"'.IntegerInputTestData::DEFAULT_LABEL.'\".* placeholder=\"'.IntegerInputTestData::DEFAULT_LABEL.'\" \/>/',
                45, false, null, '', '', true)],
            [new IntegerInputTestData('',
                '/<input.* name=\"'.IntegerInputTestData::DEFAULT_KEY.'\[0\]\" id=\"'.IntegerInputTestData::DEFAULT_KEY.'-0\" value=\"45.37\".* \/>/',
                45, false, 0)],
            [new IntegerInputTestData('',
                '/<input.* value=\"45\".* class=\"my-special-class\" \/>/',
                45, false, null, '', 'my-special-class')],
        );
    }
}