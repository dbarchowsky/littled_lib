<?php

namespace Littled\Tests\Request\DataProvider;


class FloatInputTestDataProvider
{
    public static function escapeSQLTestProvider(): array
    {
        return array(
            [new FloatInputTestData('NULL','', '[use default]')],
            [new FloatInputTestData('NULL','', true)],
            [new FloatInputTestData('NULL','', 'true')],
            [new FloatInputTestData('1','', '1')],
            [new FloatInputTestData('1','', 1)],
            [new FloatInputTestData('NULL','', false)],
            [new FloatInputTestData('NULL','', 'false')],
            [new FloatInputTestData('0','', '0')],
            [new FloatInputTestData('0','', 0)],
            [new FloatInputTestData('45','', 45)],
            [new FloatInputTestData('56','', '56')],
            [new FloatInputTestData('3.005','', 3.005)],
            [new FloatInputTestData('3.07','', '3.07')],
            [new FloatInputTestData('NULL','', 'foobar')],
        );
    }

	public static function saveInFormTestProvider(): array
	{
		return array(
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', null)],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', 1)],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', '1')],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', 0)],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', '0')],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', 'foo')],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"0.45\" \/>/', '0.45')],
			[new FloatInputTestData(null,'/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"24.84\" \/>/', '24.84')],
		);
	}

	public static function setInputValueTestProvider(): array
	{
		return array(
			[new FloatInputTestData(null, '', '[use default]')],
			[new FloatInputTestData(null, '', true)],
			[new FloatInputTestData(null, '', 'true')],
			[new FloatInputTestData(1, '', '1')],
			[new FloatInputTestData(1, '', 1)],
			[new FloatInputTestData(null, '', false)],
			[new FloatInputTestData(null, '', 'false')],
			[new FloatInputTestData(0, '', '0')],
			[new FloatInputTestData(0, '', 0)],
			[new FloatInputTestData(45, '', 45)],
			[new FloatInputTestData(45, '', '45')],
			[new FloatInputTestData(32.7, '', 32.7)],
			[new FloatInputTestData(32.7, '', '32.7')],
			[new FloatInputTestData(null, '', 'some arbitrary string')],
			[new FloatInputTestData(null, '', null)],
		);
	}

	public static function renderTestProvider(): array
	{
		return array(
			[new FloatInputTestData('', '/<div(.|\n)*?<label.*?>'.FloatInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input type=\"number\" step=\"0.01\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\".* \/>/', '[use default]')],
			[new FloatInputTestData('', '/<div class=\"form-cell\">(.|\n)*<input.*value=\"45.23\".* \/>/', 45.23)],
		);
	}

    public static function renderInputTestProvider(): array
    {
        return array(
            [new FloatInputTestData('',
                '/<input type=\"number\" step=\"0.01\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" id=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"\" title=\"'.FloatInputTestData::DEFAULT_LABEL.'\" maxlength=\"50\" \/>/',
                '[use default]')],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* \/>/',
                45.37)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* \/>/',
                45.37)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* title=\"my special label\".* \/>/',
                45.37, false, null, 'my special label')],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* title=\"my special label\".* placeholder=\"my special label\" \/>/',
                45.37, false, null, 'my special label', '', true)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* title=\"'.FloatInputTestData::DEFAULT_LABEL.'\".* placeholder=\"'.FloatInputTestData::DEFAULT_LABEL.'\" \/>/',
                45.37, false, null, '', '', true)],
            [new FloatInputTestData('',
                '/<input.* name=\"'.FloatInputTestData::DEFAULT_KEY.'\[0\]\" id=\"'.FloatInputTestData::DEFAULT_KEY.'-0\" value=\"45.37\".* \/>/',
                45.37, false, 0)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* class=\"my-special-class\" \/>/',
                45.37, false, null, '', 'my-special-class')],
        );
    }
}