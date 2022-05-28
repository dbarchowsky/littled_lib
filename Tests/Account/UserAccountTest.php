<?php
namespace Littled\Tests\Account;

use Littled\Tests\Account\TestHarness\UserAccountTestHarness;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Littled\Account\UserAccount;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Tests\Account\DataProvider\UserAccountTestData;
use PHPUnit\Framework\TestCase;

class UserAccountTest extends TestCase
{
	/** @var string Username value that already exists in the site_user table. */
	const TEST_EXISTING_USER_NAME = 'video8';
	/** @var string Path to collect user account test harness page. */
	const TEST_HARNESS_COLLECT_PATH = 'Tests/collect/account';

	/** @var UserAccount Test object. */
	public UserAccount $obj;

	public function setUp(): void
	{
		parent::setUp();
		$this->obj = new UserAccountTestHarness();
	}

	/**
	 * @param UserAccount $account UserAccount object containing data to send to collection test harness page.
	 * @return array Test harness response as json object.
	 * @throws GuzzleException
	 */
	protected function sendCollectTestHarnessRequest(UserAccount $account): array
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
		$obj = new UserAccountTestHarness();
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

		self::assertTrue($obj->password->is_database_field);
		self::assertFalse($obj->password_confirm->is_database_field);
		self::assertFalse($obj->contact_info->first_name->required);
		self::assertTrue($obj->contact_info->email->required);
		self::assertFalse($obj->contact_info->address1->required);
	}

	public function testAccountActivationURI ()
	{
		self::expectException(ConfigurationUndefinedException::class);
		UserAccount::getAccountActivationuri();

		UserAccount::setAccountActivationURI('https://foobar.com/biz/bash/');
		self::assertEquals('https://foobar.com/biz/bash', UserAccount::getAccountActivationuri());

		$obj = new UserAccountTestHarness();
		self::assertEquals('https://foobar.com/biz/bash', $this->obj->getAccountActivationuri());
		self::assertEquals('https://foobar.com/biz/bash', $obj->getAccountActivationuri());
	}

	/**
	 * @throws GuzzleException
	 */
	public function __disabled_testCollectFromInput()
	{
		$result = $this->sendCollectTestHarnessRequest($this->obj);
		$data = $result['data'];
		self::assertNull($data[$this->obj->id->key]);
	}

	public function testContactEmail()
	{
		self::expectException(ConfigurationUndefinedException::class);
		UserAccount::getContactEmail();

		UserAccount::setContactEmail('dbarchowsky@gmail.com');
		self::assertEquals('dbarchowsky@gmail.com', UserAccount::getContactEmail());

		$obj2 = new UserAccountTestHarness();
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

	/**
	 * @dataProvider \Littled\Tests\Account\DataProvider\UserAccountDataProvider::hasDataTestProvider()
	 * @return void
	 */
	public function testHasData(UserAccountTestData $data)
	{
		if ($data->expected===true) {
			$this->assertTrue($data->obj->hasData(), $data->msg);
		}
		else {
			$this->assertFalse($data->obj->hasData(), $data->msg);
		}
	}

	public function testHasDataDefaultValues()
	{
		self::assertFalse($this->obj->hasData());
	}

	/**
	 * @throws ResourceNotFoundException
	 */
	public function testRegistrationNoticeEmailTemplate ()
	{
		self::expectException(ConfigurationUndefinedException::class);
		UserAccount::getRegistrationNoticeEmailTemplate();

		UserAccount::setRegistrationNoticeEmailTemplate('/path/to/registration/template');
		self::assertEquals('/path/to/registration/template', UserAccount::getRegistrationNoticeEmailTemplate());

		$obj = new UserAccountTestHarness();
		self::assertEquals('/path/to/registration/template', $this->obj->getRegistrationNoticeEmailTemplate());
		self::assertEquals('/path/to/registration/template', $obj->getRegistrationNoticeEmailTemplate());

		$obj->setRegistrationNoticeEmailTemplate('/new/path/to/template');
		self::assertEquals('/new/path/to/registration/template', $this->obj->getRegistrationNoticeEmailTemplate());
		self::assertEquals('/new/path/to/registration/template', UserAccount::getRegistrationNoticeEmailTemplate());
	}

	/**
	 * @throws NotImplementedException
	 */
	public function testTableName()
	{
		self::assertEquals('site_user', UserAccount::getTableName());
	}
}