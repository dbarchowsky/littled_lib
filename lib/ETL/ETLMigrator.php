<?php

namespace Littled\ETL;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\FailedQueryException;


abstract class ETLMigrator extends ETLConnector
{
    public int                  $chunkSize = 500;
    public int                  $offset = 0;

    /**
     * Fetches one batch of data from the table in the old database
     * @return array
     * @throws ConfigurationUndefinedException
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
     * @throws ConfigurationUndefinedException
     */
    protected function formatFetchQuery(): string
    {
        return "SELECT * FROM `{$this->getTableName()}` LIMIT ? OFFSET ?";
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
            $args = array_values($data);
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
     * Validate data within a row before insertion into new database.
     * @param object $row
     * @return bool
     */
    protected function validateRow(object $row): bool
    {
        return count((array)$row) > 0;
    }
}