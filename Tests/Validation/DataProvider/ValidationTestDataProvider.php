<?php

namespace Littled\Tests\Validation\DataProvider;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ContentValidationException;

class ValidationTestDataProvider
{
	public static function collectIntegerArrayRequestVarTestProvider(): array
	{
		return array(
			array([], 'null', null),
			array([], 'empty', []),
			array([44], 'single_int', 44),
			array([208], 'single_float', 208.04),
			array([5,6,8,0,34,-12], 'int_array', [5,6,8,0,34,-12]),
			array([208, 209, 210], 'float_array', [208.04, 208.51, 210.0]),
			array([], 'string', 'two'),
			array([5, 10, 22, 23, 0, -12, 4], 'mixed_array', [4.5, 'three', 10, 22, 22.6, 0, -12, 4]),
		);
	}

    public static function getClientIPTestProvider(): array
    {
        return array(
            [true, '66.214.248.106', 'REMOTE_ADDR', 'Valid REMOTE_ADDR ip'],
            [true, '66.214.248.106', 'HTTP_CLIENT_ID', 'Valid HTTP_CLIENT_ID ip'],
            [true, '66.214.248.106', 'HTTP_X_FORWARDED_FOR', 'Valid HTTP_X_FORWARDED_FOR ip'],
            [false, 'not an ip', 'REMOTE_ADDR', 'Invalid REMOTE_ADDR ip'],
            [false, 'not an ip', 'HTTP_CLIENT_ID', 'Invalid HTTP_CLIENT_ID ip'],
            [false, 'not an ip', 'HTTP_X_FORWARDED_FOR', 'Invalid HTTP_X_FORWARDED_FOR ip'],
            [false, 'not an ip', 'HTTP_X_FORWARDED_FOR', 'Invalid HTTP_X_FORWARDED_FOR ip'],
            [true, '66.214.248.106', 'HTTP_CLIENT_ID', 'HTTP_CLIENT_ID overrides REMOTE_ADDR', '66.214.248.106', 'REMOTE_ADDR'],
        );
    }

    public static function getClientLocationTestProvider(): array
    {
        $US_expected = array('geoplugin_countryCode' => 'US', 'is_eu' => false);
        $CA_expected = array('geoplugin_countryCode' => 'CA', 'is_eu' => false);
        $NL_expected = array('geoplugin_countryCode' => 'NL', 'is_eu' => true);
        $DE_expected = array('geoplugin_countryCode' => 'DE', 'is_eu' => true);
        $FR_expected = array('geoplugin_countryCode' => 'FR', 'is_eu' => true);
        $ES_expected = array('geoplugin_countryCode' => 'ES', 'is_eu' => true);
        $GB_expected = array('geoplugin_countryCode' => 'GB', 'is_eu' => false);
        $SG_expected = array('geoplugin_countryCode' => 'SG', 'is_eu' => false);
        $JP_expected = array('geoplugin_countryCode' => 'JP', 'is_eu' => false);
        $IN_expected = array('geoplugin_countryCode' => 'IN', 'is_eu' => false);
        $RU_expected = array('geoplugin_countryCode' => 'RU', 'is_eu' => false);
        return array(
            [$US_expected, '66.214.248.106', 'REMOTE_ADDR', 'Valid REMOTE_ADDR ip'],
            [$US_expected, '66.214.248.106', 'HTTP_CLIENT_ID', 'Valid HTTP_CLIENT_ID ip'],
            [$US_expected, '66.214.248.106', 'HTTP_X_FORWARDED_FOR', 'Valid HTTP_X_FORWARDED_FOR ip'],
            [$US_expected, '66.214.248.106', 'user', 'Valid local ip'],
            [null, 'not an ip', 'REMOTE_ADDR', 'Invalid REMOTE_ADDR ip'],
            [$US_expected, '66.214.248.106', 'HTTP_CLIENT_ID', 'HTTP_CLIENT_ID overrides REMOTE_ADDR', '176.31.84.249', 'REMOTE_ADDR'],
            [$CA_expected, '184.107.126.165', 'REMOTE_ADDR', 'Canada IP'],
            [$NL_expected, '95.142.107.181', 'REMOTE_ADDR', 'Netherlands IP'],
            [$DE_expected, '195.201.213.247', 'REMOTE_ADDR', 'Germany IP'],
            [$FR_expected, '176.31.84.249', 'REMOTE_ADDR', 'France IP'],
            [$ES_expected, '195.12.50.155', 'REMOTE_ADDR', 'Spain IP'],
            [$GB_expected, '5.152.197.179', 'REMOTE_ADDR', 'England IP'],
            [$SG_expected, '8.134.33.121', 'REMOTE_ADDR', 'China IP'],
            [$JP_expected, '110.50.243.6', 'REMOTE_ADDR', 'Japan IP'],
            [$IN_expected, '103.159.84.142', 'REMOTE_ADDR', 'India IP'],
            [$RU_expected, '95.31.18.119', 'REMOTE_ADDR', 'Russia IP'],
        );
    }

