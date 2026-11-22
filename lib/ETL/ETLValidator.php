<?php

namespace Littled\ETL;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use stdClass;


class ETLValidator extends ETLConnector
{
    /** @var string[] */
    protected static array      $insertProperties;
    /** @var SampleRecord[] */
    protected array             $samples = [];

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
            throw new FailedQueryException("Values do not match for $propertyName property of " . static::getTableName() . " record $recordId. $actual does not match expected $expected.");
        }
    }

    /**
     * Compare two values to see if they are the same.
     * @param mixed $expected
     * @param mixed $actual
     * @param string $msg
     * @return void
     * @throws ContentValidationException
     */
    public function assertSame(mixed $expected, mixed $actual, string $msg=''): void
    {
        if ($expected !== $actual) {
            throw new ContentValidationException(($msg ? "$msg\n" : '') . "Actual value $actual does not match expected $expected.");
        }
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    protected function collectSampleRecordIds(): void
    {
        $this->samples = [
            new SampleRecord()->setRecordId($this->fetchMinId())->setDescription('First record.'),
            new SampleRecord()->setRecordId($this->fetchRandomId())->setDescription('Random record.'),
            new SampleRecord()->setRecordId($this->fetchRandomId())->setDescription('Random record.'),
            new SampleRecord()->setRecordId($this->fetchRandomId())->setDescription('Random record.'),
            new SampleRecord()->setRecordId($this->fetchMaxId())->setDescription('Last record.'),
        ];
        $this->samples = array_filter($this->samples, static function ($record) {
            return $record->recordId > 0;
        });
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
     * Fetches a random record id from the table in the old database.
     * @return int|null
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchRandomId(): int|null
    {
        $this->legacyDB->query('SELECT COUNT(*) INTO @row_count FROM `' . static::getTableName() . '`');
        $this->legacyDB->query('SET @offset := FLOOR(RAND() * @row_count)');
        $this->legacyDB->query('PREPARE stmt FROM \'SELECT id FROM `' . static::getTableName() . '` LIMIT 1 OFFSET ?\'');
        $result = $this->legacyDB->fetchRecords('EXECUTE stmt USING @offset');
        $this->legacyDB->query('DEALLOCATE PREPARE stmt');
        return $result[0]->id ?? null;
    }

    /**
     * Compares data from the old database against the data in the new database.
     * @param bool $verbose
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function validateMigration(bool $verbose = false): void
    {
        foreach($this->samples as $sample) {
            $this->validateRecordData($sample->recordId);
            if ($verbose) {
                print 'Confirmed ' . static::getTableName() . " record $sample->recordId data matches. " .
                    ($sample->description ?? '' ?: '') . "\n";
            }
        }
    }

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
}