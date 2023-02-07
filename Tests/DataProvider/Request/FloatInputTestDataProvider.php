<?php

namespace Littled\Tests\DataProvider\Request;


class FloatInputTestDataProvider
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
			[2.9, 2.9],
			[0.0041, 0.0041],
			[0.0041, '0.0041'],
			[7692.0071, '7692.0071'],
		);
	}

    public static function escapeSQLTestProvider(): array
    {
        return array(
            [new FloatInputTestData('NULL','',  'default', '[use default]')],
            [new FloatInputTestData('NULL','', 'true as bool', true)],
            [new FloatInputTestData('NULL','', 'true as string', 'true')],
            [new FloatInputTestData('1','', '1 as string', '1')],
            [new FloatInputTestData('1','', '1 as int', 1)],
            [new FloatInputTestData('NULL','', 'false as bool', false)],
            [new FloatInputTestData('NULL','', 'false as string', 'false')],
            [new FloatInputTestData('0','', '0 as string', '0')],
            [new FloatInputTestData('0','', '0 as number', 0)],
            [new FloatInputTestData('45','', '45 as number', 45)],
            [new FloatInputTestData('56','', '56 as string', '56')],
            [new FloatInputTestData('3.005','', 'decimal as number', 3.005)],
            [new FloatInputTestData('3.07','', 'decimal as string', '3.07')],
            [new FloatInputTestData('NULL','', 'non-numeric string', 'foobar')],
        );
    }

	public static function saveInFormTestProvider(): array
	{
		return array(
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/',
                'NULL', null)],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/',
                '1 as number', 1)],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/',
                '1 as string', '1')],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/',
                '0 as number', 0)],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/',
                '0 as string', '0')],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/',
                'non-numeric string', 'foo')],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"0.45\" \/>/',
                'decimal as string', '0.45')],
			[new FloatInputTestData(null,
                '/<input type=\"hidden\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"24.84\" \/>/',
                'decimal as number', 24.84)],
		);
	}

	public static function setInputValueTestProvider(): array
	{
		return array(
			[new FloatInputTestData(null, '', 'default', '[use default]')],
			[new FloatInputTestData(null, '', 'true as bool', true)],
			[new FloatInputTestData(null, '', 'true as string', 'true')],
			[new FloatInputTestData(1, '', '1 as string', '1')],
			[new FloatInputTestData(1, '', '1 as number', 1)],
			[new FloatInputTestData(null, '', 'false as bool', false)],
			[new FloatInputTestData(null, '', 'false as string', 'false')],
			[new FloatInputTestData(0, '', '0 as string', '0')],
			[new FloatInputTestData(0, '', '0 as number', 0)],
			[new FloatInputTestData(45, '', '45 as number', 45)],
			[new FloatInputTestData(45, '', '45 as string', '45')],
			[new FloatInputTestData(32.7, '', 'decimal', 32.7)],
			[new FloatInputTestData(32.7, '', 'decimal as string', '32.7')],
			[new FloatInputTestData(null, '', 'non-numeric string', 'some arbitrary string')],
			[new FloatInputTestData(null, '', 'NULL', null)],
		);
	}

	public static function renderTestProvider(): array
	{
		return array(
			[new FloatInputTestData('',
                '/<div(.|\n)*?<label.*?>'.FloatInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input type=\"number\" step=\"0.01\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\".* \/>/',
                'default value',
                '[use default]')],
			[new FloatInputTestData('',
                '/<div class=\"form-cell\">(.|\n)*<input.*value=\"45.23\".* \/>/',
                'decimal',
                45.23)],
            [new FloatInputTestData('',
                '/<div class=\"form-cell\">(.|\n)*<input .*class=\"my-input-class\"/',
                'input class set',
                45.23, false, null,
                'my-input-class')],
            [new FloatInputTestData('',
                '/<div class=\"my-container-class\">(.|\n)*<input .*maxlength=\"[0-9]*\" \/><\/div>/',
                'container class set',
                45.23, false, null,
                '', 'my-container-class')],
            [new FloatInputTestData('',
                '/<div class=\"my-container-class\">(.|\n)*<input .*class=\"my-input-class\" \/><\/div>/',
                'input & container class set',
                45.23, false, null,
                'my-input-class', 'my-container-class')],
            [new FloatInputTestData('',
                '/<div class=\"my-container-class custom-class\">(.|\n)*<input .*class=\"my-input-class\" \/><\/div>/',
                'input & container & css override class set',
                45.23, false, null,
                'my-input-class', 'my-container-class', 'custom-class')],
		);
	}

    public static function renderInputTestProvider(): array
    {
        return array(
            [new FloatInputTestData('',
                '/<input type=\"number\" step=\"0.01\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\" id=\"'.FloatInputTestData::DEFAULT_KEY.'\" value=\"\" title=\"'.FloatInputTestData::DEFAULT_LABEL.'\" maxlength=\"50\" \/>/',
                'default value', '[use default]')],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* \/>/',
                'decimal', 45.37)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* \/>/',
                'decimal as string', '45.37')],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* title=\"my special label\".* \/>/',
                'label override',
                45.37, false, null, '', '', '', 'my special label')],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* title=\"my special label\".* placeholder=\"my special label\" \/>/',
                'display placeholder with custom label',
                45.37, false, null, '', '', '', 'my special label', true)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* title=\"'.FloatInputTestData::DEFAULT_LABEL.'\".* placeholder=\"'.FloatInputTestData::DEFAULT_LABEL.'\" \/>/',
                'display placeholder without custom label',
                45.37, false, null, '', '', '', '', true)],
            [new FloatInputTestData('',
                '/<input.* name=\"'.FloatInputTestData::DEFAULT_KEY.'\[0\]\" id=\"'.FloatInputTestData::DEFAULT_KEY.'-0\" value=\"45.37\".* \/>/',
                '0 index',
                45.37, false, 0)],
            [new FloatInputTestData('',
                '/<input.* value=\"45.37\".* class=\"my-input-class\" \/>/',
                'input class set',
                45.37, false, null, 'my-input-class')],
        );
    }
}