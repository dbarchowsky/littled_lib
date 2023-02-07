<?php

namespace Littled\Tests\DataProvider\Request;


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
            [new BooleanInputTestData('', '/^\s*<div.*>\s*<label for=\"'.BooleanInputTestData::DEFAULT_KEY.'\"><input type=\"checkbox\" name=\"'.BooleanInputTestData::DEFAULT_KEY.'\" id=\"'.BooleanInputTestData::DEFAULT_KEY.'\".* \/>\s*'.BooleanInputTestData::DEFAULT_LABEL.'<\/label>\s*/',
                '[use default]', false, false,
                '', '', '', '',
                'default values')],
            [new BooleanInputTestData('', '/^<div>\s*<label.*><input .*class=\"my-input-class\"/',
                '[use default]', false, false,
                '', 'my-input-class', '', '',
                'input css class set')],
            [new BooleanInputTestData('', '/^<div class=\"other-class\">\s*<label.*><input .*class=\"my-input-class\"/',
                '[use default]', false, false,
                '', 'my-input-class', '', 'other-class',
                'css class override set')],
            [new BooleanInputTestData('', '/^<div class=\"other-class form-error\">\s*<label.*><input .*class=\"my-class input-error\"/',
                '[use default]', false, true,
                '', 'my-class', '', 'other-class',
                'input css and override classes set; has_errors is TRUE')],
            [new BooleanInputTestData('', '/^\s*<div class=\"form-error\">\s*<label.*><input .*class=\"my-class input-error\"/',
                '[use default]', false, true,
                '', 'my-class', '', '',
                'input css class set; has_errors is TRUE')],
            [new BooleanInputTestData('', '/^\s*<div class=\"form-error\">\s*<label.*><input .*class=\"input-error\"/',
                '[use default]', false, true,
                '', '', '', '',
                'has_errors is TRUE; no css classes are set')],
            [new BooleanInputTestData('', '/^\s*<div.*>\s*<label.*><input.*>\s*'.BooleanInputTestData::DEFAULT_LABEL.' \(\*\)<\/label>\s*/',
                '[use default]', true, false,
                '', '', '', '',
                'required is TRUE')],
            [new BooleanInputTestData('', '/^<div>\s*<label.*><input type=\"checkbox\" .*\/> my custom label<\/label>\s*<\/div>\s*$/',
                '[use default]', false, false,
                'my custom label', '', '', '',
                'custom label')],
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