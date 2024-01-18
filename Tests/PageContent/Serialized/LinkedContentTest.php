<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContentTestHarness;
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
     * @throws NotImplementedException
     */
    public function testFormatRecordLookupQuery()
    {
        $o = new LinkedContentTestHarness();
        $o->parent1_id->setInputValue(3);
        $o->parent2_id->setInputValue(23);
        $expected = '/^SELECT .*`'.LinkedContentTestHarness::getTableName().
            '` WHERE `parent1_id` = \? AND `parent2_id` = \?/';
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
        self::assertContains('parent1_id', $properties);
        self::assertContains('parent2_id', $properties);
        self::assertNotContains('label', $properties);
    }

    /**
     * @throws Exception
     */
    public function testInsertUpdateAndDelete()
    {
        $o = new LinkedContentTestHarness();
        $o->parent1_id->setInputValue(LinkedContentTestHarness::CREATE_LINK_IDS['parent1']);
        $o->parent2_id->setInputValue(LinkedContentTestHarness::CREATE_LINK_IDS['parent2']);

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
        $o->parent1_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->parent2_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent2']);
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
        $o->parent1_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1']);
        $o->parent2_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent2']);
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
        $o->parent1_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent1']);
        $o->parent2_id->setInputValue(LinkedContentTestHarness::EXISTING_LINK_IDS['parent2']);
        self::assertTrue($o->recordExists());

        $o->parent1_id->setInputValue(LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1']);
        self::assertFalse($o->recordExists());
    }

    /**
     * @throws Exception
     */
    protected static function lookupRecord(LinkedContentTestHarness $o): array
    {
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.$o::getTableName().'` WHERE parent1_id = ? AND parent2_id = ?';
        return $o->fetchRecords($query, 'ii', $o->parent1_id->value, $o->parent2_id->value);
    }

    /**
     * @throws NotImplementedException
     * @throws Exception
     */
    protected static function lookupLabel(LinkedContentTestHarness $o): array
    {
        $query = 'SEL'.'ECT `label` FROM `'.$o::getTableName().'` WHERE parent1_id = ? AND parent2_id = ?';
        return $o->fetchRecords($query, 'ii', $o->parent1_id->value, $o->parent2_id->value);
    }
}