<?php
namespace Littled\Tests\Account;

use Littled\Account\Address;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Exception\ResourceNotFoundException;
use Littled\Request\RequestInput;
use Littled\Tests\DataProvider\Account\AddressTestData;
use Littled\Tests\TestExtensions\ContentValidationTestCase;
use Exception;

/**
 * Class AddressTest
 * Unit Tests for Littled\Account\Address
 * @package Littled\Tests\Account
 */
class AddressTest extends ContentValidationTestCase
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

    public Address $address;

    public function setUp(): void
    {
	    parent::setUp();
	    $this->address = new Address();
        RequestInput::setTemplateBasePath(SHARED_CMS_TEMPLATE_DIR);
        Address::setCommonCMSTemplatePath(SHARED_CMS_TEMPLATE_DIR);
    }

	/**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
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

	    $this->address->state->setInputValue("Maryland");
	    self::assertEquals("{$this->address->city->value}, {$this->address->state->value}", $this->address->formatCity());

	    $this->address->country->setInputValue("USA");
	    $expected = "{$this->address->city->value}, {$this->address->state->value}, {$this->address->country->value}";
	    self::assertEquals($expected, $this->address->formatCity());

	    $this->address->state_abbrev = "MD";
	    $expected = "{$this->address->city->value}, {$this->address->state_abbrev}, {$this->address->country->value}";
	    self::assertEquals($expected, $this->address->formatCity());

	    $this->address->city->value = "Paris";
	    $this->address->state->setInputValue("");
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

	    $this->address->state->setInputValue('Oklahoma');
	    $this->address->country->value = '';
	    self::assertEquals($this->address->state->value, $this->address->formatCity());

	    $this->address->state_abbrev = 'OK';
	    self::assertEquals($this->address->state_abbrev, $this->address->formatCity());
    }

    public function testFormatCityWithEmptyValues()
    {
	    self::assertEquals('', $this->address->formatCity());

    	$this->address->city->value = null;
	    $this->address->state->value = null;
	    $this->address->country->value = null;
	    $this->address->zip->value = null;
	    self::assertEquals('', $this->address->formatCity());

	    $this->address->city->value = '';
	    $this->address->state->value = '';
	    $this->address->country->value = '';
	    $this->address->zip->value = '';
	    self::assertEquals('', $this->address->formatCity());

	    $this->address->city->value = ' ';
	    $this->address->state->value = ' ';
	    $this->address->country->value = ' ';
	    $this->address->zip->value = ' ';
	    self::assertEquals('', $this->address->formatCity());
    }

    /**
     * @throws ConnectionException
     * @throws NotImplementedException
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

        $this->address->state->value = '';
        self::assertEquals('10956 SE Main Street, Milwaukee 97222', $this->address->formatOneLineAddress());

        $this->address->zip->value = '';
        self::assertEquals('10956 SE Main Street, Milwaukee', $this->address->formatOneLineAddress());

        $this->address->city->value = '';
        $this->address->zip->value = '99999';
        self::assertEquals('10956 SE Main Street 99999', $this->address->formatOneLineAddress());

        $this->address->state->value = null;
        $this->address->city->value = null;
        self::assertEquals('10956 SE Main Street 99999', $this->address->formatOneLineAddress());

        $this->address->address1->value = '';
        $this->address->city->value = 'City';
        $this->address->state_abbrev = 'ST';
        self::assertEquals('City, ST 99999', $this->address->formatOneLineAddress());

        $this->address->address1->value = '123 Some Lane';
        $this->address->city->value = 'London';
        $this->address->state->value = '';
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
     * @throws NotImplementedException
     */
    public function testFormatGoogleAddress()
    {
        $this->fetchTestRecord();
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee%2C+OR+97222', $this->address->formatGoogleAddress());

        $this->address->state_abbrev = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee%2C+Oregon+97222', $this->address->formatGoogleAddress());

        $this->address->state->value = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee+97222', $this->address->formatGoogleAddress());

        $this->address->zip->value = '';
        self::assertEquals('10956+SE+Main+Street%2C+Milwaukee', $this->address->formatGoogleAddress());

        $this->address->city->value = '';
        $this->address->zip->value = '99999';
        self::assertEquals('10956+SE+Main+Street+99999', $this->address->formatGoogleAddress());

        $this->address->state->value = null;
        $this->address->city->value = null;
        self::assertEquals('10956+SE+Main+Street+99999', $this->address->formatGoogleAddress());

        $this->address->address1->value = '';
        $this->address->city->value = 'City';
        $this->address->state_abbrev = 'ST';
        self::assertEquals('City%2C+ST+99999', $this->address->formatGoogleAddress());
    }

    public function testFormatHTMLAddressEmptyValues()
    {
    	$this->assertEquals('', $this->address->formatHTMLAddress(true));

    	$this->address->first_name->value = null;
	    $this->address->company->value = null;
	    $this->address->address1->value = null;
	    $this->address->city->value = null;
	    $this->assertEquals('', $this->address->formatHTMLAddress(true));

	    $this->address->first_name->value = '';
	    $this->address->company->value = '';
	    $this->address->address1->value = '';
	    $this->address->city->value = '';
	    $this->assertEquals('', $this->address->formatHTMLAddress(true));

	    $this->address->first_name->value = ' ';
	    $this->address->company->value = ' ';
	    $this->address->address1->value = ' ';
	    $this->address->city->value = ' ';
	    $this->assertEquals('', $this->address->formatHTMLAddress(true));
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     */
    public function testFormatHTMLAddressIncludeName()
    {
	    $this->fetchTestRecord();

	    $expected = "<div>{$this->address->first_name->value} {$this->address->last_name->value}</div>\n".
		    "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->address2->value = "Building 2";
	    $expected = "<div>{$this->address->first_name->value} {$this->address->last_name->value}</div>\n".
		    "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->address2->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->company->value = "Puckanerry Inc";
	    $expected = "<div>{$this->address->first_name->value} {$this->address->last_name->value}</div>\n".
		    "<div>{$this->address->company->value}</div>\n".
		    "<div>{$this->address->address1->value}</div>\n".
		    "<div>{$this->address->address2->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->address1->value = "";
	    $this->address->address2->value = "";
	    $expected = "<div>{$this->address->first_name->value} {$this->address->last_name->value}</div>\n".
		    "<div>{$this->address->company->value}</div>\n".
		    "<div>{$this->address->city->value}, {$this->address->state_abbrev} {$this->address->zip->value}</div>\n";
	    $this->assertEquals($expected, $this->address->formatHTMLAddress(true));

	    $this->address->first_name->value = "";
	    $this->address->last_name->value = "";
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
    	$this->address->first_name->value = 'Damien';
    	$this->address->last_name->value = 'Barchowsky';
    	$this->assertEquals('Damien Barchowsky', $this->address->formatContactName());

	    $this->address->salutation->value = '';
	    $this->assertEquals('Damien Barchowsky', $this->address->formatContactName());

	    $this->address->first_name->value = '';
	    $this->assertEquals('Barchowsky', $this->address->formatContactName());

	    $this->address->last_name->value = '';
	    $this->assertEquals('', $this->address->formatContactName());

	    $this->address->first_name->value = 'Damien';
	    $this->assertEquals('Damien', $this->address->formatContactName());

	    $this->address->first_name->value = null;
	    $this->address->last_name->value = null;
	    $this->assertEquals('', $this->address->formatContactName());
    }

    public function testFullname()
    {
    	$this->assertEquals('', $this->address->formatFullName());

    	$this->address->first_name->value = 'Foo';
	    $this->assertEquals('Foo', $this->address->formatFullName());

	    $this->address->last_name->value = 'Bar';
	    $this->assertEquals('Foo Bar', $this->address->formatFullName());

	    $this->address->salutation->value = 'Dr';
	    $this->assertEquals('Dr Foo Bar', $this->address->formatFullName());

	    $this->address->first_name->value = " {$this->address->first_name->value} ";
	    $this->address->last_name->value = " {$this->address->last_name->value} ";
	    $this->address->salutation->value = " {$this->address->salutation->value} ";
	    $this->assertEquals('Dr Foo Bar', $this->address->formatFullName());

	    $this->address->first_name->value = '';
	    $this->assertEquals('Dr Bar', $this->address->formatFullName());

	    $this->address->last_name->value = '';
	    $this->assertEquals('Dr', $this->address->formatFullName());
    }

	/**
	 * @throws ConfigurationUndefinedException
	 */
	public function testGoogleMapsURI()
    {
		if (!defined('GMAP_KEY')) {
			throw new ConfigurationUndefinedException("GMAP_KEY is not defined.");
		}
		Address::setGMapAPIKey(GMAP_KEY);
    	$this->assertMatchesRegularExpression('/\?key=.*&address/', Address::GOOGLE_MAPS_URI());
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\Account\AddressTestDataProvider::hasAddressDataTestProvider()
	 * @return void
	 */
	public function testHasAddressData(AddressTestData $data)
	{
		if ($data->expected===true) {
			$this->assertTrue($data->obj->hasAddressData(), $data->msg);
		}
		else {
			$this->assertFalse($data->obj->hasAddressData(), $data->msg);
		}
	}

    public function testHasData()
    {
    	$this->assertFalse($this->address->hasData());

	    $this->address->id->value = null;
	    $this->address->first_name->value = '';
	    $this->address->last_name->value = '';
	    $this->address->email->value = '';
	    $this->address->location->value = '';
	    $this->address->address1->value = '';
	    $this->address->city->value = '';
	    $this->address->state_id->value = null;
	    $this->assertFalse($this->address->hasData());

	    $this->address->first_name->value = null;
	    $this->address->last_name->value = null;
	    $this->address->email->value = null;
	    $this->address->location->value = null;
	    $this->address->address1->value = null;
	    $this->address->city->value = null;
	    $this->assertFalse($this->address->hasData());

	    $this->address->id->value = 1;
	    $this->assertTrue($this->address->hasData());

	    $this->address->id->value = null;
	    $this->address->first_name->value = 'foo';
	    $this->assertTrue($this->address->hasData());

	    $this->address->first_name->value = '';
	    $this->address->last_name->value = 'bar';
	    $this->assertTrue($this->address->hasData());

	    $this->address->last_name->value = '';
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

    public function testInitialValues()
    {
        $address = new Address();
        $this->assertInstanceOf('Littled\Request\IntegerInput', $address->id);
        $this->assertInstanceOf('Littled\Request\StringTextField', $address->state);
        $this->assertEquals(AddressTest::TEST_ADDRESS2_SIZE, $address->address2->size_limit);
        $this->assertEquals(AddressTest::TEST_URL_SIZE, $address->url->size_limit);
    }

    /**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
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
     * @return void
     * @throws Exception
     */
    function testLookupStateByName()
    {
        $md_state_id = 27;
        $ca_state_id = 9;

        $this->address->state->setInputValue('md');
        $this->assertEquals($md_state_id, $this->address->lookupStateByName());
        $this->assertEquals($md_state_id, $this->address->id->value);

        $this->address->state->setInputValue('CA');
        $this->assertEquals($ca_state_id, $this->address->lookupStateByName());
        $this->assertEquals($ca_state_id, $this->address->id->value);

        $this->address->state->setInputValue('Maryland');
        $this->assertEquals($md_state_id, $this->address->lookupStateByName());
        $this->assertEquals($md_state_id, $this->address->id->value);

        $this->address->state->setInputValue('california');
        $this->assertEquals($ca_state_id, $this->address->lookupStateByName());
        $this->assertEquals($ca_state_id, $this->address->id->value);

        $this->address->state->setInputValue('bogus name');
        $this->assertNull($this->address->lookupStateByName());
        $this->assertNull($this->address->id->value);
    }

    /**
     * @throws ConnectionException
     * @throws NotImplementedException
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
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->last_name->key}\" value=\"{$this->address->last_name->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->address1->key}\" value=\"{$this->address->address1->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->city->key}\" value=\"{$this->address->city->value}\"/", $markup);
	    $this->assertMatchesRegularExpression("/<input ."."*name=\"{$this->address->state_id->key}\" value=\"{$this->address->state_id->value}\"/", $markup);
    }

    /**
     * @return void
     * @throws ResourceNotFoundException
     */
    public function testPreservePhysicalAddressInForm()
    {
        ob_start();
        $this->address->preservePhysicalAddressInForm();
        $markup = ob_get_clean();
        $this->assertDoesNotMatchRegularExpression("/{$this->address->first_name->key}/", $markup);
        $this->assertMatchesRegularExpression("/{$this->address->city->key}/", $markup);
    }

    /**
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testRead()
    {
        $this->address->id->value = AddressTest::TEST_ID_VALUE;
        $this->address->read();

        $this->assertEquals(AddressTest::TEST_LAST_NAME_VALUE, $this->address->last_name->value);
        $this->assertEquals(AddressTest::TEST_STATE_VALUE, $this->address->state->value);
        $this->assertEquals(AddressTest::TEST_STATE_ABBREV_VALUE, $this->address->state_abbrev);
    }

    /**
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public function testReadNonexistentRecord()
    {
        $this->address->id->value = AddressTest::TEST_NONEXISTENT_ID_VALUE;
        $this->expectException(RecordNotFoundException::class);
        $this->address->read();
    }

    /**
     * @throws RecordNotFoundException
     */
    public function testReadStateProperties()
    {
    	$this->assertEquals('', $this->address->state->value);
	    $this->assertEquals('', $this->address->state_abbrev);

	    $this->address->state_id->value = $this::TEST_STATE_ID;
	    $this->address->readStateProperties();
	    $this->assertEquals('California', $this->address->state->safeValue());
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
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws ContentValidationException
     * @throws Exception
     */
    public function testSaveExistingRecord()
	{
		$this->address->id->value = $this::TEST_ID_VALUE;
		$this->address->read();
		$lastname = $this->address->last_name->value;
		$company = $this->address->company->value;
		$state_id = $this->address->state_id->value;
		$zip = $this->address->zip->value;

		$this->address->last_name->value = 'New Lastname';
		$this->address->company->value = 'New Company';
		$this->address->state_id->value = 25;
		$this->address->zip->value = '89898';
		$this->address->save();

		$address = new Address();
		$address->id->value = $this::TEST_ID_VALUE;
		$address->read();
		$this->assertEquals('New Lastname', $address->last_name->value);
		$this->assertEquals('New Company', $address->company->value);
		$this->assertEquals(25, $address->state_id->value);
		$this->assertEquals('89898', $address->zip->value);

		/* revert database record to original values */
		$this->address->last_name->value = $lastname;
		$this->address->company->value = $company;
		$this->address->state_id->value = $state_id;
		$this->address->zip->value = $zip;
		$this->address->save();
	}

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    public function testSaveNewRecord()
    {
    	$this->address->first_name->value = 'Damien';
    	$this->address->last_name->value = 'Barchowsky';
    	$this->address->address1->value = '122 N Rose St';
    	$this->address->city->value = 'Burbank';
    	$this->address->state_id->value = $this::TEST_STATE_ID;
    	$this->address->zip->value = '91505';
    	$this->address->save();

        $data = $this->address->fetchRecords('SELECT LAST_INSERT_ID() as `insert_id`');
        if (1 > count($data)) {
            throw new Exception('Could not retrieve insert id.');
        }

    	$this->assertEquals($data[0]->insert_id, $this->address->id->value);

        // confirm values stored in the database
    	$new_address = new Address();
    	$new_address->id->value = $this->address->id->value;
    	$new_address->read();

    	$this->assertEquals($new_address->first_name->value, $this->address->first_name->value);
	    $this->assertEquals($new_address->last_name->value, $this->address->last_name->value);
	    $this->assertEquals($new_address->company->value, $this->address->company->value);
	    $this->assertEquals($new_address->address1->value, $this->address->address1->value);
	    $this->assertEquals($new_address->address2->value, $this->address->address2->value);
	    $this->assertEquals($new_address->city->value, $this->address->city->value);
	    $this->assertEquals($new_address->state_id->value, $this->address->state_id->value);
	    $this->assertEquals($new_address->zip->value, $this->address->zip->value);

        // clean up
        $new_address->delete();
        $this->address->id->value = null;
    }

    /**
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    function testSaveProcedure()
    {
        $query = 'CALL addressUpdate(@record_id,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $types = 'ssssssisssssssssssii';
        $record_id = $longitude = $latitude = null;
        $salutation = $address1 = $address2 = $state = $fax = $company = '';
        $home_phone = $work_phone = $title = $location = $url = '';
        $first_name = 'Damien';
        $last_name = 'Barchowsky';
        $city = 'Burbank';
        $state_id = 9;
        $zip = '91505';
        $country = 'US';
        $email = 'dbarchowsky@gmail.com';
        $mysqli = $this->address->getMysqli();

        // configure record id as MYSQL session variable in order to retrieve insert id
        $s1 = $mysqli->prepare('SET @record_id = ?');
        $s1->bind_param('i', $record_id);
        $s1->execute();

        // call stored procedure to create a new address record
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($types,
            $salutation,
            $first_name,
            $last_name,
            $address1,
            $address2,
            $city,
            $state_id,
            $state,
            $zip,
            $country,
            $home_phone,
            $work_phone,
            $fax,
            $email,
            $company,
            $title,
            $location,
            $url,
            $longitude,
            $latitude
        );
        $stmt->execute();
        $stmt->close();

        // retrieve record id value
        $result = $mysqli->query('SELECT @record_id as `insert_id`');
        $row = $result->fetch_object();
        $record_id = $row->insert_id;
        $result->free();
        $s1->close();

        // query to confirm insert id value
        $result = $mysqli->query('SELECT LAST_INSERT_ID() as `insert_id`');
        if (!$result) {
            throw new Exception('Error retrieving insert id '.$mysqli->error);
        }
        $row = $result->fetch_object();
        $insert_id = $row->insert_id;
        $result->free();

        // confirm insert id value
        $this->assertNotNull($record_id);
        $this->assertEquals($insert_id, $record_id);

        // clean up new record
        $stmt = $mysqli->prepare('DELETE FROM `address` WHERE id = ?');
        $stmt->bind_param('i', $record_id);
        if (!$stmt->execute()) {
            throw new Exception('Error deleting inserted record: '.$stmt->error);
        }
        $stmt->close();
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    function testUpdateProcedure()
    {
        $address = new Address();
        $address->id->setInputValue(self::TEST_ID_VALUE);
        $address->read();

        $query = 'CALL addressUpdate(@record_id,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $types = 'ssssssisssssssssssii';

        $mysqli = $this->address->getMysqli();
        $s1 = $mysqli->prepare('SET @record_id = ?');
        $s1->bind_param('i', $address->id->value);
        $s1->execute();

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($types,
            $address->salutation->value,
            $address->first_name->value,
            $address->last_name->value,
            $address->address1->value,
            $address->address2->value,
            $address->city->value,
            $address->state_id->value,
            $address->state->value,
            $address->zip->value,
            $address->country->value,
            $address->home_phone->value,
            $address->work_phone->value,
            $address->fax->value,
            $address->email->value,
            $address->company->value,
            $address->title->value,
            $address->location->value,
            $address->url->value,
            $address->longitude->value,
            $address->latitude->value
        );
        $stmt->execute();
        $stmt->close();

        // verify the value returned in @record_id parameter
        $result = $mysqli->query('SELECT @record_id as `record_id`');
        $row = $result->fetch_object();
        $record_id = $row->record_id;
        $result->free();
        $s1->close();

        $this->assertEquals(self::TEST_ID_VALUE, $record_id);

        // verify that the existing id is different from whatever the last insert id value may be
        $result = $mysqli->query('SELECT LAST_INSERT_ID() as `insert_id`');
        if(1 > $result->num_rows) {
            throw new Exception('Error retrieving insert id.');
        }
        $row = $result->fetch_object();
        $result->free();

        $this->assertNotEquals($row->insert_id, $address->id->value);
    }

    /**
     * @throws NotImplementedException
     */
    public function testTableName()
    {
    	$this->assertEquals('address', Address::getTableName());
	    $this->assertEquals('address', $this->address::getTableName());
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
		$this->assertStringContainsString($this->address->first_name->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->last_name->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->address1->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->city->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->state_id->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->zip->formatErrorLabel().' is required.', $error_msg);

		$this->address->first_name->value = 'Damien';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->first_name->formatErrorLabel().' is required.', $error_msg);
		$this->assertStringContainsString($this->address->last_name->formatErrorLabel().' is required.', $error_msg);

		$this->address->last_name->value = 'Barchowsky';
		try {
			$this->address->validateInput();
		} catch(ContentValidationException $e) { /* continue */ } catch (Exception $e) {
        }
        $error_msg = join('', $this->address->validationErrors);
		$this->assertStringNotContainsString($this->address->last_name->formatErrorLabel().' is required.', $error_msg);
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

	/**
	 * @throws Exception
	 */
	function testValidatePhoneNumbers()
	{
		$this->address->email->setAsNotRequired();
		$this->address->first_name->setAsNotRequired();
		$this->address->last_name->setAsNotRequired();
		$this->address->address1->setAsNotRequired();
		$this->address->city->setAsNotRequired();
		$this->address->state_id->setAsNotRequired();
		$this->address->zip->setAsNotRequired();

		$this->address->home_phone->setAsNotRequired();
		$this->address->home_phone->value = "";

		// test no validation error thrown on default data
		$this->assertNull($this->address->validateInput());

		// test bad format for non-required phone field
		$this->address->home_phone->value = "abc123";
		$this->assertContentValidationException($this->address);

		// test good format for non-required phone field
		$this->address->home_phone->value = "763 987 6754";
		$this->assertNull($this->address->validateInput());

		// test missing required field
		$this->address->home_phone->setAsRequired();
		$this->address->home_phone->value = "";
		$this->assertContentValidationException($this->address);

		// test bad format for required phone field
		$this->address->home_phone->value = "678 8765 323";
		$this->assertContentValidationException($this->address);

		// test good format for required phone field
		$this->address->home_phone->value = "763.987.6754";
		$this->assertNull($this->address->validateInput());
	}

    /**
     * @throws Exception
     */
    function testValidateEmailFormat()
    {
        $this->address->first_name->setAsNotRequired();
        $this->address->last_name->setAsNotRequired();
        $this->address->address1->setAsNotRequired();
        $this->address->city->setAsNotRequired();
        $this->address->state_id->setAsNotRequired();
        $this->address->zip->setAsNotRequired();

        // confirm no validation errors in this state
        $this->assertNull($this->address->validateInput());

        $this->address->email->setAsRequired();

        // confirm validation error with no email value
        $this->expectException(Exception::class);
        $this->address->validateInput();

        // test valid email address
        $this->address->email->value = 'dbarchowsky@gmail.com';
        $this->assertNull($this->address->validateInput());

        // test invalid email addresses
        $this->address->email->value = 'dbarchowsky';
        $this->expectException(Exception::class);
        $this->address->validateInput();

        $this->address->email->value = 'dbarchowsky@gmail';
        $this->expectException(Exception::class);
        $this->address->validateInput();

        $this->address->email->value = 'gmail.com';
        $this->expectException(Exception::class);
        $this->address->validateInput();

        // confirm error is thrown when email is not required but its format is invalid
        $this->address->email->setAsNotRequired();
        $this->address->email->value = "missing-domain";
        $this->expectException(Exception::class);
        $this->address->validateInput();

        // email not required; format of address is valid
        $this->address->email->value = 'dbarchowsky@gmail.com';
        $this->assertNull($this->address->validateInput());
    }

    /**
     * @throws ContentValidationException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public function disabled_testValidateUniqueEmailDefaultValue()
	{
        $this->address->validateUniqueEmail();
    }

    /**
     * @throws ContentValidationException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public function disabled_testValidateUniqueExistingEmail()
    {
    	$this->address->email->value = $this::TEST_EMAIL;
        $this->address->validateUniqueEmail();
    }

    /**
     * @throws ContentValidationException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    public function disabled_testValidateUniqueValidEmail()
	{
		$this->address->email->value = 'notindatabase@domain.com';
        $this->address->validateUniqueEmail();
    }
}