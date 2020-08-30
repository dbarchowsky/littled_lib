<?php
namespace Littled\Tests\Account;

require_once(realpath(dirname(__FILE__)) . "/../../keys/littledamien/keys.php");

use Littled\Account\Address;
use Littled\Exception\RecordNotFoundException;
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
    const TEST_URL_SIZE = 255;
    const TEST_LAST_NAME_VALUE = 'Schutz';
    const TEST_STATE_VALUE = 'Oregon';
    const TEST_STATE_ABBREV_VALUE = 'OR';

    /** @var $addr Address */
    public $addr;

    /**
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
        $this->addr = new Address();
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
        self::assertEquals('123 Some Lane, London, UK', $this->addr->formatOneLineAddress());

        $this->addr->city->value = '';
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

    public function testInitialValues()
    {
        $addr = new Address();
        self::assertInstanceOf('Littled\Request\IntegerInput', $addr->id);
        self::assertInstanceOf('Littled\Request\StringTextField', $addr->province);
        self::assertEquals(AddressTest::TEST_ADDR2_SIZE, $addr->address2->sizeLimit);
        self::assertEquals(AddressTest::TEST_URL_SIZE, $addr->url->sizeLimit);
    }

    public function testRead()
    {
        $addr = new Address();
        $addr->id->value = AddressTest::TEST_ID_VALUE;
        try {
            $addr->read();
        }
        catch (Exception $e)
        {
            print ("Exception: {$e}");
        }
        self::assertEquals(AddressTest::TEST_LAST_NAME_VALUE, $addr->lastname->value);
        self::assertEquals(AddressTest::TEST_STATE_VALUE, $addr->state);
        self::assertEquals(AddressTest::TEST_STATE_ABBREV_VALUE, $addr->state_abbrev);
    }

    public function testReadNonexistentRecord()
    {
        $addr = new Address();
        $addr->id->value = AddressTest::TEST_NONEXISTENT_ID_VALUE;
        self::expectException(RecordNotFoundException::class);
        $addr->read();
    }
}