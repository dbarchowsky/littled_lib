<?php

namespace Littled\ETL;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\FailedQueryException;
use Littled\Exception\RecordNotFoundException;
use stdClass;


class ETLValidator extends MySQLConnection
{
    use ETLConnectorTrait;

    /** @var string[] */
    protected array             $insertProperties;
    /** @var SampleRecord[] */
    protected array             $samples = [];

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param string $propertyName
     * @param int|object|null $record
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     */
    protected function assertPropertyMatches(mixed $expected, mixed $actual, string $propertyName='', int|object|null $record=null): void
    {
        if ($expected !== $actual) {
            $msg = "$actual does not match expected $expected.";
            if ($propertyName && $record) {
                $msg = "Values do not match for $propertyName property of {$this->getTableName()} record $record. $msg";
            }
            throw new ContentValidationException($msg);
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
     * Insert properties getter.
     * @return array|string[]
     */
    public function getInsertProperties(): array
    {
        return $this->insertProperties ?? [];
    }

    /**
     * Returns the number of records in the legacy database.
     * @return int
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchLegacyRecordCount(): int
    {
        return $this->legacyDB->fetchRecords("SELECT COUNT(*) as `count` FROM `{$this->getTableName()}`")[0]->count ?? 0;
    }

    /**
     * @return int|object|null
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchMaxId(): int|object|null
    {
        $result = $this->legacyDB->fetchRecords("SELECT MAX(id) as maxId FROM `{$this->getTableName()}`");
        return $result[0]->maxId ?? null;
    }

    /**
     * @return int|stdClass|null
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchMinId(): int|object|null
    {
        $result = $this->legacyDB->fetchRecords("SELECT MIN(id) as minId FROM `{$this->getTableName()}`");
        return $result[0]->minId ?? null;
    }

    /**
     * Returns the expected number of records in the new database.
     * @return int
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchNewRecordCount(): int
    {
        return $this->fetchRecords("SELECT COUNT(*) as count FROM `{$this->getTableName()}`")[0]->count ?? 0;
    }

    /**
     * Fetches a random record id from the table in the old database.
     * @return int|object|null
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     */
    public function fetchRandomId(): int|object|null
    {
        $this->legacyDB->query("SELECT COUNT(*) INTO @row_count FROM `{$this->getTableName()}`");
        $this->legacyDB->query('SET @offset := FLOOR(RAND() * @row_count)');
        $this->legacyDB->query($this->formatRandomIdQuery());
        $result = $this->legacyDB->fetchRecords('EXECUTE stmt USING @offset');
        $this->legacyDB->query('DEALLOCATE PREPARE stmt');
        if (empty($result)) {
            return null;
        }
        if (property_exists($result[0], 'id')) {
            return $result[0]->id;
        }
        return $result[0];
    }

    /**
     * @param int|object $record
     * @return stdClass[]
     * @throws ConfigurationUndefinedException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    protected function fetchSampleRecord(int|object $record): array
    {
        $legacy = $this->legacyDB->fetchRecords($this->formatLegacyRecordQuery(), 'i', $record);
        if (empty($legacy)) {
            throw new RecordNotFoundException("{$this->getTableName()} record id $record not found in legacy database.");
        }
        $new = $this->fetchRecords($this->formatNewRecordQuery(), 'i', $record);
        if (empty($new)) {
            throw new RecordNotFoundException("{$this->getTableName()} record id $record not found in new database.");
        }
        return [$legacy[0], $new[0]];
    }

    /**
     * Fetch selected record from legacy database.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    protected function formatLegacyRecordQuery(): string
    {
        return "SELECT * FROM `{$this->getTableName()}` WHERE id = ?";
    }

    /**
     * Retrieve selected record from new database.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    protected function formatNewRecordQuery(): string
    {
        return $this->formatLegacyRecordQuery();
    }

    /**
     * Formats query used to retrieve a random record id from the table in the old database.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    protected function formatRandomIdQuery(): string
    {
        return "PREPARE stmt FROM 'SELECT id FROM `{$this->getTableName()}` LIMIT 1 OFFSET ?'";
    }

    /**
     * Insert properties setter.
     * @param array $insertProperties
     * @return $this
     */
    public function setInsertProperties(array $insertProperties): static
    {
        $this->insertProperties = $insertProperties;
        return $this;
    }

    /**
     * Compares data from the old database against the data in the new database.
     * @param bool $verbose
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    public function validateMigration(bool $verbose = false): void
    {
        $this->validateRecordCount($verbose);
        $this->collectSampleRecordIds();
        foreach($this->samples as $sample) {
            $this->validateRecordData($sample->recordId);
            if ($verbose) {
                print "Confirmed {$this->getTableName()} record $sample->recordId data matches. " .
                    ($sample->description ?? '' ?: '') . "\n";
            }
        }
    }

    /**
     * Confirms the record count in the new table matches the expected count.
     * @param bool $verbose
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     */
    protected function validateRecordCount(bool $verbose = false): void
    {
        $expected = $this->fetchLegacyRecordCount();
        static::assertSame($expected, $this->fetchNewRecordCount(), "Expected record count in new table does not match expected $expected.");
        if ($verbose) {
            print "Confirmed record count in new table matches expected $expected.\n";
        }
    }

    /**
     * Validates the data for a single record.
     * @param int|object $record
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws FailedQueryException
     * @throws RecordNotFoundException
     */
    protected function validateRecordData(int|object $record): void
    {
        [$legacy, $new] = $this->fetchSampleRecord($record);
        $insertProperties =
            $this->insertProperties ??
            array_keys(get_object_vars($legacy));
        foreach($insertProperties as $p) {
            $this->assertPropertyMatches($legacy->$p, $new->$p, $p, $record);
        }
    }
}