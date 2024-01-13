<?php

namespace LittledTests\Database;


use Littled\Database\MySQLConnection;
use Littled\Exception\InvalidQueryException;
use PHPUnit\Framework\TestCase;
use Exception;

class MySQLConnectionTest extends TestCase
{
    /**
     * @throws Exception
     */
    function testFetchOptions()
    {
        $conn = new MySQLConnection();
        $query = "SELECT `id`, `name` AS `option` FROM test_table where LOWER(`name`) LIKE '%hello'";
        $data = $conn->fetchOptions($query);
        $this->assertGreaterThan(0, count($data));
        $this->assertArrayHasKey(2215, $data);
        $this->assertEquals('hello', $data[2215]);
        $this->assertArrayHasKey(2216, $data);
        $this->assertEquals('hello hello', $data[2216]);
    }

    /**
     * @throws Exception
     */
    function testFetchOptionsWithInvalidColumns()
    {
        $conn = new MySQLConnection();
        $query = "SELECT `id`, `name` AS `invalid` FROM test_table";
        try {
            $data = $conn->fetchOptions($query);
            $this->fail('Expected InvalidQueryException not thrown.');
        }
        catch(InvalidQueryException $e) {
            $this->assertStringContainsString('Invalid query', $e->getMessage());
        }
    }
}