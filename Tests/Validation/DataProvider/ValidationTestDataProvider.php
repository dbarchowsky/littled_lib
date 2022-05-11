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
		return array(
			array('No stored CSRF value; no CSRF data', false, [], [], null),
			array('No CSRF value in session',
				false, [], array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()), null),
			array(
				'Empty CSRF value in session',
				false,
				array(LittledGlobals::CSRF_SESSION_KEY => ''),
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken())
			),
			array(
				'Valid CSRF value in POST data',
				true,
				array(LittledGlobals::CSRF_TOKEN_KEY => AppBase::getCSRFToken()),
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				null),
			array(
				'Valid CSRF value in SESSION data',
				true,
				[],
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				(object)array(LittledGlobals::CSRF_TOKEN_KEY => AppBase::getCSRFToken())),
			array(
				'Invalid CSRF value in POST data',
				false,
				array(LittledGlobals::CSRF_TOKEN_KEY => 'bogus_value'),
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				null),
			array(
				'Invalid CSRF value in local data',
				false,
				[],
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				(object)array(LittledGlobals::CSRF_TOKEN_KEY => 'bogus_value')),
			array(
				'Empty token value in headers',
				false,
				[],
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				null,
				array($header_key => '')
			),
			array(
				'Invalid token value in headers',
				false,
				[],
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				null,
				array($header_key => 'bogus_value')
			),
			array(
				'Valid token in headers; No POST or local data',
				true,
				[],
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				null,
				array($header_key => AppBase::getCSRFToken())
			),
			array(
				'Valid token in headers; Invalid POST token',
				true,
				array(LittledGlobals::CSRF_TOKEN_KEY => 'BOGUS'),
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				null,
				array($header_key => AppBase::getCSRFToken())
			),
			array(
				'Valid token in headers; Invalid local token',
				false,
				[],
				array(LittledGlobals::CSRF_SESSION_KEY => AppBase::getCSRFToken()),
				(object)array(LittledGlobals::CSRF_TOKEN_KEY => 'BOGUS'),
				array($header_key => AppBase::getCSRFToken())
			),
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
}