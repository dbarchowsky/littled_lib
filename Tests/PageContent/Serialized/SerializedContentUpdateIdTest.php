<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use LittledTests\DataProvider\PageContent\Serialized\SerializedContentUpdateIdTestData;
use LittledTests\TestHarness\PageContent\Serialized\TestTableTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\TestTableWithoutUpdateProcTestHarness;
use PHPUnit\Framework\TestCase;

class SerializedContentUpdateIdTest extends TestCase
{
    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\SerializedContentUpdateIdTestDataProvider::updateIdAfterCommitWithInsertQueryTestProvider()
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws ContentValidationException
     */
    public function testUpdateIdAfterCommitWithInsertQuery(SerializedContentUpdateIdTestData $data)
    {
        // setup
        $query = ($data->use_lowercase ? 'ins'.'ert into ' : 'INS'.'ERT INTO ');
        $query .= '`'.TestTableTestHarness::getTableName().'` '.
            '(`id`, `name`, `int_col`, `slot`) VALUES (?,?,?,?)'.
            ' ON DUPLICATE KEY UPDATE `name` = ?, `int_col` = ?, `slot` = ?';
        $start_ids = $this::getRecordIdList();

        // insert new record with query
        $start_record_id = $data->o->id->value;
        $data->o->query($query, 'iisiisi',
            $data->o->id->value,
            $data->o->name->value, $data->o->int_col->value, $data->o->slot->value,
            $data->o->name->value, $data->o->int_col->value, $data->o->slot->value);
        static::runInsertTests($data, $query, $start_ids, $start_record_id);

        // cleanup
        if ($data->expect_insert) {
            $data->o->delete();
        }
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\SerializedContentUpdateIdTestDataProvider::updateIdAfterCommitWithProcedureTestProvider()
     * @param SerializedContentUpdateIdTestData $data
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     */
    public function testUpdateIdAfterCommitWithProcedure(SerializedContentUpdateIdTestData $data)
    {
        // setup
        $args = $data->o->generateUpdateQuery();
        if ($data->use_lowercase) {
            $args[0] = strtolower(substr($args[0], 0, 5)).substr($args[0], 5);
        }
        $start_record_id = $data->o->id->value;
        $start_ids = static::getRecordIdList();

        // run query
        $data->o->prepareInsertIdSession_public();
        $data->o->query(...$args);
        static::runInsertTests($data, $args[0], $start_ids, $start_record_id);

        // cleanup
        if ($data->expect_insert) {
            $data->o->delete();
        }
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws InvalidValueException
     * @throws RecordNotFoundException
     */
    public function testUpdateWithoutProcedure()
    {
        $o = new TestTableWithoutUpdateProcTestHarness();
        $o->name->value = 'foo';

        $start_ids = static::getRecordIdList();
        $o->save();

        self::assertGreaterThan(0, $o->id->value);
        self::assertNotContains($o->id->value, $start_ids);

        // cleanup
        $o->delete();
    }

    /**
     * @return array
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     */
    protected static function getRecordIdList(): array
    {
        $query = 'SEL'.'ECT `id` FROM `'.TestTableTestHarness::getTableName().'`';
        $conn = new MySQLConnection();
        $result = $conn->fetchRecords($query);
        return array_map(fn($e): int => $e->id, $result);
    }

    /**
     * @return mixed
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     */
    protected static function getRecordCount(): int
    {
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.TestTableTestHarness::getTableName().'`';
        $conn = new MySQLConnection();
        $result = $conn->fetchRecords($query);
        return $result[0]->count;
    }

    /**
     * @param SerializedContentUpdateIdTestData $data
     * @param string $query
     * @param array $start_ids
     * @param ?int $start_record_id
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     */
    protected static function runInsertTests(
        SerializedContentUpdateIdTestData $data,
        string $query,
        array $start_ids,
        ?int $start_record_id)
    {
        $data->o->updateIdAfterCommit_public($query);
        if ($data->expect_insert) {
            self::assertGreaterThan(0, $data->o->id->value);
            self::assertNotContains($data->o->id->value, $start_ids);
        }
        else {
            self::assertEquals($start_record_id, $data->o->id->value);
            self::assertEqualsCanonicalizing($start_ids, static::getRecordIdList());
        }
    }
}