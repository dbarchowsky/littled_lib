<?php
namespace Littled\Tests\Database;

use Exception;
use Littled\Database\DBUtils;
use PHPUnit\Framework\TestCase;


class DBUtilsTest extends TestCase
{
    /**
     * @throws Exception
     */
    function testLookupNextAvailableRecordId()
    {
        $table = 'test_table';
        $expected_id = 2024;
        $this->assertEquals($expected_id, DBUtils::lookupNextAvailableRecordId($table));
    }
}