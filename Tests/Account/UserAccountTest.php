<?php

namespace Littled\Tests\Account;


use GuzzleHttp\Client;
use Littled\Account\UserAccount;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Request\RequestInput;
use PHPUnit\Framework\TestCase;

class UserAccountTest extends TestCase
{
	/** @var string User name value that already exists in the site_user table. */
	const TEST_EXISTING_USER_NAME = 'video8';
	/** @var string Path to collect user account test harness page. */
	const TEST_HARNESS_COLLECT_PATH = 'tests/collect/account';

	/** @var UserAccount Test object. */
	public $obj;

	public function setUp(): void
	{
		parent::setUp();
		$this->obj = new UserAccount();
	}

	/**
	 * @param UserAccount $account UserAccount object containing data to send to collection test harness page.
	 * @return array Test harness response as json object.
	 */
	protected function sendCollectTestHarnessRequest($account)
	{
		$data = $account->arrayEncode();
		$client = new Client(['base_uri' => TEST_HARNESS_BASE_URI]);
		$response = $client->post(self::TEST_HARNESS_COLLECT_PATH, [
			'form_params' => $data
		]);
		return(json_decode($response->getBody()->getContents(), true));
	}


	public function testConstruct()
	{
		$obj = new UserAccount();
		self::assertNull($obj->id->value);
		self::assertEquals('', $obj->uname->value);
		self::assertEquals('', $obj->username->value);
		self::assertEquals('', $obj->uname->value);
		self::assertEquals('', $obj->password->value);
		self::assertEquals('', $obj->password_confirm->value);
		self::assertEquals(UserAccount::BASIC_AUTHENTICATION, $obj->access->value);
		self::assertFalse($obj->email_opt_in->value);
		self::assertFalse($obj->postal_opt_in->value);
		self::assertEquals('', $obj->contact_info->first_name->value);
		self::assertEquals('', $obj->contact_info->email->value);

		self::assertTrue($obj->password->isDatabaseField);
		self::assertFalse($obj->password_confirm->isDatabaseField);
		self::assertFalse($obj->contact_info->first_name->required);
		self::assertTrue($obj->contact_info->email->required);
		self::assertFalse($obj->contact_info->address1->required);
	}

	public function testAccountActivationURI ()
	{
		self::expectException(ConfigurationUndefinedException::class);
		$uri = UserAccount::getAccountActivationuri();

		UserAccount::setAccountActivationURI('https://foobar.com/biz/bash/');
		self::assertEquals('https://foobar.com/biz/bash', UserAccount::getAccountActivationuri());

		$obj = new UserAccount();
		self::assertEquals('https://foobar.com/biz/bash', $this->obj->getAccountActivationuri());
		self::assertEquals('https://foobar.com/biz/bash', $obj->getAccountActivationuri());
	}

	public function testCollectFromInput()
	{
		$result = $this->sendCollectTestHarnessRequest($this->obj);
		$data = $result['data'];
		self::assertNull($data[$this->obj->id->key]);
	}

	public function testContactEmail()
	{
		self::expectException(ConfigurationUndefinedException::class);
		$email = UserAccount::getContactEmail();

		UserAccount::setContactEmail('dbarchowsky@gmail.com');
		self::assertEquals('dbarchowsky@gmail.com', UserAccount::getContactEmail());

		$obj2 = new UserAccount();
		self::assertEquals('dbarchowsky@gmail.com', $obj2->getContactEmail());

		$obj2->setContactEmail('damienjay@gmail.com');
		self::assertEquals('damienjay@gmail.com', $obj2->getContactEmail());
		self::assertEquals('damienjay@gmail.com', $this->obj->getContactEmail());

		$obj2 = null;
		self::assertEquals('damienjay@gmail.com', $this->obj->getContactEmail());
	}

	public function testContactIDPointer()
	{
		self::assertNull($this->obj->contact_info->id->value);
		self::assertNull($this->obj->contact_id->value);

		$this->obj->contact_info->id->value = 45;
		self::assertEquals(45, $this->obj->contact_info->id->value);
		self::assertEquals(45, $this->obj->contact_id->value);
	}

	public function testHasDataDefaultValues()
	{
		self::assertFalse($this->obj->hasData());
	}

	public function testHasDataWithUserName()
	{
		$this->obj->username->value = 'username';
		self::assertTrue($this->obj->hasData());
	}

	public function testHasDataWithPassword()
	{
		$this->obj->password->value = 'secret';
		self::assertTrue($this->obj->hasData());
	}

	public function testHasDataWithRecordId()
	{
		$this->obj->id->value = 9999999;
		self::assertTrue($this->obj->hasData());
	}

	public function testHasDataWithNonEssentialProperty()
	{
		$this->obj->password_confirm->value = 'secret';
		self::assertFalse($this->obj->hasData());
	}

	public function testHasDataWithMultiplePropertyValues()
	{
		$this->obj->id->value = 989898;
		$this->obj->username->value = 'user1235';
		$this->obj->password->value = 'secret1';
		$this->obj->password_confirm->value = 'secret2';
		self::assertTrue($this->obj->hasData());
	}

	public function testRegistrationNoticeEmailTemplate ()
	{
		self::expectException(ConfigurationUndefinedException::class);
		$uri = UserAccount::getRegistrationNoticeEmailTemplate();

		UserAccount::setRegistrationNoticeEmailTemplate('/path/to/registration/template');
		self::assertEquals('/path/to/registration/template', UserAccount::getRegistrationNoticeEmailTemplate());

		$obj = new UserAccount();
		self::assertEquals('/path/to/registration/template', $this->obj->getRegistrationNoticeEmailTemplate());
		self::assertEquals('/path/to/registration/template', $obj->getRegistrationNoticeEmailTemplate());

		$obj->setRegistrationNoticeEmailTemplate('/new/path/to/template');
		self::assertEquals('/new/path/to/registration/template', $this->obj->getRegistrationNoticeEmailTemplate());
		self::assertEquals('/new/path/to/registration/template', UserAccount::getRegistrationNoticeEmailTemplate());
	}

	public function testTableName()
	{
		self::assertEquals('site_user', UserAccount::TABLE_NAME);
		self::assertEquals('site_user', UserAccount::TABLE_NAME());
	}

	public function testValidateUserNameDefaultValue()
	{
		try
		{
			$this->obj->validateUserName();
			self::assertEquals('Exception not thrown.', 'Exception not thrown.');
		}
		catch(ContentValidationException $ex)
		{
			self::assertEquals('', $ex->getMessage());
		}
	}

	public function testValidateUserNameValidUserName()
	{
		$this->obj->username->value = 'newuser';
		try
		{
			$this->obj->validateUserName();
			self::assertEquals('Exception not thrown.', 'Exception not thrown.');
		}
		catch(ContentValidationException $ex)
		{
			self::assertEquals('', $ex->getMessage());
		}
	}

	public function testValidateUserNameExistingUserName()
	{
		$this->obj->username->value = self::TEST_EXISTING_USER_NAME;
		try
		{
			$this->obj->validateUserName();
			self::assertEquals('', 'Exception not thrown.');
		}
		catch(ContentValidationException $ex)
		{
			self::assertEquals('User name already exists.', $ex->getMessage());
		}
	}
}