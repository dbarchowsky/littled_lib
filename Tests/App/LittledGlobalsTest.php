<?php

namespace Littled\Tests\App;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use PHPUnit\Framework\TestCase;

class LittledGlobalsTest extends TestCase
{
	public function testAppDomain()
	{
		self::assertEquals('', LittledGlobals::getAppDomain());

		LittledGlobals::setAppDomain('damienjay.com');
		self::assertEquals('damienjay.com', LittledGlobals::getAppDomain());

		LittledGlobals::setAppDomain();
		self::assertEquals('', LittledGlobals::getAppDomain());
	}

	public function testCmsRootUri()
	{
		self::assertEquals('', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('');
		self::assertEquals('', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('https://www.foobar.com/noendingslash');
		self::assertEquals('https://www.foobar.com/noendingslash/', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('https://www.foobar.com/subdir/');
		self::assertEquals('https://www.foobar.com/subdir/', LittledGlobals::getCMSRootURI());

		LittledGlobals::setCMSRootURI('');
		self::assertEquals('', LittledGlobals::getCMSRootURI());
	}

	public function testMySQLKeysPath()
	{
		self::assertEquals('', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeyspath('');
		self::assertEquals('', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeyspath('/path/to/mysql/no/terminating/slash');
		self::assertEquals('/path/to/mysql/no/terminating/slash/', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeyspath('/path/to/mysql/keys/');
		self::assertEquals('/path/to/mysql/keys/', LittledGlobals::getMySQLKeysPath());

		LittledGlobals::setMySQLKeyspath('');
		self::assertEquals('', LittledGlobals::getMySQLKeysPath());
	}

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     */
    function testTemplatePathDefault()
    {
        // run this test before making any other assignments to LittledGlobals::$template_path
        $this->expectException(ConfigurationUndefinedException::class);
        LittledGlobals::getTemplatePath();
    }

    /**
	 * @throws ConfigurationUndefinedException
	 */
	public function testTemplatePath()
	{
		LittledGlobals::setTemplatePath('/path/to/templates/no/terminating/slash');
		self::assertEquals('/path/to/templates/no/terminating/slash/', LittledGlobals::getTemplatePath());

		LittledGlobals::setTemplatePath('/path/to/templates/');
		self::assertEquals('/path/to/templates/', LittledGlobals::getTemplatePath());
	}

    function testTemplatePathWhenEmpty()
    {
        LittledGlobals::setTemplatePath('');
        $this->expectException(ConfigurationUndefinedException::class);
        LittledGlobals::getTemplatePath();
    }
}