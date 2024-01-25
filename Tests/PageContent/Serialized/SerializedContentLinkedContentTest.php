<?php

namespace LittledTests\PageContent\Serialized;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\NotInitializedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\LinkedContent;
use LittledTests\DataProvider\PageContent\Serialized\SerializedContentLinkedContentTestData;
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
            LittledGlobals::setVerboseErrors(true);
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
        self::assertInstanceOf(LinkedContent::class, $fkp[0]);
        self::assertEquals(LinkedContentTestHarness::LINK_KEY, $fkp[0]->link_id->key);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\SerializedContentLinkedContentTestDataProvider::collectRequestDataTestProvider()
     * @param SerializedContentLinkedContentTestData $data
     * @return void
     */
    public function testCollectRequestData(SerializedContentLinkedContentTestData $data)
    {
        $o = new SerializedLinkedTestHarness();
        $saved_post = static::setUpPostData($o, $data);
        $o->collectRequestData();
        self::assertEquals($data->primary_id, $o->id->value);
        self::assertEquals($data->primary_id, $o->linked->primary_id->value);
        if (is_array($data->foreign_id)) {
            self::assertEqualsCanonicalizing($data->foreign_id, $o->linked->link_id->value);
        }
        else {
            self::assertEqualsCanonicalizing([$data->foreign_id], $o->linked->link_id->value);
        }
        self::assertEquals($data->name, $o->name->value);
        self::assertEquals($data->label, $o->linked->label->value);
        $_POST = $saved_post;
    }

    /**
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws NotInitializedException
     * @throws Exception
     */
    public function testCommitLinkedRecords()
    {
        $primary_id = 45;
        $o = new SerializedLinkedTestHarness();
        $o->setRecordId($primary_id);
        $o->linked->link_id->setInputValue([13, 14, 109]);

        // confirm initial number of links
        $start_count = LinkedContentTest::getLinkCount($o->id->value);
        self::assertEquals(0, $start_count);

        // confirm newly saved number of links
        $o->commitLinkedRecords_public();
        self::assertEquals($start_count+3, LinkedContentTest::getLinkCount($o->id->value));

        // confirm value in new record
        self::assertEquals(1, static::confirmLinkById($o, 1));

        // cleanup
        $o->deleteLinked();
    }

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws InvalidValueException
     * @throws RecordNotFoundException
     * @throws NotInitializedException
     */
    public function testRead()
    {
        $primary_id = LinkedContentTestHarness::EXISTING_LINK_IDS['parent1'];
        $expected_link_count = 2;

        $o = (new SerializedLinkedTestHarness())
            ->setRecordId($primary_id);
        $o->read();

        self::assertCount($expected_link_count, $o->linked->listingsData());
    }

    /**
     * @throws Exception
     */
    public function testSave()
    {
        $primary_id = LinkedContentTestHarness::NONEXISTENT_LINK_IDS['parent1'];
        $o = (new SerializedLinkedTestHarness())
            ->setRecordId($primary_id);
        $o->linked->setLinkId([13, 14, 109]);

        // confirm no existing record
        self::assertEquals(0, static::getPrimaryRecordCount($primary_id));

        $o->save();

        // confirm new record
        self::assertEquals(1, static::getPrimaryRecordCount($primary_id));

        // confirm links
        self::assertEquals(3, LinkedContentTest::getLinkCount($primary_id));
        self::assertEquals(1, static::confirmLinkById($o, 1));

        // cleanup
        $o->delete();
    }

    public function testSetRecordId()
    {
        $test_id = 37394;
        $o = new SerializedLinkedTestHarness();
        $o->setRecordId($test_id);
        self::assertEquals($test_id, $o->id->value);
        self::assertEquals($test_id, $o->linked->primary_id->value);
    }

    public function testSetRecordIdChained()
    {
        $test_id = 37394;
        $o = (new SerializedLinkedTestHarness())
            ->setRecordId($test_id);
        self::assertEquals($test_id, $o->id->value);
        self::assertEquals($test_id, $o->linked->primary_id->value);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\SerializedContentLinkedContentTestDataProvider::validateInputFailTestDataProvider()
     * @param SerializedContentLinkedContentTestData $data
     * @return void
     * @throws ContentValidationException
     */
    public function testValidateInputFail(SerializedContentLinkedContentTestData $data)
    {
        $o = new SerializedLinkedTestHarness();
        if ($data->required) {
            $o->linked->link_id->setAsRequired();
        } else {
            $o->linked->link_id->setAsNotRequired();
        }

        $saved_post = static::setUpPostData($o, $data);
        $o->collectRequestData();
        self::expectException(ContentValidationException::class);
        $o->validateInput();

        $_POST = $saved_post;
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\SerializedContentLinkedContentTestDataProvider::validateInputPassTestDataProvider()
     * @param SerializedContentLinkedContentTestData $data
     * @return void
     * @throws ContentValidationException
     */
    public function testValidateInputPass(SerializedContentLinkedContentTestData $data)
    {
        $o = new SerializedLinkedTestHarness();
        if ($data->required) {
            $o->linked->link_id->setAsRequired();
        } else {
            $o->linked->link_id->setAsNotRequired();
        }

        $saved_post = static::setUpPostData($o, $data);
        $o->collectRequestData();
        $o->validateInput();
        self::assertFalse($o->hasValidationErrors());

        $_POST = $saved_post;
    }

    /**
     * @throws NotImplementedException
     * @throws Exception
     */
    public static function confirmLinkById(SerializedLinkedTestHarness $o, int $link_index): int
    {
        $query = 'SEL'.'ECT COUNT(1) AS `count` FROM `'.LinkedContentTestHarness::getTableName().'` '.
            'WHERE `'.$o->id->getColumnName('primary_id').'` = ? '.
            'AND `'.$o->linked->link_id->getColumnName().'` = ?';
        $result = $o->fetchRecords($query, 'ii', $o->id->value, $o->linked->link_id->value[$link_index]);
        return $result[0]->count;
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

    /**
     * Configure POST data for test.
     * @param SerializedLinkedTestHarness $o
     * @param SerializedContentLinkedContentTestData $data
     * @return array Original POST data to be restored after test
     */
    protected static function setUpPostData(
        SerializedLinkedTestHarness $o,
        SerializedContentLinkedContentTestData $data
    ): array
    {
        $saved_post = $_POST;
        $_POST = array_merge($_POST, array(
            $o->id->key => $data->primary_id,
            $o->linked->link_id->key => $data->foreign_id,
            $o->name->key => $data->name,
            $o->linked->label->key => $data->label
        ));
        return $saved_post;
    }
}