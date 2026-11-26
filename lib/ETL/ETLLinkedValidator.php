<?php

namespace Littled\ETL;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\RecordNotFoundException;

class ETLLinkedValidator extends ETLValidator
{
    public string $parentId;
    public string $linkId;

    /**
     * @inheritDoc
     */
    protected function assertPropertyMatches(mixed $expected, mixed $actual, string $propertyName='', object|int|null $record=null): void
    {
        try {
            parent::assertPropertyMatches($expected, $actual);
        }
        catch (ContentValidationException $ex) {
            $msg = $ex->getMessage();
            if ($propertyName && $record) {
                $parentId = $record->{$this->parentId};
                $linkedId = $record->{$this->linkId};
                $msg = "Values do not match for $propertyName property of {$this->getTableName()} record $this->parentId $parentId / $this->linkId $linkedId. $msg";
            }
            throw new ContentValidationException($msg, $ex->getCode(), $ex);
        }
    }

    /**
     * @inheritDoc
     */
    protected function collectSampleRecordIds(): void
    {
        $src = [];
        $src[] = $this->pushSampleRecord('fetchMinId', 'First record.');
        $src[] = $this->pushSampleRecord('fetchRandomId', 'Random record.');
        $src[] = $this->pushSampleRecord('fetchRandomId', 'Random record.');
        $src[] = $this->pushSampleRecord('fetchRandomId', 'Random record.');
        $src[] = $this->pushSampleRecord('fetchMaxId', 'Last record.');

        foreach($src as $r) {
            if ($r) {
                $this->samples[] = new SampleRecord()
                    ->setLinkIds($r->{$this->parentId}, $r->{$this->linkId})
                    ->setDescription($r->description);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchMaxId(): int|object|null
    {
        $query = "SELECT `$this->parentId`, `$this->linkId` " .
            "FROM `{$this->getTableName()}` " .
            "WHERE `$this->parentId` = (" .
            "SELECT MAX(`$this->parentId`) " .
            "FROM `{$this->getTableName()}`)";
        $result = $this->legacyDB->fetchRecords($query);
        if (empty($result)) {
            return null;
        }
        return $result[0];
    }

    /**
     * @inheritDoc
     */
    public function fetchMinId(): int|object|null
    {
        $query = "SELECT `$this->parentId`, `$this->linkId` " .
            "FROM `{$this->getTableName()}` " .
            "WHERE `$this->parentId` = (" .
            "SELECT MIN(`$this->parentId`) " .
            "FROM `{$this->getTableName()}`)";
        $result = $this->legacyDB->fetchRecords($query);
        if (empty($result)) {
            return null;
        }
        return $result[0];
    }

    /**
     * @inheritDoc
     */
    protected function fetchSampleRecord(int|object $record): array
    {
        $legacy = $this->legacyDB->fetchRecords($this->formatLegacyRecordQuery(), 'ii', $record->{$this->parentId}, $record->{$this->linkId});
        if (empty($legacy)) {
            throw new RecordNotFoundException("{$this->getTableName()} record id $record not found in legacy database.");
        }
        $new = $this->fetchRecords($this->formatNewRecordQuery(), 'ii', $record->{$this->parentId}, $record->{$this->linkId});
        if (empty($new)) {
            throw new RecordNotFoundException("{$this->getTableName()} record id $record not found in new database.");
        }
        return [$legacy[0], $new[0]];
    }

    /**
     * @return string
     * @throws ConfigurationUndefinedException
     */
    protected function formatLegacyRecordQuery(): string
    {
        return "SELECT * FROM `{$this->getTableName()}` WHERE `$this->parentId` = ? AND `$this->linkId` = ?";
    }

    /**
     * @inheritDoc
     */
    protected function formatRandomIdQuery(): string
    {
        return "PREPARE stmt FROM 'SELECT `$this->parentId`, `$this->linkId` " .
            "FROM `{$this->getTableName()}` " .
            "ORDER BY `$this->parentId` LIMIT 1 OFFSET ?'";
    }

    /**
     * Calls method on the current object to retrieve a sample record and adds the description as a property of the returned object.
     * @param string $method
     * @param string $description
     * @return int|object|null
     */
    protected function pushSampleRecord(string $method, string $description): int|object|null
    {
        $r = $this->$method();
        if(is_object($r)) {
            $r->description = $description;
        }
        return $r;
    }

    /**
     * @inheritDoc
     */
    public function validateMigration(bool $verbose = false): void
    {
        $this->validateRecordCount($verbose);
        $this->collectSampleRecordIds();
        foreach($this->samples as $sample) {
            $this->validateRecordData((object)[
                $this->parentId => $sample->parentId,
                $this->linkId => $sample->linkId,
            ]);
            if ($verbose) {
                print "Confirmed {$this->getTableName()} record $this->parentId $sample->parentId / $this->linkId $sample->linkId data matches. " .
                    ($sample->description ?? '' ?: '') . "\n";
            }
        }
    }
}
