<?php
namespace Littled\Tests\Filters;
require_once (realpath(dirname(__FILE__)).'/../bootstrap.php');

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Tests\Filters\Samples\FilterCollectionChild;
use Littled\Tests\Filters\Samples\FilterCollectionChildWithProcedure;
use Littled\Tests\Filters\Samples\FilterCollectionChildWithQuery;
use PHPUnit\Framework\TestCase;
use Exception;

class FilterCollectionTest extends TestCase
{
    public const TEST_RECORD_LABEL = 'FilterCollection Unit Test';
    public const TEST_TABLE = 'test_table';

    /**
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $conn = new MySQLConnection();
        $conn->connectToDatabase();
        $mysqli = $conn->getMysqli();
        $query = 'INS'.'ERT INTO `'.self::TEST_TABLE.'` (`name`, `int_col`, `bool_col`, `date`, `slot`) VALUES (?,?,?,?,?)';
        $stmt = $mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception('Error preparing statement. '.$mysqli->error);
        }
        $name = $date = $int_col = $bool_col = $slot = null;
        $stmt->bind_param('siisi', $name, $int_col, $bool_col, $date, $slot);
        for($slot=0; $slot<5; $slot++) {
            $name = self::TEST_RECORD_LABEL.' '.sprintf('%02d', $slot);
            $date = date('Y-m-d h:i:s');
            $int_col = $slot * 11;
            $bool_col = $slot % 2;
            $stmt->execute();
        }
        $conn->closeDatabaseConnection();
        unset($conn);
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $conn = new MySQLConnection();
        $conn->query('DE'.'LETE FROM `'.self::TEST_TABLE.'` WHERE `name` LIKE \'%'.self::TEST_RECORD_LABEL.'%\'');
        $conn->closeDatabaseConnection();
        unset($conn);
    }

    function testFormatListingsQueryNotImplemented()
    {
        // Test when not implemented in child class
        $fc = new FilterCollectionChild();
        $args = $fc->formatListingsQueryTest();
        $this->assertCount(3, $args);
        $this->assertEquals('', $args[0]);  /* query string */
        $this->assertEquals('', $args[1]);  /* types descriptor */
        $this->assertNull($args[2]);                /* start of variables to bind to query */
    }

    /**
     * @return void
     */
    function testFormatListingsQueryUsingProcedure()
    {
        $fc = new FilterCollectionChildWithProcedure();
        $args = $fc->formatListingsQuery();
        $this->assertCount(9, $args);
        $this->assertMatchesRegularExpression('/^CALL testTableListingsSelect\(/', $args[0]);
        $this->assertEquals('iisiiss', $args[1]);
        $this->assertNull($args[2]); /* page filter, the first filter value in the list */
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListings()
    {
        $fc = new FilterCollectionChildWithProcedure();
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        $row = $data[0];
        $this->assertIsString($row->name);
        $this->assertGreaterThan(0, $fc->record_count);
    }

    /**
     * @return void
     * @throws Exception
     */
    function testRetrieveListingsWithQuery()
    {
        $fc = new FilterCollectionChildWithQuery();

        // no filters
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));
        $this->assertGreaterThan(0, $fc->record_count);

        // filter that matches some records
        $fc->name_filter->value = 'unit';
        $data = $fc->retrieveListings();
        $this->assertGreaterThan(0, count($data));

        // filter that matches no records
        $fc->name_filter->value = 'string that does not match';
        $data = $fc->retrieveListings();
        $this->assertCount(0, $data);
    }
}