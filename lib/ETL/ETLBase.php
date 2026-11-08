<?php

namespace Littled\ETL;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Log\Log;


abstract class ETLBase extends MySQLConnection
{
    protected static string     $table;

    public int                  $chunkSize = 500;
    protected MySQLConnection   $legacyDB;
    public int                  $offset = 0;

    /**
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->connectToLegacy();
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    protected function connectToLegacy(): void
    {
        $this->legacyDB = new MySQLConnection()
            ->withSharedConnection(false)
            ->connectToDatabase(
                $_ENV['MYSQL_LEGACY_HOST'],
                $_ENV['MYSQL_USER'],
                $_ENV['MYSQL_PASS'],
                $_ENV['MYSQL_SCHEMA'],
                $_ENV['MYSQL_PORT']);
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public static function getTableName(): string
    {
        if (empty(static::$table)) {
            throw new ConfigurationUndefinedException('Table name not set in ' . Log::getClassBaseName(static::class));
        }
        return static::$table;
    }

    /**
     * Fetches one batch of data from the table in the old database
     * @return array
     * @throws FailedQueryException
     */
    public function fetch(): array
    {
        $result = $this->legacyDB->fetchRecords($this->formatFetchQuery(), 'ii', $this->chunkSize, $this->offset);
        $this->offset += $this->chunkSize;
        return $result;
    }

    /**
     * Returns the query to use to fetch data from the table in the old database.
     * @return string
     */
    protected function formatFetchQuery(): string
    {
        return 'SELECT * FROM ' . static::$table . ' LIMIT ? OFFSET ?';
    }

    /**
     * Returns the query to use to insert data into the table in the new database.
     * @return string[]
     */
    abstract protected function formatInsertQuery(): array;

    /**
     * Inserts data into the table in the new database.
     * @param object|array $data
     * @return void
     * @throws FailedQueryException
     */
    public function insert(object|array $data): void
    {
        if (is_object($data)) {
            $args = array_values((array)$data);
        } else {
            $args = $data;
        }
        $this->query(...$this->formatInsertQuery(), ...$args);
    }

    /**
     * Returns a row of data suitable for insertion into the new database.
     * @param object|array $data
     * @return array
     */
    public function transform(object|array $data): array
    {
        // defaults to no transformation other than casting to array
        if (is_object($data)) {
            $src = (array)$data;
        } else {
            $src= $data;
        }

        $new = [];
        foreach($src as $row) {
            if ($this->validateRow($row)) {
                $new[] = $this->transformRow($row);
            }
        }
        return $new;
    }

    /**
     * Transforms the data within a row before insertion into the new database.
     * @param object $row
     * @return object
     */
    protected function transformRow(object $row): object
    {
        return $row;
    }

    /**
     * Compares data from the old database against the data in the new database.
     * @return void
     */
    public function validateMigration(): void {}

    /**
     * Validate data within a row before insertion into new database.
     * @param object $row
     * @return bool
     */
    protected function validateRow(object $row): bool
    {
        return count((array)$row) > 0;
    }
}