    public static function parseIntegerTestProvider(): array
    {
        return array(
            [1, 1, 'starting value: 1'],
            [0, 0, 'starting value: 0'],
            [-1, -1, 'starting value: -1'],
            [1, '1', 'starting value: "1"'],
            [0, '0', 'starting value: "0'],
            [-1, '-1', 'starting value: "-1"'],
            [null, '-', 'starting value: "-"'],
            [null, 'true', 'starting value: "true"'],
            [null, 'false', 'starting value: "false"'],
            [null, true, 'starting value: true'],
            [null, false, 'starting value: false'],
            [5, 4.5, 'starting value: 4.5'],
            [5, '4.5', 'starting value: "4.5"'],
            [4, 4.49, 'starting value: 4.49'],
            [5, 4.51, 'starting value: 4.51'],
            [null, null, 'starting value: null'],
            [null, 'funky chicken', 'starting value: "funky chicken"'],
        );
    }

    public static function isIntegerTestProvider(): array
    {
        return array(
            [true, 1],
            [true, 0],
            [true, -1],
            [true, '1'],
            [true, '0'],
            [true, '58'],
            [true, 87],
            [false, '-'],
            [false, 'true'],
            [false, 'false'],
            [false, true],
            [false, false],
            [false, 4.5],
            [false, '4.5'],
            [false, null],
        );
    }

	public static function isStringWithContentTestProvider(): array
	{
		return array(
			[false, null],
			[false, false],
			[false, true],
			[false, 0],
			[false, 1],
			[false, 435],
			[false, ''],
			[true, 'a'],
			[true, 'foo biz bar bash'],
			[true, 'null'],
			[true, 'false'],
			[true, 'true'],
			[true, '0'],
			[true, '1'],
			[true, '435'],
		);
	}

    public static function parseNumericTestProvider(): array
    {
        return array(
            [1, '1'],
            [0, '0'],
            [-1, '-1'],
            [5, '5'],
            [PHP_INT_MAX, ''.PHP_INT_MAX],
            [0.01, '0.01'],
            [4.5, '4.5'],
            [null, 'zero'],
            [null, 'j01'],
            [null, '01jx'],
            [null, '0x020'],
            [null, 'true'],
            [null, 'false'],
            [null, true],
            [null, false],
        );
    }

