<?php
namespace Littled\Tests\Filters;

use Littled\App\LittledGlobals;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\NotImplementedException;
use Littled\Tests\Filters\TestHarness\FilterCollectionChild;
use Littled\Tests\Filters\TestHarness\FilterCollectionChildWithProcedure;
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
        for($slot=1000; $slot<1005; $slot++) {
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

    /**
     * @throws NotImplementedException
     */
    function testCollectFilterValues_ReferringURI()
    {
        $o = new FilterCollectionChild();
        $o->collectFilterValues();
        $this->assertEquals('', $o->referer_uri);

        $_POST[LittledGlobals::REFERER_KEY] = 'https://localhost';
        $o->collectFilterValues();
        $this->assertEquals('https://localhost', $o->referer_uri);
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
}