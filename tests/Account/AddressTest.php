<?php
namespace Littled\Tests\Account;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Account\Address;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
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
    const TEST_ADDRESS2_SIZE = 100;
    const TEST_EMAIL = 'dbarchowsky@gmail.com';
    const TEST_URL_SIZE = 255;
    const TEST_LAST_NAME_VALUE = 'Schultz';
    const TEST_STATE_VALUE = 'Oregon';
    const TEST_STATE_ABBREV_VALUE = 'OR';
    const TEST_STATE_ID = 9; /* California */

    /** @var Address $address */
    public $address;

    public function setUp(): void
    {
	    parent::setUp();
	    $this->address = new Address();
    }

	/**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws Exception
     */
    public function fetchTestRecord()
    {
        $this->address = new Address();
        $this->address->id->value = AddressTest::TEST_ID_VALUE;
        $this->address->read();
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function testCheckForDuplicate()
    {
        $this->fetchTestRecord();

        self::assertTrue($this->address->checkForDuplicate());

        $saved_value = $this->address->location->value;
        $this->address->location->value .= "x";
        self::assertFalse($this->address->checkForDuplicate());

        $this->address->location->value = $saved_value;
        $saved_value = $this->address->address1->value;
        $this->address->address1->value = " ".$this->address->address1->value;
        self::assertFalse($this->address->checkForDuplicate());

        $this->address->address1->value = $saved_value;
        $this->address->zip->value = substr($this->address->zip->value, 0, -1);
        self::assertFalse($this->address->checkForDuplicate());
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws InvalidValueException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     */
    public function testFormatAddress()
    {
    	$this->fetchTestRecord();
    	self::assertEquals($this->address->formatOneLineAddress(), $this->address->formatAddress());
	    self::assertEquals($this->address->formatOneLineAddress(), $this->address->formatAddress());
	    self::assertEquals($this->address->formatGoogleAddress(), $this->address->formatAddress('google'));
	    self::assertEquals($this->address->formatHTMLAddress(), $this->address->formatAddress('html'));
	    self::assertEquals($this->address->formatHTMLAddress(true), $this->address->formatAddress('html', true));
    }

    public function testFormatCity()
    {
	    $this->address->city->value = "Baltimore";
	    self::assertEquals("{$this->address->city->value}", $this->address->formatCity());

	    $this->address->state = "Maryland";
	    self::assertEquals("{$this->address->city->value}, {$this->address->state}", $this->address->formatCity());

	    $this->address->country->value = "USA";
	    $expected = "{$this->address->city->value}, {$this->address->state}, {$this->address->country->value}";
	    self::assertEquals($expected, $this->address->formatCity());

	    $this->address->state_abbrev = "MD";
	    $expected = "{$this->address->city->value}, {$this->address->state_abbrev}, {$this->address->country->value}";
	    self::assertEquals($expected, $this->address->formatCity());

	    $this->address->city->value = "Paris";
	    $this->address->state = "";
	    $this->address->state_abbrev = "";
	    $this->address->country->value = "FRANCE";
	    $expected = "{$this->address->city->value}, {$this->address->country->value}";
	    self::assertEquals($expected, $this->address->formatCity());

	    $this->address->zip->value = "DQF123";
	    $expected = "{$this->address->city->value}, {$this->address->country->value} {$this->address->zip->value}";
	    self::assertEquals($expected, $this->address->formatCity());

		$this->address->zip->value = '';
		$this->address->city->value = '';
	    self::assertEquals($this->address->country->value, $this->address->formatCity());

	    $this->address->state = 'Oklahoma';
	    $this->address->country->value = '';
	    self::assertEquals($this->address->state, $this->address->formatCity());

	    $this->address->state_abbrev = 'OK';
	    self::assertEquals($this->address->state_abbrev, $this->address->formatCity());
    }

    public function testFormatCityWithEmptyValues()
    {
	    self::assertEquals('', $this->address->formatCity());

    	$this->address->city->value = null;
	    $this->address->state = null;
	    $this->address->country->value = null;
	    $this->address->zip->value = null;
	    self::assertEquals('', $this->address->formatCity());

	    $this->address->city->value = '';
	    $this->address->state = '';
	    $this->address->country->value = '';
	    $this->address->zip->value = '';
	    self::assertEquals('', $this->address->formatCity());

	    $this->address->city->value = ' ';
	    $this->address->state = ' ';
	    $this->address->country->value = ' ';
	    $this->address->zip->value = ' ';
	    self::assertEquals('', $this->address->formatCity());
    }

    /**
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     */
    public function testFormatOneLineAddress()
    {
        $this->fetchTestRecord();
        self::assertEquals('10956 SE Main Street, Milwaukee, OR 97222', $this->address->formatOneLineAddress());

        $this->address->state_abbrev = '';
        self::assertEquals('10956 SE Main Street, Milwaukee, Oregon 97222', $this->address->formatOneLineAddress());

        $this->address->state = '';
        self::assertEquals('10956 SE Main Street, Milwaukee 97222', $this->address->formatOneLineAddress());

        $this->address->zip->value = '';
        self::assertEquals('10956 SE Main Street, Milwaukee', $this->address->formatOneLineAddress());

        $this->address->city->value = '';
        $this->address->zip->value = '99999';
        self::assertEquals('10956 SE Main Street 99999', $this->address->formatOneLineAddress());

        $this->address->state = null;
        $this->address->city->value = null;
        self::assertEquals('10956 SE Main Street 99999', $this->address->formatOneLineAddress());

        $this->address->address1->value = '';
        $this->address->city->value = 'City';
        $this->address->state_abbrev = 'ST';
        self::assertEquals('City, ST 99999', $this->address->formatOneLineAddress());

        $this->address->address1->value = '123 Some Lane';
        $this->address->city->value = 'London';
        $this->address->state = '';
        $this->address->state_abbrev = '';
        $this->address->country->value = 'UK';
        self::assertEquals('123 Some Lane, London, UK 99999', $this->address->formatOneLineAddress());

        $this->address->city->value = '';
        $this->address->zip->value = '';
        self::assertEquals('123 Some Lane, UK', $this->address->formatOneLineAddress());
    }

    /**
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     */
    public function testFormatGoogleAddress()
    {
        $this->fetchTestRecord();
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee%2C+OR+97222', $this->address->formatGoogleAddress());

        $this->address->state_abbrev = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee%2C+Oregon+97222', $this->address->formatGoogleAddress());

        $this->address->state = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee+97222', $this->address->formatGoogleAddress());

        $this->address->zip->value = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee', $this->address->formatGoogleAddress());

        $this->address->city->value = '';
        $this->address->zip->value = '99999';
        self::assertEquals('10956+SE+Main+Street+99999', $this->address->formatGoogleAddress());

        $this->address->state = null;
        $this->address->city->value = null;
        self::assertEquals('10956+SE+Main+Street+99999', $this->address->formatGoogleAddress());

        $this->address->address1->value = '';
        $this->address->city->value = 'City';
        $this->address->state_abbrev = 'ST';
        self::assertEquals('City%2C+ST+99999', $this->address->formatGoogleAddress());
    }

    public function testInitialValues()
    {
        $address = new Address();
        $this->assertInstanceOf('Littled\Request\IntegerInput', $address->id);
        $this->assertInstanceOf('Littled\Request\StringTextField', $address->province);
        $this->assertEquals(AddressTest::TEST_ADDRESS2_SIZE, $address->address2->sizeLimit);
        $this->assertEquals(AddressTest::TEST_URL_SIZE, $address->url->sizeLimit);
    }

    public function testFormatHTMLAddressEmptyValues()
    {
    	$this->assertEquals('', $this->address->formatHTMLAddress(true));

    	$this->address->firstname->value = null;
	    $this->address->company->value = null;
	    $this->address->address1->value = null;
	    $this->address->city->value = null;
	    $this->assertEquals('', $this->address->formatHTMLAddress(true));

	    $this->address->firstname->value = '';
	    $this->address->company->value = '';
	    $this->address->address1->value = '';
	    $this->address->city->value = '';
	    $this->assertEquals('', $this->address->formatHTMLAddress(true));

	    $this->address->firstname->value = ' ';
	    $this->address->company->value = ' ';
	    $this->address->address1->value = ' ';
	    $this->address->city->value = ' ';
	    $this->assertEquals('', $this->address->formatHTMLAddress(true));
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     */
    public function testFormatHTMLAddressIncludeName()
    {
	    $this->fetchTestRecord();

	    $expected = "<div>{$this->address->firstname->value} {$this->address->lastname->value}</div>\n".
		    "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->address2->value = "Building 2";
	    $expected = "<div>{$this->address->firstname->value} {$this->address->lastname->value}</div>\n".
		    "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->address2->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->company->value = "Puckanerry Inc";
	    $expected = "<div>{$this->address->firstname->value} {$this->address->lastname->value}</div>\n".
		    "<div>{$this->address->company->value}</div>\n".
		    "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->address2->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->address1->value = "";
	    $this->address->address2->value = "";
	    $expected = "<div>{$this->address->firstname->value} {$this->address->lastname->value}</div>\n".
		    "<div>{$this->address->company->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->firstname->value = "";
	    $this->address->lastname->value = "";
	    $expected = "<div>{$this->address->company->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->company->value = "";
	    $expected = "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->address1->value = "122 N Rose St";
	    $expected = "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

    }

    public function testFormatStreet()
    {
    	$this->assertEquals('', $this->address->formatStreet());

    	$this->address->address1->value = ' ';
	    $this->assertEquals('', $this->address->formatStreet());

	    $this->address->address1->value = '123 N Rose St';
	    $this->assertEquals('123 N Rose St', $this->address->formatStreet());

	    $this->address->address2->value = 'Building 2';
	    $this->assertEquals('123 N Rose St, Building 2', $this->address->formatStreet());

	    $this->address->address1->value = " {$this->address->address1->value} ";
	    $this->address->address2->value = " {$this->address->address2->value} ";
	    $this->assertEquals('123 N Rose St, Building 2', $this->address->formatStreet());

	    /* test limiting character count */
	    $this->assertEquals('123 N Rose St, ', $this->address->formatStreet(15));

	    /* test character limit greater than string length */
	    $this->assertEquals('123 N Rose St, Building 2', $this->address->formatStreet(1500));
    }

    public function testFormatContactName()
    {
    	$this->address->salutation->value = 'Dr.';
    	$this->address->firstname->value = 'Damien';
    	$this->address->lastname->value = 'Barchowsky';
    	$this->assertEquals('Damien Barchowsky', $this->address->formatContactName());

	    $this->address->salutation->value = '';
	    $this->assertEquals('Damien Barchowsky', $this->address->formatContactName());

	    $this->address->firstname->value = '';
	    $this->assertEquals('Barchowsky', $this->address->formatContactName());

	    $this->address->lastname->value = '';
	    $this->assertEquals('', $this->address->formatContactName());

	    $this->address->firstname->value = 'Damien';
	    $this->assertEquals('Damien', $this->address->formatContactName());

	    $this->address->firstname->value = null;
	    $this->address->lastname->value = null;
	    $this->assertEquals('', $this->address->formatContactName());
    }

    public function testFullname()
    {
    	$this->assertEquals('', $this->address->formatFullName());

    	$this->address->firstname->value = 'Foo';
	    $this->assertEquals('Foo', $this->address->formatFullName());

	    $this->address->lastname->value = 'Bar';
	    $this->assertEquals('Foo Bar', $this->address->formatFullName());

	    $this->address->salutation->value = 'Dr';
	    $this->assertEquals('Dr Foo Bar', $this->address->formatFullName());

	    $this->address->firstname->value = " {$this->address->firstname->value} ";
	    $this->address->lastname->value = " {$this->address->lastname->value} ";
	    $this->address->salutation->value = " {$this->address->salutation->value} ";
	    $this->assertEquals('Dr Foo Bar', $this->address->formatFullName());

	    $this->address->firstname->value = '';
	    $this->assertEquals('Dr Bar', $this->address->formatFullName());

	    $this->address->lastname->value = '';
	    $this->assertEquals('Dr', $this->address->formatFullName());
    }

    public function testGoogleMapsURI()
    {
    	$this->assertMatchesRegularExpression('/\?key=.*\&address/', Address::GOOGLE_MAPS_URI());
    }

    public function testHasData()
    {
    	$this->assertFalse($this->address->hasData());

	    $this->address->id->value = null;
	    $this->address->firstname->value = '';
	    $this->address->lastname->value = '';
	    $this->address->email->value = '';
	    $this->address->location->value = '';
	    $this->address->address1->value = '';
	    $this->address->city->value = '';
	    $this->address->state_id->value = null;
	    $this->assertFalse($this->address->hasData());

	    $this->address->firstname->value = null;
	    $this->address->lastname->value = null;
	    $this->address->email->value = null;
	    $this->address->location->value = null;
	    $this->address->address1->value = null;
	    $this->address->city->value = null;
	    $this->assertFalse($this->address->hasData());

	    $this->address->id->value = 1;
	    $this->assertTrue($this->address->hasData());

	    $this->address->id->value = null;
	    $this->address->firstname->value = 'foo';
	    $this->assertTrue($this->address->hasData());

	    $this->address->firstname->value = '';
	    $this->address->lastname->value = 'bar';
	    $this->assertTrue($this->address->hasData());

	    $this->address->lastname->value = '';
	    $this->address->email->value = 'dbarchowsky@gmail.com';
	    $this->assertTrue($this->address->hasData());

	    $this->address->email->value = '';
	    $this->address->location->value = 'biz';
	    $this->assertTrue($this->address->hasData());

	    $this->address->location->value = '';
	    $this->address->city->value = 'Paris';
	    $this->assertTrue($this->address->hasData());

	    $this->address->city->value = '';
	    $this->address->state_id->value = 22;
	    $this->assertTrue($this->address->hasData());
    }

    /**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws NotImplementedException
     * @throws Exception
     * @todo Test against database that contains a 'zips' table.
     */
    public function disabled_testLookupMapPosition()
    {
    	$this->fetchTestRecord();
    	$this->address->lookupMapPosition();
    	$this->assertNotEquals('', $this->address->latitude->value);
	    $this->assertNotEquals('', $this->address->longitude->value);
    }

    /**
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ResourceNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     */
    public function testPreserveInForm()
    {
    	$this->fetchTestRecord();
    	ob_start();
    	$this->address->preserveInForm();
    	$markup = ob_get_clean();
    	$this->assertMatchesRegularExpression("/^\W*<input ."."*name=\"{$this->address->id->key}\" value=\"{$this->address->id->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->lastname->key}\" value=\"{$this->address->lastname->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->address1->key}\" value=\"{$this->address->address1->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->city->key}\" value=\"{$this->address->city->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->state_id->key}\" value=\"{$this->address->state_id->value}\"/", $markup);
    }

    public function testRead()
    {
        $this->address->id->value = AddressTest::TEST_ID_VALUE;
        try {
            $this->address->read();
        }
        catch (Exception $e)
        {
            print ("Exception: $e");
        }
        $this->assertEquals(AddressTest::TEST_LAST_NAME_VALUE, $this->address->lastname->value);
        $this->assertEquals(AddressTest::TEST_STATE_VALUE, $this->address->state);
        $this->assertEquals(AddressTest::TEST_STATE_ABBREV_VALUE, $this->address->state_abbrev);
    }

    /**
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     */
    public function testReadNonexistentRecord()
    {
        $this->address->id->value = AddressTest::TEST_NONEXISTENT_ID_VALUE;
        $this->expectException(RecordNotFoundException::class);
        $this->address->read();
    }

    /**
     * @throws InvalidValueException
     * @throws RecordNotFoundException
     */
    public function testReadStateProperties()
    {
    	$this->assertEquals('', $this->address->state);
	    $this->assertEquals('', $this->address->state_abbrev);

	    $this->address->state_id->value = $this::TEST_STATE_ID;
	    $this->address->readStateProperties();
	    $this->assertEquals('California', $this->address->state);
	    $this->assertEquals('CA', $this->address->state_abbrev);

	    /* Test non-existent state */
	    $this->address->state_id->value = 99999;
	    $this->expectException(RecordNotFoundException::class);
	    $this->address->readStateProperties();

	    /* Test null state id */
	    $this->address->state_id->value = null;
	    $this->expectException(InvalidValueException::class);
	    $this->address->readStateProperties();
    }

    public function testSaveErrors()
    {
    	$this->expectException(Exception::class);
    	$this->address->save();

    	try {
    		$this->address->save();
	    }
	    catch(Exception $e) {
    		$this->assertEquals('Error', $e->getMessage());
	    }
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws ContentValidationException
     * @throws Exception
     */
    public function testSaveExistingRecord()
	{
		$this->address->id->value = $this::TEST_ID_VALUE;
		$this->address->read();
		$lastname = $this->address->lastname->value;
		$company = $this->address->company->value;
		$state_id = $this->address->state_id->value;
		$zip = $this->address->zip->value;

		$this->address->lastname->value = 'New Lastname';
		$this->address->company->value = 'New Company';
		$this->address->state_id->value = 25;
		$this->address->zip->value = '89898';
		$this->address->save();

		$address = new Address();
		$address->id->value = $this::TEST_ID_VALUE;
		$address->read();
		$this->assertEquals('New Lastname', $address->lastname->value);
		$this->assertEquals('New Company', $address->company->value);
		$this->assertEquals(25, $address->state_id->value);
		$this->assertEquals('89898', $address->zip->value);

		/* revert database record to original values */
		$this->address->lastname->value = $lastname;
		$this->address->company->value = $company;
		$this->address->state_id->value = $state_id;
		$this->address->zip->value = $zip;
		$this->address->save();
	}

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function testSaveNewRecord()
    {
    	$this->address->firstname->value = 'Damien';
    	$this->address->lastname->value = 'Barchowsky';
    	$this->address->address1->value = '122 N Rose St';
    	$this->address->city->value = 'Burbank';
    	$this->address->state_id->value = $this::TEST_STATE_ID;
    	$this->address->zip->value = '91505';
    	$this->address->save();

    	$this->assertNotNull($this->address->id->value);

    	$new_address = new Address();
    	$new_address->id->value = $this->address->id->value;
    	$new_address->read();

    	$this->assertEquals($new_address->firstname->value, $this->address->firstname->value);
	    $this->assertEquals($new_address->lastname->value, $this->address->lastname->value);
	    $this->assertEquals($new_address->company->value, $this->address->company->value);
	    $this->assertEquals($new_address->address1->value, $this->address->address1->value);
	    $this->assertEquals($new_address->address2->value, $this->address->address2->value);
	    $this->assertEquals($new_address->city->value, $this->address->city->value);
	    $this->assertEquals($new_address->state_id->value, $this->address->state_id->value);
	    $this->assertEquals($new_address->zip->value, $this->address->zip->value);
    }

    /**
     * @throws NotImplementedException
     */
    public function testTableName()
    {
    	$this->assertEquals('address', Address::TABLE_NAME());
	    $this->assertEquals('address', $this->address::TABLE_NAME());
    }

    /**
     * @throws Exception
     */
    public function testValidateInputException()
    {
    	$this->expectException(ContentValidationException::class);
    	$this->address->validateInput();
    }

	public function testValidateInput()
	{
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringContainsString($this->address->firstname->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->lastname->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->address1->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->city->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->state_id->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->zip->formatErrorLabel().' is required.', $error_msg);

		$this->address->firstname->value = 'Damien';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->firstname->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->lastname->formatErrorLabel().' is required.', $error_msg);

		$this->address->lastname->value = 'Barchowsky';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->lastname->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->address1->formatErrorLabel().' is required.', $error_msg);

		$this->address->address1->value = '122 N Rose St';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->address1->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->city->formatErrorLabel().' is required.', $error_msg);

		$this->address->city->value = 'Burbank';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->city->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->state_id->formatErrorLabel().' is required.', $error_msg);

		$this->address->state_id->value = $this::TEST_STATE_ID;
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->state_id->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->zip->formatErrorLabel().' is required.', $error_msg);

		$this->address->zip->value = '91505';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $this->assertCount(0, $this->address->validationErrors);

		$this->address->address1->value = '';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringContainsString($this->address->address1->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringNotContainsString($this->address->zip->formatErrorLabel().' is required.', $error_msg);
	}

    /*
	public function testValidateUniqueEmailDefaultValue()
	{
        $this->address->validateUniqueEmail();
    }

	public function testValidateUniqueExistingEmail()
    {
    	$this->address->email->value = $this::TEST_EMAIL;
        $this->address->validateUniqueEmail();
    }

	public function testValidateUniqueValidEmail()
	{
		$this->address->email->value = 'notindatabase@domain.com';
        $this->address->validateUniqueEmail();
    }
    */
}