	public static function validateCSRFTestProvider(): array
	{
		$header_key = 'HTTP_'.LittledGlobals::CSRF_HEADER_KEY;
		$valid_header_data = array($header_key => AppBase::getCSRFToken(true));
		$valid_post_data = array(LittledGlobals::CSRF_TOKEN_KEY => AppBase::getCSRFToken(true));
		$valid_session_data = array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken(true));
		$valid_user_data = (object)array(LittledGlobals::CSRF_TOKEN_KEY => AppBase::getCSRFToken(true));
		$invalid_header_data = array($header_key => 'bogus value');
		$invalid_post_data = array(LittledGlobals::CSRF_TOKEN_KEY => 'bogus_value');
		$invalid_user_data = (object)array(LittledGlobals::CSRF_TOKEN_KEY => 'bogus value');
		return array(
			array('No stored CSRF value; no CSRF data', false, [], [], null),
			array('No CSRF value in session',
				false, [], $valid_session_data, null),
			array(
				'Empty CSRF value in POST data',
				false,
				array(LittledGlobals::CSRF_SESSION_KEY => ''),
				$valid_session_data),
			array(
				'Valid CSRF value in POST data (POST data is ignored)',
				false,
				$valid_post_data,
				$valid_session_data,
				null),
			array(
				'Valid CSRF value in user data',
				true,
				[],
				$valid_session_data,
				$valid_user_data),
			array(
				'Invalid CSRF value in POST data',
				false,
				$invalid_post_data,
				$valid_session_data,
				null),
			array(
				'Invalid CSRF value in local data',
				false,
				[],
				$valid_session_data,
				$invalid_user_data),
			array(
				'Empty token value in headers',
				false,
				[],
				$valid_session_data,
				null,
				array($header_key => '')),
			array(
				'Invalid token value in headers',
				false,
				[],
				$valid_session_data,
				null,
				$invalid_header_data),
			array(
				'Invalid token value in headers; Valid token in POST data. (POST data is ignored)',
				false,
				$valid_post_data,
				$valid_session_data,
				null,
				$invalid_header_data),
			array(
				'Valid token in headers; No POST or local data',
				true,
				[],
				$valid_session_data,
				null,
				$valid_header_data),
			array(
				'Valid token in headers; Invalid POST token',
				true,
				$invalid_post_data,
				$valid_session_data,
				null,
				$valid_header_data),
			array(
				'Valid token in headers; Invalid local token',
				false,
				[],
				$valid_session_data,
				$invalid_user_data,
				$valid_header_data)
		);
	}

	public static function validateDateStringTestProvider(): array
	{
		return array(
			array('2016-03-15', '', '2016-03-15', 'Y-m-d'),
			array('2016-03-15', '', '3/15/2016', 'n/j/Y'),
			array('2016-03-15', '', '03/15/2016', 'm/d/Y'),
			array('2016-03-02', '', '3/2/2016', 'n/j/Y'),
			array('1987-02-08', '', '02/08/1987', 'm/d/Y'),
			array('1987-02-08', '', '2/8/87', 'n/j/y'),
			array('1987-02-08', '', '02/08/87', 'm/d/y'),
			array('1987-02-08', '', 'February 08, 1987', 'F d, Y'),
			array('1987-02-08', '', 'February 8, 1987', 'F j, Y'),
			array('', ContentValidationException::class, 'February 08, 87', 'invalid date'),
		);
	}

    public static function stripTagsTestProvider(): array
    {
        return array(
            array('/^$/', '', '', [], 'no data'),
            array('/^this is the original source$/', 'p1', 'this is the original source', [], '', 'nothing to strip'),
            array('/^this is the original sourcediv content$/', 'p1', '<p>this is the original source</p><div>div content</div>', [], '', 'strip all tags'),
            array('/^<p>this is the original source<\/p>div content$/', 'p1', '<p>this is the original source</p><div>div content</div>', ['p'], '', 'p tag whitelisted'),
            array('/^this is the original source<div>div content<\/div>$/', 'p1', '<p>this is the original source</p><div>div content</div>', ['div'], '', 'div tag whitelisted'),
            array('/^<p>this is the original source<\/p>div content$/', 'p1', '<p>this is the original source</p><div>div content</div>', ['p'], 'POST', 'reading from POST data'),
            array('/^<p>this is the original source<\/p>div content$/', 'p1', '<p>this is the original source</p><div>div content</div>', ['p'], 'REQUEST', 'reading from REQUEST data'),
            array('/^<p>this is the original source<\/p><div>div content<\/div>$/', 'p1', '<p>this is the original source</p><im'. 'g src="/assets/images/image.jpg" alt="test" /><div>div content</div>', ['p', 'div'], '', 'strip img tag'),
            array('/^<p>this is the original source<\/p><img.*\/><div>div content<\/div>$/', 'p1', '<p>this is the original source</p><img src="/assets/images/image.jpg" alt="test" /><div>div content</div>', ['p', 'img', 'div'], '', 'allow img tag'),
            array('/^<p>this is the original source<\/p><img.*\/>alert.*\)<div>div content<\/div>$/', 'p1', '<p>this is the original source</p><img src="/assets/images/image.jpg" alt="test" /><script>alert("hello there!")</script><div>div content</div>', ['p', 'img', 'div'], '', 'strip script tag'),
        );
    }
}