<?php

namespace LittledTests\Account;


use Littled\Account\LoginAuthenticator;
use PHPUnit\Framework\TestCase;

class LoginAuthenticatorTest extends TestCase
{
	/** @var string Username value that already exists in the site_user table. */
	const TEST_EXISTING_USER_NAME = 'video8';
	/** @var string Path to collect user account test harness page. */
	const TEST_HARNESS_COLLECT_PATH = 'Tests/collect/account';

	/** @var LoginAuthenticator Test object. */
	protected LoginAuthenticator $obj;
	protected static string $test_uri;

	public function setUp(): void
	{
		parent::setUp();
		$this->obj = new LoginAuthenticator();
		static::$test_uri = '/path/to/login.php';
	}

	public function testSetLoginURI()
	{
		$this->assertEmpty($this->obj->getLoginURI());
		$this->obj->setLoginURI(static::$test_uri);
		self::assertEquals(static::$test_uri, $this->obj->getLoginURI());
	}

	public function testGetLoginURI()
	{
		$new_uri = '/new/path/to/login.php';
		$invalid_uri = '/path/to/some/other/login.php';

		/* static property set previously in testSetLoginURI() */
		self::assertEquals(static::$test_uri, $this->obj->getLoginURI());

		$this->obj->setLoginURI($new_uri);
		self::assertNotEquals('', $this->obj->getLoginURI());
		self::assertNotEquals($invalid_uri, $this->obj->getLoginURI());
		self::assertEquals($new_uri, $this->obj->getLoginURI());
	}
}