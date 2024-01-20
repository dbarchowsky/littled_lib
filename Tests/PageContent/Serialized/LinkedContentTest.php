<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentUninitializedTestHarness;
use PHPUnit\Framework\TestCase;
use Exception;

class LinkedContentTest extends TestCase
{
    protected static bool $initialized = false;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (!static::$initialized) {
            $o = new LinkedContentTestHarness();
            $o->setUpTestData();
            static::$initialized = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function tearDownAfterClass(): void
    {
        $o = new LinkedContentTestHarness();
        $o->tearDownTestData();
    }

    /**
     * @throws NotImplementedException|NotInitializedException
     * @throws ContentValidationException|InvalidQueryException
     * @throws Exception
     */
    public function testDeleteStaleLinks()
    {
        $primary_id = 45;
        $o = new LinkedContentTestHarness();

        // retrieve the initial count
        $original_count = static::getLinkCount($primary_id);
        self::assertEquals(0, $original_count);

        // populate with test link records
        $o->setPrimaryId($primary_id)->setForeignID(108)->save();
        $o->setForeignID(109)->save();
        $o->setForeignID(13)->save();
        self::assertEquals($original_count+3, static::getLinkCount($primary_id));

        // remove link to FK with value of 109
        $o->deleteStaleLinks([108,13]);
        self::assertEquals($original_count+2, static::getLinkCount($primary_id));

        // clean up
        $query = 'DEL'.'ETE FROM `'.LinkedContentTestHarness::getTableName().'` WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ?';
        $o->query($query, 'i', $primary_id);
    }

    /**
     * @throws NotImplementedException
     */
    public function testFormatRecordLookupQuery()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(3);
        $o->foreign_id->setInputValue(23);
        $expected = '/^SELECT .*`'.LinkedContentTestHarness::getTableName().
            '` WHERE `'.$o->primary_id->getColumnName('primary_id').'` = \? '.
            'AND `'.$o->foreign_id->getColumnName('foreign_id').'` = \?/';
        list($query, $arg_types, $args) =
            $o->formatRecordLookupQuery_public(
                'SEL'.'ECT COUNT(1) AS `count` FROM `'.LinkedContentTestHarness::getTableName().'`');
        self::assertMatchesRegularExpression($expected, $query);
        self::assertEquals('ii', $arg_types);
        self::assertIsArray($args);
        self::assertCount(2, $args);
        self::assertContains(3, $args);
        self::assertContains(23, $args);
    }

    public function testGetLinkedProperties()
    {
        $o = new LinkedContentTestHarness();
        $properties = $o->getLinkedProperties_public();
        self::assertIsArray($properties);
        self::assertGreaterThan(0, count($properties));
        self::assertContains('primary_id', $properties);
        self::assertContains('foreign_id', $properties);
        self::assertNotContains('label', $properties);
    }

    /**
     * @throws Exception
     */
    public function testInsertUpdateAndDelete()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::CREATE_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::CREATE_LINK_IDS['parent2']);

        // confirm there is no pre-existing record
        $result = static::lookupRecord($o);
        self::assertEquals(0, $result[0]->count);

        $o->save();

        // confirm record after saving it
        $result = static::lookupRecord($o);
        self::assertEquals(1, $result[0]->count);

        $result = static::lookupLabel($o);
        self::assertCount(1, $result);
        self::assertEquals('', $result[0]->label);

        $label = 'my label';
        $o->label->setInputValue($label);
        $o->save();

        // confirm the existing record was updated
        $result = static::lookupRecord($o);
        self::assertEquals(1, $result[0]->count);

        // confirm that the new label value was saved
        $result = static::lookupLabel($o);
        self::assertCount(1, $result);
        self::assertEquals($label, $result[0]->label);

        $o->delete();

        // confirm that the record has been removed
        $result = static::lookupRecord($o);
        self::assertEquals(0, $result[0]->count);
    }

    /**
     * @throws Exception
     */
    public function testRead()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent2']);
        $o->read();
        self::assertEquals(LinkedContentTestHarness::EXISTING_LINK_IDS['label'], $o->label->value);
    }

    /**
     * @return void
     * @throws NotImplementedException|Exception
     */
    public function testReadNonexistentRecord()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2']);
        try {
            $o->read();
            self::fail('Expected RecordNotFoundException not thrown.');
        } catch(RecordNotFoundException $e) {
            self::assertMatchesRegularExpression('/'.LinkedContentTestHarness::getTableName().'.* not found/', $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testRecordExists()
    {
        $o = new LinkedContentTestHarness();
        $o->primary_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->foreign_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent2']);
        self::assertTrue($o->recordExists());

        $o->primary_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1']);
        self::assertFalse($o->recordExists());
    }

    /**
     * @throws NotInitializedException
     */
    public function testSetForeignId()
    {
        $o = new LinkedContentTestHarness();
        self::assertNull($o->foreign_id->value);
        $new_id = 14;
        $o->setForeignID($new_id);
        self::assertEquals($new_id, $o->foreign_id->value);
    }

    public function testSetForeignIdWhenUninitialized()
    {
        $o = new LinkedContentUninitializedTestHarness();
        try {
            $o->setForeignID(12);
            self::fail('Expected NotInitializedException not thrown.');
        }
        catch(NotInitializedException $e) {
            $expected = '/foreign.* not initialized/i';
            self::assertMatchesRegularExpression($expected, $e->getMessage());
        }
    }

    public function testSetPrimaryIdWhenUninitialized()
    {
        $o = new LinkedContentUninitializedTestHarness();
        try {
            $o->setPrimaryId(25);
            self::fail('Expected NotInitializedException not thrown.');
        }
        catch(NotInitializedException $e) {
            $expected = '/primary.* not initialized/i';
            self::assertMatchesRegularExpression($expected, $e->getMessage());
        }
    }

    /**
     * @throws NotInitializedException
     */
    public function testSetPrimaryId()
    {
        $o = new LinkedContentTestHarness();
        self::assertNull($o->primary_id->value);
        $new_id = 12;
        $o->setPrimaryId($new_id);
        self::assertEquals($new_id, $o->primary_id->value);
    }

    /**
     * @param ?int $primary_id
     * @return int
     * @throws NotImplementedException
     * @throws Exception
     */
    public static function getLinkCount(?int $primary_id=null): int
    {
        $o = new LinkedContentTestHarness();
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.LinkedContentTestHarness::getTableName().'`';
        if ($primary_id === NULL) {
            $result = $o->fetchRecords($query);
        } else {
            $query .= ' WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ?';
            $result = $o->fetchRecords($query, 'i', $primary_id);
        }
        return $result[0]->count;
    }

    /**
     * @throws Exception
     */
    protected static function lookupRecord(LinkedContentTestHarness $o): array
    {
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.$o::getTableName().'` '.
            'WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ? '.
            'AND `'.$o->foreign_id->getColumnName('foreign_id').'` = ?';
        return $o->fetchRecords($query, 'ii', $o->primary_id->value, $o->foreign_id->value);
    }

    /**
     * @throws NotImplementedException
     * @throws Exception
     */
    protected static function lookupLabel(LinkedContentTestHarness $o): array
    {
        $query = 'SEL'.'ECT `label` FROM `'.$o::getTableName().'` '.
            'WHERE `'.$o->primary_id->getColumnName('primary_id').'` = ? '.
            'AND `'.$o->foreign_id->getColumnName('foreign_id').'` = ?';
        return $o->fetchRecords($query, 'ii', $o->primary_id->value, $o->foreign_id->value);
    }
}