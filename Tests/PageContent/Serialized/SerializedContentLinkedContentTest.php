<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Request\ForeignKeyInput;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\LinkedContentTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\LinkedContent\SerializedLinkedTestHarness;
use PHPUnit\Framework\TestCase;
use Exception;

class SerializedContentLinkedContentTest extends TestCase
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

    public function testGetForeignKeyPropertyList()
    {
        $o = new SerializedLinkedTestHarness();
        $fkp = $o->getForeignKeyPropertyList_public();
        self::assertCount(1, $fkp);
        self::assertInstanceOf(ForeignKeyInput::class, $fkp[0]);
        self::assertEquals(SerializedLinkedTestHarness::LINK_KEY, $fkp[0]->key);
    }

    /**
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws NotInitializedException
     * @throws Exception
     */
    public function testCommitForeignKeys()
    {
        $primary_id = 45;
        $o = new SerializedLinkedTestHarness();
        $o->id->setInputValue($primary_id);
        $o->parent2->setInputValue([13, 14, 109]);

        // confirm initial number of links
        $count = LinkedContentTest::getLinkCount($o->id->value);
        self::assertEquals(0, $count);

        // confirm newly saved number of links
        $o->commitForeignKeys_public();
        self::assertEquals($count+3, LinkedContentTest::getLinkCount($o->id->value));

        // confirm value in new record
        self::assertEquals(1, static::confirmLinkById($o, 1));

        // cleanup
        static::deleteLinkRecords($o);
    }

    /**
     * @throws Exception
     */
    public function testSave()
    {
        $primary_id = LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'];
        $o = new SerializedLinkedTestHarness();
        $o->id->setInputValue($primary_id);
        $o->parent2->setInputValue([13, 14, 109]);

        // confirm no existing record
        $count = static::getPrimaryRecordCount($primary_id);
        self::assertEquals(0, $count);

        $o->save();

        // confirm new record
        self::assertEquals(1, static::getPrimaryRecordCount($primary_id));

        // confirm links
        self::assertEquals(3, LinkedContentTest::getLinkCount($primary_id));
        self::assertEquals(1, static::confirmLinkById($o, 1));

        // cleanup
        static::deleteParentRecord($o);
    }

    /**
     * @throws NotImplementedException
     * @throws Exception
     */
    public static function confirmLinkById(SerializedLinkedTestHarness $o, int $link_index): int
    {
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.LinkedContentTestHarness::getTableName().'` '.
            'WHERE `'.$o->id->getColumnName('primary_id').'` = ? '.
            'AND `'.$o->parent2->getColumnName().'` = ?';
        $result = $o->fetchRecords($query, 'ii', $o->id->value, $o->parent2->value[$link_index]);
        return $result[0]->count;
    }

    /**
     * @throws NotImplementedException
     * @throws Exception
     */
    public static function deleteLinkRecords(SerializedLinkedTestHarness $o)
    {
        $query = 'DEL'.'ETE FROM `'.LinkedContentTestHarness::getTableName().'` '.
            'WHERE `'.$o->id->getColumnName('primary_id').'` = ?';
        $o->query($query, 'i', $o->id->value);
    }

    /**
     * @throws Exception
     */
    public static function deleteParentRecord(SerializedLinkedTestHarness $o)
    {
        // ON DELETE CASCADE will take care of links
        $query = 'DEL'.'ETE FROM `test_parent1` WHERE `id` = ?';
        $o->query($query, 'i', $o->id->value);
    }

    /**
     * @throws Exception
     */
    public static function getPrimaryRecordCount(?int $id=null): int
    {
        $o = new LinkedContentTestHarness();
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `test_parent1`';
        if ($id === null) {
            $result = $o->fetchRecords($query);
        }
        else {
            $query .= ' WHERE `id` = ?';
            $result = $o->fetchRecords($query, 'i', $id);
        }
        return $result[0]->count;
    }
}