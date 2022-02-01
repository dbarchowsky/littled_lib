<?php

namespace Littled\Tests\Request\DataProvider;


class StringInputTestDataProvider
{
    public static function escapeSQLTestProvider(): array
    {
        return array(
            [new StringInputTestData('NULL','', '[use default]')],
            [new StringInputTestData('NULL','', true)],
            [new StringInputTestData('NULL','', 'true')],
            [new StringInputTestData('1','', '1')],
            [new StringInputTestData('1','', 1)],
            [new StringInputTestData('NULL','', false)],
            [new StringInputTestData('NULL','', 'false')],
            [new StringInputTestData('0','', '0')],
            [new StringInputTestData('0','', 0)],
            [new StringInputTestData('45','', 45)],
            [new StringInputTestData('56','', '56')],
            [new StringInputTestData('3.005','', 3.005)],
            [new StringInputTestData('3.07','', '3.07')],
            [new StringInputTestData('NULL','', 'foobar')],
        );
    }

	public static function saveInFormTestProvider(): array
	{
		return array(
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', null)],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', 1)],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', '1')],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', 0)],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', '0')],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', 'foo')],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"0.45\" \/>/', '0.45')],
			[new StringInputTestData(null,'/<input type=\"hidden\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"24.84\" \/>/', '24.84')],
		);
	}

	public static function setInputValueTestProvider(): array
	{
		return array(
			[new StringInputTestData('', '', '')],
			[new StringInputTestData('test value', '', 'test value')],
			[new StringInputTestData('5643', '', 5643)],
			[new StringInputTestData('', '', null)],
            [new StringInputTestData('873.85', '', 873.85)],
		);
	}

	public static function renderTestProvider(): array
	{
		return array(
			[new StringInputTestData('', '/<div(.|\n)*?<label for=\"'.StringInputTestData::DEFAULT_KEY.'\">'.StringInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input type=\"text\" name=\"'.StringInputTestData::DEFAULT_KEY.'\".* \/>/', '[use default]')],
			[new StringInputTestData('', '/<div class=\"form-cell\">(.|\n)*<input.*value=\"my test value\".* \/>/', 'my test value')],
            [new StringInputTestData('', '/<div class=\"form-cell\">(.|\n)*<label.*>My Special Label<\/label>(.|\n)*<input.*value=\"my test value\"/', 'my test value', false, null, 'My Special Label')],
            [new StringInputTestData('', '/<div class=\"my-special-class\">(.|\n)*<label.*>'.StringInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input.*value=\"my test value\"/', 'my test value', false, null, '', 'my-special-class')],
            [new StringInputTestData('', '/<div(.|\n)*<input.* name=\"'.StringInputTestData::DEFAULT_KEY.'\" id=\"'.StringInputTestData::DEFAULT_KEY.'\".* value=\"my test value\".* \/>/', 'my test value')],
            [new StringInputTestData('', '/<div(.|\n)*<label for=\"'.StringInputTestData::DEFAULT_KEY.'-5\"(.|\n)*<input.* name=\"'.StringInputTestData::DEFAULT_KEY.'\[5\]\" id=\"'.StringInputTestData::DEFAULT_KEY.'-5\".* value=\"my test value\".* \/>/', 'my test value', false, 5)],
            [new StringInputTestData('', '/<label for=\"'.StringInputTestData::DEFAULT_KEY.'-0\"(.|\n)*<input.* name=\"'.StringInputTestData::DEFAULT_KEY.'\[0\]\" id=\"'.StringInputTestData::DEFAULT_KEY.'-0\"/', 'my test value', false, 0)],
		);
	}

    public static function renderInputTestProvider(): array
    {
        return array(
            [new StringInputTestData('',
                '/<input type=\"text\" name=\"'.StringInputTestData::DEFAULT_KEY.'\" id=\"'.StringInputTestData::DEFAULT_KEY.'\" value=\"\" title=\"'.StringInputTestData::DEFAULT_LABEL.'\" maxlength=\"50\" \/>/',
                '[use default]')],
            [new StringInputTestData('',
                '/<input.* value=\"hello honey!\".* \/>/',
                'hello honey!')],
            [new StringInputTestData('',
                '/<input.* value=\"what up!\".* \/>/',
                'what up!')],
            [new StringInputTestData('',
                '/<input.* value=\"yo yo\. yo, yo!\".* title=\"my special label\".* \/>/',
                'yo yo. yo, yo!', false, null, 'my special label')],
            [new StringInputTestData('',
                '/<input.* value=\"foo bar\".* title=\"my special label\".* placeholder=\"my special label\" \/>/',
                'foo bar', false, null, 'my special label', '', true)],
            [new StringInputTestData('',
                '/<input.* value=\"foo bar\".* title=\"'.StringInputTestData::DEFAULT_LABEL.'\".* placeholder=\"'.StringInputTestData::DEFAULT_LABEL.'\" \/>/',
                'foo bar', false, null, '', '', true)],
            [new StringInputTestData('',
                '/<input.* name=\"'.StringInputTestData::DEFAULT_KEY.'\[0\]\" id=\"'.StringInputTestData::DEFAULT_KEY.'-0\" value=\"foo bar\".* \/>/',
                'foo bar', false, 0)],
            [new StringInputTestData('',
                '/<input.* value=\"foo bar\".* class=\"my-special-class\" \/>/',
                'foo bar', false, null, '', 'my-special-class')],
            [new StringInputTestData('',
                '/<input.* value=\"ampersand&amp;&lt;script&gt;\".* \/>/',
                'ampersand&<script>')],
        );
    }
}