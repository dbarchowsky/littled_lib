<?php
namespace Littled\Tests\Account;

<<<<<<< HEAD
require_once(realpath(dirname(__FILE__)) . "/../../keys/littledamien/keys.php");

use Littled\Account\Address;
use Littled\Exception\RecordNotFoundException;
=======
use Littled\Account\Address;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\RecordNotFoundException;
use Littled\Validation\Validation;
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Class AddressTest
 * Unit tests for Littled\Account\Address
 * @package Littled\Tests\Account
 */
class AddressTest extends TestCase
{
    const TEST_ID_VALUE = 8;
    const TEST_NONEXISTENT_ID_VALUE = 9999999;
    const TEST_ADDR2_SIZE = 100;
<<<<<<< HEAD
=======
    const TEST_EMAIL = 'dbarchowsky@gmail.com';
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
    const TEST_URL_SIZE = 255;
    const TEST_LAST_NAME_VALUE = 'Schutz';
    const TEST_STATE_VALUE = 'Oregon';
    const TEST_STATE_ABBREV_VALUE = 'OR';
<<<<<<< HEAD
=======
    const TEST_STATE_ID = 9; /* California */
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe

    /** @var $addr Address */
    public $addr;

<<<<<<< HEAD
    /**
=======
    public function setUp(): void
    {
	    parent::setUp();
	    $this->addr = new Address();
    }

	/**
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
     * @throws RecordNotFoundException
     * @throws \Littled\Exception\ConfigurationUndefinedException
     * @throws \Littled\Exception\ConnectionException
     * @throws \Littled\Exception\ContentValidationException
     * @throws \Littled\Exception\InvalidQueryException
     * @throws \Littled\Exception\InvalidTypeException
     * @throws \Littled\Exception\NotImplementedException
     * @throws Exception
     */
    public function fetchTestRecord()
    {
<<<<<<< HEAD
        $this->addr = new Address();
=======
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
        $this->addr->id->value = AddressTest::TEST_ID_VALUE;
        $this->addr->read();
    }

    public function testCheckForDuplicate()
    {
        $this->fetchTestRecord();

        self::assertTrue($this->addr->checkForDuplicate());

        $saved_value = $this->addr->location->value;
        $this->addr->location->value .= "x";
        self::assertFalse($this->addr->checkForDuplicate());

        $this->addr->location->value = $saved_value;
        $saved_value = $this->addr->address1->value;
        $this->addr->address1->value = " ".$this->addr->address1->value;
        self::assertFalse($this->addr->checkForDuplicate());

        $this->addr->address1->value = $saved_value;
        $saved_value = $this->addr->zip->value;
        $this->addr->zip->value = substr($this->addr->zip->value, 0, -1);
        self::assertFalse($this->addr->checkForDuplicate());
    }

<<<<<<< HEAD
=======
    public function testFormatAddress()
    {
    	$this->fetchTestRecord();
    	self::assertEquals($this->addr->formatOneLineAddress(), $this->addr->formatAddress());
	    self::assertEquals($this->addr->formatOneLineAddress(), $this->addr->formatAddress('oneline'));
	    self::assertEquals($this->addr->formatGoogleAddress(), $this->addr->formatAddress('google'));
	    self::assertEquals($this->addr->formatHTMLAddress(), $this->addr->formatAddress('html'));
	    self::assertEquals($this->addr->formatHTMLAddress(true), $this->addr->formatAddress('html', true));
    }

    public function testFormatCity()
    {
	    $this->addr->city->value = "Baltimore";
	    self::assertEquals("{$this->addr->city->value}", $this->addr->formatCity());

	    $this->addr->state = "Maryland";
	    self::assertEquals("{$this->addr->city->value}, {$this->addr->state}", $this->addr->formatCity());

	    $this->addr->country->value = "USA";
	    $expected = "{$this->addr->city->value}, {$this->addr->state}, {$this->addr->country->value}";
	    self::assertEquals($expected, $this->addr->formatCity());

	    $this->addr->state_abbrev = "MD";
	    $expected = "{$this->addr->city->value}, {$this->addr->state_abbrev}, {$this->addr->country->value}";
	    self::assertEquals($expected, $this->addr->formatCity());

	    $this->addr->city->value = "Paris";
	    $this->addr->state = "";
	    $this->addr->state_abbrev = "";
	    $this->addr->country->value = "FRANCE";
	    $expected = "{$this->addr->city->value}, {$this->addr->country->value}";
	    self::assertEquals($expected, $this->addr->formatCity());

	    $this->addr->zip->value = "DQF123";
	    $expected = "{$this->addr->city->value}, {$this->addr->country->value} {$this->addr->zip->value}";
	    self::assertEquals($expected, $this->addr->formatCity());

		$this->addr->zip->value = '';
		$this->addr->city->value = '';
	    self::assertEquals($this->addr->country->value, $this->addr->formatCity());

	    $this->addr->state = 'Oklahoma';
	    $this->addr->country->value = '';
	    self::assertEquals($this->addr->state, $this->addr->formatCity());

	    $this->addr->state_abbrev = 'OK';
	    self::assertEquals($this->addr->state_abbrev, $this->addr->formatCity());
    }

