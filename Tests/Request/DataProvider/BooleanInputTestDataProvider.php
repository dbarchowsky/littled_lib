<?php

namespace Littled\Tests\Request\DataProvider;


class BooleanInputTestDataProvider
{
	public static function escapeSQLProvider(): array
	{
		return array(
			[new BooleanInputTestData('NULL', '', '[use default]')],
			[new BooleanInputTestData('1', '', true)],
			[new BooleanInputTestData('1', '', 'true')],
			[new BooleanInputTestData('1', '', '1')],
			[new BooleanInputTestData('1', '', 1)],
			[new BooleanInputTestData('0', '', false)],
			[new BooleanInputTestData('0', '', 'false')],
			[new BooleanInputTestData('0', '', 0)],
			[new BooleanInputTestData('0', '', '0')],
			[new BooleanInputTestData('NULL', '', 45)],
			[new BooleanInputTestData('NULL', '', 1.005)],
			[new BooleanInputTestData('NULL', '', 'foobar')],
		);
	}

	public static function formatValueMarkupProvider(): array
	{
		return array(
			[new BooleanInputTestData('', '', '[use default]')],
			[new BooleanInputTestData('1', '', 1)],
			[new BooleanInputTestData('1', '', true)],
			[new BooleanInputTestData('1', '', 'on')],
			[new BooleanInputTestData('0', '', 0)],
			[new BooleanInputTestData('0', '', false)],
			[new BooleanInputTestData('0', '', 'off')],
			[new BooleanInputTestData('', '', null)],
		);
	}

    public static function renderTestProvider(): array
    {
        return array(
            [new BooleanInputTestData('', '/^\s*<div.*>\s*<label for=\"'.BooleanInputTestData::DEFAULT_KEY.'\"><input type=\"checkbox\" name=\"'.BooleanInputTestData::DEFAULT_KEY.'\" id=\"'.BooleanInputTestData::DEFAULT_KEY.'\".* \/>\s*'.BooleanInputTestData::DEFAULT_LABEL.'<\/label>\s*/', '[use default]')],
            [new BooleanInputTestData('', '/^\s*<div class=\"my-class\"/', '[use default]', false, false, '', 'my-class')],
            [new BooleanInputTestData('', '/^\s*<div class=\"my-class other-class\"/', '[use default]', false, false, '', 'my-class', 'other-class')],
            [new BooleanInputTestData('', '/^\s*<div class=\"my-class other-class input-error\"/', '[use default]', false, true, '', 'my-class', 'other-class')],
            [new BooleanInputTestData('', '/^\s*<div class=\"my-class input-error\"/', '[use default]', false, true, '', 'my-class')],
            [new BooleanInputTestData('', '/^\s*<div class=\"input-error\"/', '[use default]', false, true)],
            [new BooleanInputTestData('', '/^\s*<div.*>\s*<label.*><input.*>\s*'.BooleanInputTestData::DEFAULT_LABEL.' \(\*\)<\/label>\s*/', '[use default]', true)],
        );
    }

	public static function saveInFormProvider(): array
	{
		return array(
			[new BooleanInputTestData(null,'/<input type=\"hidden\" name=\"'.BooleanInputTestData::DEFAULT_KEY.'\" value=\"\" \/>/', null)],
			[new BooleanInputTestData(null,'/<input type=\"hidden\" name=\"'.BooleanInputTestData::DEFAULT_KEY.'\" value=\"1\" \/>/', true)],
			[new BooleanInputTestData(null,'/<input type=\"hidden\" name=\"'.BooleanInputTestData::DEFAULT_KEY.'\" value=\"0\" \/>/', false)],
		);
	}

	public static function setInputValueProvider(): array
	{
		return array(
			[new BooleanInputTestData(null, '', '[use default]')],
			[new BooleanInputTestData(true, '', true)],
			[new BooleanInputTestData(true, '', 'true')],
			[new BooleanInputTestData(true, '', '1')],
			[new BooleanInputTestData(true, '', 1)],
			[new BooleanInputTestData(false, '', false)],
			[new BooleanInputTestData(false, '', 'false')],
			[new BooleanInputTestData(false, '', '0')],
			[new BooleanInputTestData(false, '', 0)],
			[new BooleanInputTestData(null, '', 45)],
			[new BooleanInputTestData(null, '', 32.7)],
			[new BooleanInputTestData(null, '', 'some arbitrary string')],
		);
	}

	public static function setValidateProvider(): array
	{
		return array(
			[new BooleanInputTestData(false, '', true, false)],
			[new BooleanInputTestData(false, '', false, false)],
			[new BooleanInputTestData(false, '', null, false)],
			[new BooleanInputTestData(true, '', 'some string value', false)],
			[new BooleanInputTestData(true, '', '0', false)],
			[new BooleanInputTestData(true, '', 'true', false)],
			[new BooleanInputTestData(false, '', true, true)],
			[new BooleanInputTestData(false, '', false, true)],
		);
	}
}