<?php

namespace LittledTests\Database;


use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use PHPUnit\Framework\TestCase;
use Exception;

class MySQLConnectionTest extends TestCase
{
    /**
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    public function testFetchOptionsWithArgs()
    {
        $conn = new MySQLConnection();
        $args = ['%hello'];
        $query = 'SEL'.'ECT `id`, `name` AS `option` FROM test_table where LOWER(`name`) LIKE ?';
        $this->confirmFetchOptionsResult(
            $conn->fetchOptions($query, 's', ...$args)
        );
    }

    /**
     * @throws Exception
     */
    public function testFetchOptionsWithInvalidColumns()
    {
        $conn = new MySQLConnection();
        $query = 'SEL' . 'ECT `id`, `name` AS `invalid` FROM test_table';
        try {
            $conn->fetchOptions($query);
            self::fail('Expected InvalidQueryException not thrown.');
        } catch (InvalidQueryException $e) {
            self::assertStringContainsString('Invalid query', $e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    public function testFetchOptionsWithoutArgs()
    {
        $conn = new MySQLConnection();
        $query = "SELECT `id`, `name` AS `option` FROM test_table where LOWER(`name`) LIKE '%hello'";
        $this->confirmFetchOptionsResult(
            $conn->fetchOptions($query));
    }

    /**
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    public function testFetchRecordsWithArgs()
    {
        $expected = 56;
        $args = ['%hello'];
        $conn = new MySQLConnection();
        $query = 'SEL' . 'ECT `id`, `name`, `int_col`, `bool_col` FROM test_table where LOWER(`name`) LIKE ?';
        $this->confirmFetchRecordsResult($expected,
            $conn->fetchRecords($query, 's', ...$args));
    }

    /**
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     */
    public function testFetchRecordsWithoutArgs()
    {
        $expected = 56;
        $conn = new MySQLConnection();
        $query = 'SEL' . 'ECT `id`, `name`, `int_col`, `bool_col` FROM test_table where LOWER(`name`) LIKE \'%hello\'';
        $this->confirmFetchRecordsResult($expected,
            $conn->fetchRecords($query));
    }

    protected function confirmFetchOptionsResult(array $data)
    {
        self::assertGreaterThan(0, count($data));
        self::assertArrayHasKey(2215, $data);
        self::assertEquals('hello', $data[2215]);
        self::assertArrayHasKey(2216, $data);
        self::assertEquals('hello hello', $data[2216]);
    }

    protected function confirmFetchRecordsResult($expected, array $data)
    {
        $found_match = false;
        self::assertGreaterThan(0, count($data));
        foreach ($data as $row) {
            if ((int)$row->int_col === $expected) {
                $found_match = $row->int_col;
                break;
            }
        }
        self::assertEquals($expected, $found_match);
    }
}