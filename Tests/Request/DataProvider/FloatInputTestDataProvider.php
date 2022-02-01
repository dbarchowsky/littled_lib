<?php

namespace Littled\Tests\Request\DataProvider;


class FloatInputTestDataProvider
{
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
			[new FloatInputTestData('', '/<div(.|\n)*?<label.*?>'.FloatInputTestData::DEFAULT_LABEL.'<\/label>(.|\n)*<input type=\"text\" name=\"'.FloatInputTestData::DEFAULT_KEY.'\".* \/>/', '[use default]')],
			[new FloatInputTestData('', '/<div class=\"form-cell\">(.|\n)*<input.*value=\"45.23\".* \/>/', 45.23)],
		);
	}
}