    public function testFormatCityWithEmptyValues()
    {
	    self::assertEquals('', $this->addr->formatCity());

    	$this->addr->city->value = null;
	    $this->addr->state = null;
	    $this->addr->country->value = null;
	    $this->addr->zip->value = null;
	    self::assertEquals('', $this->addr->formatCity());

	    $this->addr->city->value = '';
	    $this->addr->state = '';
	    $this->addr->country->value = '';
	    $this->addr->zip->value = '';
	    self::assertEquals('', $this->addr->formatCity());

	    $this->addr->city->value = ' ';
	    $this->addr->state = ' ';
	    $this->addr->country->value = ' ';
	    $this->addr->zip->value = ' ';
	    self::assertEquals('', $this->addr->formatCity());
    }

>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
    public function testFormatOneLineAddress()
    {
        $this->fetchTestRecord();
        self::assertEquals('10956 SE Main Street, Milwaukie, OR 97222', $this->addr->formatOneLineAddress());

        $this->addr->state_abbrev = '';
        self::assertEquals('10956 SE Main Street, Milwaukie, Oregon 97222', $this->addr->formatOneLineAddress());

        $this->addr->state = '';
        self::assertEquals('10956 SE Main Street, Milwaukie 97222', $this->addr->formatOneLineAddress());

        $this->addr->zip->value = '';
        self::assertEquals('10956 SE Main Street, Milwaukie', $this->addr->formatOneLineAddress());

        $this->addr->city->value = '';
        $this->addr->zip->value = '99999';
        self::assertEquals('10956 SE Main Street 99999', $this->addr->formatOneLineAddress());

        $this->addr->state = null;
        $this->addr->city->value = null;
        self::assertEquals('10956 SE Main Street 99999', $this->addr->formatOneLineAddress());

        $this->addr->address1->value = '';
        $this->addr->city->value = 'City';
        $this->addr->state_abbrev = 'ST';
        self::assertEquals('City, ST 99999', $this->addr->formatOneLineAddress());

        $this->addr->address1->value = '123 Some Lane';
        $this->addr->city->value = 'London';
        $this->addr->state = '';
        $this->addr->state_abbrev = '';
        $this->addr->country->value = 'UK';
<<<<<<< HEAD
        self::assertEquals('123 Some Lane, London, UK', $this->addr->formatOneLineAddress());

        $this->addr->city->value = '';
=======
        self::assertEquals('123 Some Lane, London, UK 99999', $this->addr->formatOneLineAddress());

        $this->addr->city->value = '';
        $this->addr->zip->value = '';
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
        self::assertEquals('123 Some Lane, UK', $this->addr->formatOneLineAddress());
    }

