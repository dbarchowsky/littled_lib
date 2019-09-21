<?php

namespace Littled\tests\App;


use Littled\App\LittledGlobals;
use PHPUnit\Framework\TestCase;

class LittledGlobalsTest extends TestCase
{
	public function testCmsRootUri()
	{
		self::assertEquals('', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('');
		self::assertEquals('', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('https://www.foobar.com/noendingslash');
		self::assertEquals('https://www.foobar.com/noendingslash/', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('https://www.foobar.com/subdir/');
		self::assertEquals('https://www.foobar.com/subdir/', LittledGlobals::getCMSRootURI());
	}

	public function testMySQLKeysPath()
	{
		self::assertEquals('', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeysPath('');
		self::assertEquals('', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeysPath('/path/to/mysql/no/terminating/slash');
		self::assertEquals('/path/to/mysql/no/terminating/slash/', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeysPath('/path/to/mysql/keys/');
		self::assertEquals('/path/to/mysql/keys/', LittledGlobals::getMySQLKeysPath());
	}

	public function testTemplatePath()
	{
		self::assertEquals('', LittledGlobals::getTemplatePath());

		LittledGlobals::setTemplatePath('');
		self::assertEquals('', LittledGlobals::getTemplatePath());

		LittledGlobals::setTemplatePath('/path/to/templates/no/terminating/slash');
		self::assertEquals('/path/to/templates/no/terminating/slash/', LittledGlobals::getTemplatePath());

		LittledGlobals::setTemplatePath('/path/to/templates/');
		self::assertEquals('/path/to/templates/', LittledGlobals::getTemplatePath());
	}
}