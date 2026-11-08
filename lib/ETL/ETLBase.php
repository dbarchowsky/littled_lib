<?php

namespace Littled\ETL;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use Littled\Log\Log;
use stdClass;


abstract class ETLBase extends MySQLConnection
{
    protected static string     $table;
    /** @var string[] */
    protected static array      $insertProperties;

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
     * @param mixed $expected
     * @param mixed $actual
     * @param string $propertyName
     * @param int $recordId
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    protected function assertPropertyMatches(mixed $expected, mixed $actual, string $propertyName, int $recordId): void
    {
        if ($expected !== $actual) {
            throw new FailedQueryException("$propertyName property values do not match for " . static::getTableName() . "record $recordId. $expected != $actual");
        }
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
     * @param int $recordId
     * @return stdClass[]
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    protected function fetchSampleRecord(int $recordId): array
    {
        $query = 'SELECT * FROM `' . static::getTableName() . '` WHERE id = ?';
        $legacy = $this->legacyDB->fetchRecords($query, 'i', $recordId);
        if (empty($legacy)) {
            throw new RecordNotFoundException(static::getTableName() . " record id $recordId not found in legacy database.");
        }
        $new = $this->fetchRecords($query, 'i', $recordId);
        if (empty($new)) {
            throw new RecordNotFoundException(static::getTableName() . " record id $recordId not found in new database.");
        }
        return [$legacy[0], $new[0]];
    }

    /**
     * @return int|null
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchMaxId(): int|null
    {
        $result = $this->legacyDB->fetchRecords('SELECT MAX(id) as maxId FROM `' . static::getTableName() . '`');
        return $result[0]->maxId ?? null;
    }

    /**
     * @return int|null
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchMinId(): int|null
    {
        $result = $this->legacyDB->fetchRecords('SELECT MIN(id) as minId FROM `' . static::getTableName() . '`');
        return $result[0]->minId ?? null;
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
     * Validates the data for a single record.
     * @param int $recordId
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    protected function validateRecordData(int $recordId): void
    {
        [$legacy, $new] = $this->fetchSampleRecord($recordId);
        foreach(static::$insertProperties as $p) {
            $this->assertPropertyMatches($legacy->$p, $new->$p, $p, $recordId);
        }
    }

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