    public function testFormatGoogleAddress()
    {
        $this->fetchTestRecord();
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukie%2C+OR+97222', $this->addr->formatGoogleAddress());

        $this->addr->state_abbrev = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukie%2C+Oregon+97222', $this->addr->formatGoogleAddress());

        $this->addr->state = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukie+97222', $this->addr->formatGoogleAddress());

        $this->addr->zip->value = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukie', $this->addr->formatGoogleAddress());

        $this->addr->city->value = '';
        $this->addr->zip->value = '99999';
        self::assertEquals('10956+SE+Main+Street+99999', $this->addr->formatGoogleAddress());

        $this->addr->state = null;
        $this->addr->city->value = null;
        self::assertEquals('10956+SE+Main+Street+99999', $this->addr->formatGoogleAddress());

        $this->addr->address1->value = '';
        $this->addr->city->value = 'City';
        $this->addr->state_abbrev = 'ST';
        self::assertEquals('City%2C+ST+99999', $this->addr->formatGoogleAddress());
    }

<<<<<<< HEAD
    public function testInitialValues()
    {
        $addr = new Address();
        self::assertInstanceOf('Littled\Request\IntegerInput', $addr->id);
        self::assertInstanceOf('Littled\Request\StringTextField', $addr->province);
        self::assertEquals(AddressTest::TEST_ADDR2_SIZE, $addr->address2->sizeLimit);
        self::assertEquals(AddressTest::TEST_URL_SIZE, $addr->url->sizeLimit);
=======
    public function testFormatHTMLAddressEmptyValues()
    {
    	self::assertEquals('', $this->addr->formatHTMLAddress(true));

    	$this->addr->firstname->value = null;
	    $this->addr->company->value = null;
	    $this->addr->address1->value = null;
	    $this->addr->city->value = null;
	    self::assertEquals('', $this->addr->formatHTMLAddress(true));

	    $this->addr->firstname->value = '';
	    $this->addr->company->value = '';
	    $this->addr->address1->value = '';
	    $this->addr->city->value = '';
	    self::assertEquals('', $this->addr->formatHTMLAddress(true));

	    $this->addr->firstname->value = ' ';
	    $this->addr->company->value = ' ';
	    $this->addr->address1->value = ' ';
	    $this->addr->city->value = ' ';
	    self::assertEquals('', $this->addr->formatHTMLAddress(true));
    }

    public function testFormatHTMLAddressIncludeName()
    {
	    $this->fetchTestRecord();

	    $expected = "<div>{$this->addr->firstname->value} {$this->addr->lastname->value}</div>\n".
		    "<div>{$this->addr->address1->value}</div>\n".
		    "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

	    $this->addr->address2->value = "Building 2";
	    $expected = "<div>{$this->addr->firstname->value} {$this->addr->lastname->value}</div>\n".
		    "<div>{$this->addr->address1->value}</div>\n".
		    "<div>{$this->addr->address2->value}</div>\n".
		    "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

	    $this->addr->company->value = "Puckanerry Inc";
	    $expected = "<div>{$this->addr->firstname->value} {$this->addr->lastname->value}</div>\n".
		    "<div>{$this->addr->company->value}</div>\n".
		    "<div>{$this->addr->address1->value}</div>\n".
		    "<div>{$this->addr->address2->value}</div>\n".
		    "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

	    $this->addr->address1->value = "";
	    $this->addr->address2->value = "";
	    $expected = "<div>{$this->addr->firstname->value} {$this->addr->lastname->value}</div>\n".
		    "<div>{$this->addr->company->value}</div>\n".
		    "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

	    $this->addr->firstname->value = "";
	    $this->addr->lastname->value = "";
	    $expected = "<div>{$this->addr->company->value}</div>\n".
		    "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

	    $this->addr->company->value = "";
	    $expected = "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

	    $this->addr->address1->value = "122 N Rose St";
	    $expected = "<div>{$this->addr->address1->value}</div>\n".
		    "<div>{$this->addr->city->value}, {$this->addr->state_abbrev} {$this->addr->zip->value}</div>\n";
	    self::assertEquals($expected, $this->addr->formatHTMLAddress(true));

    }

    public function testFormatStreet()
    {
    	self::assertEquals('', $this->addr->formatStreet());

    	$this->addr->address1->value = ' ';
	    self::assertEquals('', $this->addr->formatStreet());

	    $this->addr->address1->value = '123 N Rose St';
	    self::assertEquals('123 N Rose St', $this->addr->formatStreet());

	    $this->addr->address2->value = 'Building 2';
	    self::assertEquals('123 N Rose St, Building 2', $this->addr->formatStreet());

	    $this->addr->address1->value = " {$this->addr->address1->value} ";
	    $this->addr->address2->value = " {$this->addr->address2->value} ";
	    self::assertEquals('123 N Rose St, Building 2', $this->addr->formatStreet());

	    /* test limiting character count */
	    self::assertEquals('123 N Rose St, ', $this->addr->formatStreet(15));

	    /* test character limit greater than string length */
	    self::assertEquals('123 N Rose St, Building 2', $this->addr->formatStreet(1500));
    }

    public function testFormatContactName()
    {
    	$this->addr->salutation->value = 'Dr.';
    	$this->addr->firstname->value = 'Damien';
    	$this->addr->lastname->value = 'Barchowsky';
    	self::assertEquals('Damien Barchowsky', $this->addr->formatContactName());

	    $this->addr->salutation->value = '';
	    self::assertEquals('Damien Barchowsky', $this->addr->formatContactName());

	    $this->addr->firstname->value = '';
	    self::assertEquals('Barchowsky', $this->addr->formatContactName());

	    $this->addr->lastname->value = '';
	    self::assertEquals('', $this->addr->formatContactName());

	    $this->addr->firstname->value = 'Damien';
	    self::assertEquals('Damien', $this->addr->formatContactName());

	    $this->addr->firstname->value = null;
	    $this->addr->lastname->value = null;
	    self::assertEquals('', $this->addr->formatContactName());
    }

    public function testFullname()
    {
    	self::assertEquals('', $this->addr->formatFullName());

    	$this->addr->firstname->value = 'Foo';
	    self::assertEquals('Foo', $this->addr->formatFullName());

	    $this->addr->lastname->value = 'Bar';
	    self::assertEquals('Foo Bar', $this->addr->formatFullName());

	    $this->addr->salutation->value = 'Dr';
	    self::assertEquals('Dr Foo Bar', $this->addr->formatFullName());

	    $this->addr->firstname->value = " {$this->addr->firstname->value} ";
	    $this->addr->lastname->value = " {$this->addr->lastname->value} ";
	    $this->addr->salutation->value = " {$this->addr->salutation->value} ";
	    self::assertEquals('Dr Foo Bar', $this->addr->formatFullName());

	    $this->addr->firstname->value = '';
	    self::assertEquals('Dr Bar', $this->addr->formatFullName());

	    $this->addr->lastname->value = '';
	    self::assertEquals('Dr', $this->addr->formatFullName());
    }

    public function testGoogleMapsURI()
    {
    	self::assertRegExp('/\?key=.*\&address/', Address::GOOGLE_MAPS_URI());
    }

    public function testHasData()
    {
    	self::assertFalse($this->addr->hasData());

	    $this->addr->id->value = null;
	    $this->addr->firstname->value = '';
	    $this->addr->lastname->value = '';
	    $this->addr->email->value = '';
	    $this->addr->location->value = '';
	    $this->addr->address1->value = '';
	    $this->addr->city->value = '';
	    $this->addr->state_id->value = null;
	    self::assertFalse($this->addr->hasData());

	    $this->addr->firstname->value = null;
	    $this->addr->lastname->value = null;
	    $this->addr->email->value = null;
	    $this->addr->location->value = null;
	    $this->addr->address1->value = null;
	    $this->addr->city->value = null;
	    self::assertFalse($this->addr->hasData());

	    $this->addr->id->value = 1;
	    self::assertTrue($this->addr->hasData());

	    $this->addr->id->value = null;
	    $this->addr->firstname->value = 'foo';
	    self::assertTrue($this->addr->hasData());

	    $this->addr->firstname->value = '';
	    $this->addr->lastname->value = 'bar';
	    self::assertTrue($this->addr->hasData());

	    $this->addr->lastname->value = '';
	    $this->addr->email->value = 'dbarchowsky@gmail.com';
	    self::assertTrue($this->addr->hasData());

	    $this->addr->email->value = '';
	    $this->addr->location->value = 'biz';
	    self::assertTrue($this->addr->hasData());

	    $this->addr->location->value = '';
	    $this->addr->city->value = 'Paris';
	    self::assertTrue($this->addr->hasData());

	    $this->addr->city->value = '';
	    $this->addr->state_id->value = 22;
	    self::assertTrue($this->addr->hasData());
    }

    public function testInitialValues()
    {
        self::assertInstanceOf('Littled\Request\IntegerInput', $this->addr->id);
        self::assertInstanceOf('Littled\Request\StringTextField', $this->addr->province);
        self::assertEquals(AddressTest::TEST_ADDR2_SIZE, $this->addr->address2->sizeLimit);
        self::assertEquals(AddressTest::TEST_URL_SIZE, $this->addr->url->sizeLimit);
    }

	/**
	 * @todo Test against database that contains a 'zips' table.
	 * @throws RecordNotFoundException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 */
    public function disabled_testLookupMapPosition()
    {
    	$this->fetchTestRecord();
    	$this->addr->lookupMapPosition();
    	self::assertNotEquals('', $this->addr->latitude->value);
	    self::assertNotEquals('', $this->addr->longitude->value);
    }

    public function testPreserveInForm()
    {
    	$this->fetchTestRecord();
    	ob_start();
    	$this->addr->preserveInForm();
    	$markup = ob_get_clean();
    	self::assertRegExp("/^\W*<input .*name=\"{$this->addr->id->key}\" value=\"{$this->addr->id->value}\"/", $markup);
	    self::assertRegExp("/<input .*name=\"{$this->addr->lastname->key}\" value=\"{$this->addr->lastname->value}\"/", $markup);
	    self::assertRegExp("/<input .*name=\"{$this->addr->address1->key}\" value=\"{$this->addr->address1->value}\"/", $markup);
	    self::assertRegExp("/<input .*name=\"{$this->addr->city->key}\" value=\"{$this->addr->city->value}\"/", $markup);
	    self::assertRegExp("/<input .*name=\"{$this->addr->state_id->key}\" value=\"{$this->addr->state_id->value}\"/", $markup);
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
    }

    public function testRead()
    {
<<<<<<< HEAD
        $addr = new Address();
        $addr->id->value = AddressTest::TEST_ID_VALUE;
        try {
            $addr->read();
=======
        $this->addr->id->value = AddressTest::TEST_ID_VALUE;
        try {
            $this->addr->read();
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
        }
        catch (Exception $e)
        {
            print ("Exception: {$e}");
        }
<<<<<<< HEAD
        self::assertEquals(AddressTest::TEST_LAST_NAME_VALUE, $addr->lastname->value);
        self::assertEquals(AddressTest::TEST_STATE_VALUE, $addr->state);
        self::assertEquals(AddressTest::TEST_STATE_ABBREV_VALUE, $addr->state_abbrev);
=======
        self::assertEquals(AddressTest::TEST_LAST_NAME_VALUE, $this->addr->lastname->value);
        self::assertEquals(AddressTest::TEST_STATE_VALUE, $this->addr->state);
        self::assertEquals(AddressTest::TEST_STATE_ABBREV_VALUE, $this->addr->state_abbrev);
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
    }

    public function testReadNonexistentRecord()
    {
<<<<<<< HEAD
        $addr = new Address();
        $addr->id->value = AddressTest::TEST_NONEXISTENT_ID_VALUE;
        self::expectException(RecordNotFoundException::class);
        $addr->read();
    }
=======
        $this->addr->id->value = AddressTest::TEST_NONEXISTENT_ID_VALUE;
        self::expectException(RecordNotFoundException::class);
        $this->addr->read();
    }

    public function testReadStateProperties()
    {
    	self::assertEquals('', $this->addr->state);
	    self::assertEquals('', $this->addr->state_abbrev);

	    $this->addr->state_id->value = self::TEST_STATE_ID;
	    $this->addr->readStateProperties();
	    self::assertEquals('California', $this->addr->state);
	    self::assertEquals('CA', $this->addr->state_abbrev);

	    /* Test non-existent state */
	    $this->addr->state_id->value = 99999;
	    $this->expectException(RecordNotFoundException::class);
	    $this->addr->readStateProperties();

	    /* Test null state id */
	    $this->addr->state_id->value = null;
	    $this->expectException(InvalidValueException::class);
	    $this->addr->readStateProperties();
    }

    public function testSaveErrors()
    {
    	self::expectException(Exception::class);
    	$this->addr->save();

    	try {
    		$this->addr->save();
	    }
	    catch(\Exception $e) {
    		self::assertEquals('Error', $e->getMessage());
	    }
    }

	public function testSaveExistingRecord()
	{
		$this->addr->id->value = self::TEST_ID_VALUE;
		$this->addr->read();
		$lastname = $this->addr->lastname->value;
		$company = $this->addr->company->value;
		$state_id = $this->addr->state_id->value;
		$zip = $this->addr->zip->value;

		$this->addr->lastname->value = 'New Lastname';
		$this->addr->company->value = 'New Company';
		$this->addr->state_id->value = 25;
		$this->addr->zip->value = '89898';
		$this->addr->save();

		$addr = new Address();
		$addr->id->value = self::TEST_ID_VALUE;
		$addr->read();
		self::assertEquals('New Lastname', $addr->lastname->value);
		self::assertEquals('New Company', $addr->company->value);
		self::assertEquals(25, $addr->state_id->value);
		self::assertEquals('89898', $addr->zip->value);

		/* revert database record to original values */
		$this->addr->lastname->value = $lastname;
		$this->addr->company->value = $company;
		$this->addr->state_id->value = $state_id;
		$this->addr->zip->value = $zip;
		$this->addr->save();
	}

	public function testSaveNewRecord()
    {
    	$this->addr->firstname->value = 'Damien';
    	$this->addr->lastname->value = 'Barchowsky';
    	$this->addr->address1->value = '122 N Rose St';
    	$this->addr->city->value = 'Burbank';
    	$this->addr->state_id->value = self::TEST_STATE_ID;
    	$this->addr->zip->value = '91505';
    	$this->addr->save();

    	self::assertNotNull($this->addr->id->value);

    	$new_addr = new Address();
    	$new_addr->id->value = $this->addr->id->value;
    	$new_addr->read();

    	self::assertEquals($new_addr->firstname->value, $this->addr->firstname->value);
	    self::assertEquals($new_addr->lastname->value, $this->addr->lastname->value);
	    self::assertEquals($new_addr->company->value, $this->addr->company->value);
	    self::assertEquals($new_addr->address1->value, $this->addr->address1->value);
	    self::assertEquals($new_addr->address2->value, $this->addr->address2->value);
	    self::assertEquals($new_addr->city->value, $this->addr->city->value);
	    self::assertEquals($new_addr->state_id->value, $this->addr->state_id->value);
	    self::assertEquals($new_addr->zip->value, $this->addr->zip->value);
    }

    public function testTableName()
    {
    	self::assertEquals('address', Address::TABLE_NAME());
	    self::assertEquals('address', $this->addr::TABLE_NAME());
    }

    public function testValidateInputException()
    {
    	self::expectException(ContentValidationException::class);
    	$this->addr->validateInput();
    }

	public function testValidateInput()
	{
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringContainsString($this->addr->firstname->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->lastname->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->address1->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->city->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->state_id->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->zip->formatErrorLabel().' is required.', $errormsg);

		$this->addr->firstname->value = 'Damien';
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringNotContainsString($this->addr->firstname->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->lastname->formatErrorLabel().' is required.', $errormsg);

		$this->addr->lastname->value = 'Barchowsky';
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringNotContainsString($this->addr->lastname->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->address1->formatErrorLabel().' is required.', $errormsg);

		$this->addr->address1->value = '122 N Rose St';
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringNotContainsString($this->addr->address1->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->city->formatErrorLabel().' is required.', $errormsg);

		$this->addr->city->value = 'Burbank';
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringNotContainsString($this->addr->city->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->state_id->formatErrorLabel().' is required.', $errormsg);

		$this->addr->state_id->value = self::TEST_STATE_ID;
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringNotContainsString($this->addr->state_id->formatErrorLabel().' is required.', $errormsg);
		self::assertStringContainsString($this->addr->zip->formatErrorLabel().' is required.', $errormsg);

		$this->addr->zip->value = '91505';
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		self::assertEquals(0, count($this->addr->validationErrors));

		$this->addr->address1->value = '';
		try {
			$this->addr->validateInput();
		} catch(ContentValidationException $e) { /* continue */ }
		$errormsg = join('', $this->addr->validationErrors);
		self::assertStringContainsString($this->addr->address1->formatErrorLabel().' is required.', $errormsg);
		self::assertStringNotContainsString($this->addr->zip->formatErrorLabel().' is required.', $errormsg);
	}

	public function testValidateUniqueEmailDefaultValue()
	{
		try
		{
			$this->addr->validateUniqueEmail();
			self::assertEquals('Exception not thrown.', 'Exception not thrown.');
		}
		catch(ContentValidationException $ex)
		{
			self::assertEquals('', $ex->getMessage());
		}
	}

	public function testValidateUniqueExistingEmail()
	{
		$this->addr->email->value = self::TEST_EMAIL;
		try
		{
			$this->addr->validateUniqueEmail();
			self::assertEquals('', 'Exception not thrown.');
		}
		catch(ContentValidationException $ex)
		{
			self::assertEquals("The email address \"".self::TEST_EMAIL."\" has already been registered.", $ex->getMessage());
		}
	}

	public function testValidateUniqueValidEmail()
	{
		$this->addr->email->value = 'notindatabase@domain.com';
		try
		{
			$this->addr->validateUniqueEmail();
			self::assertEquals('Exception not thrown.', 'Exception not thrown.');
		}
		catch(ContentValidationException $ex)
		{
			self::assertEquals('', $ex->getMessage());
		}
	}
>>>>>>> 3602a466b49424d5d6c2cb940771652ebd0784fe
}