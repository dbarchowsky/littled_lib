<?php
namespace Littled\Tests\Filters;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use PHPUnit\Framework\TestCase;
use Exception;

class FilterCollectionTestBase extends TestCase
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
        $conn->query('DEL'.'ETE FROM `'.self::TEST_TABLE.'` WHERE `name` LIKE \'%'.self::TEST_RECORD_LABEL.'%\'');
        $conn->closeDatabaseConnection();
        unset($conn);
    }
}