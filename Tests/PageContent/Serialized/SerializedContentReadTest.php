<?php

namespace LittledTests\PageContent\Serialized;


use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\PageUtils;
use LittledTests\DataProvider\PageContent\Serialized\OneToOneLinkSerializedContentTestData;
use LittledTests\DataProvider\PageContent\Serialized\ReadListTestDataProvider;
use LittledTests\TestHarness\PageContent\Serialized\OneToOneLinkLCTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\OneToOneLinkProcLCTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentChild;
use LittledTests\TestHarness\PageContent\Serialized\SketchbookTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\TestTableSerializedContentTestHarness;

class SerializedContentReadTest extends SerializedContentTestBase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $o = new OneToOneLinkProcLCTestHarness();
        $o->setUpTestData();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $o = new OneToOneLinkProcLCTestHarness();
        $o->tearDownTestData();
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testReadExisting()
    {
        $o = (new TestTableSerializedContentTestHarness())
            ->setRecordId(TestTableSerializedContentTestHarness::EXISTING_ID);
        $o->read();

        $query = 'SEL'.'ECT * from `'.TestTableSerializedContentTestHarness::getTableName().'` WHERE `id` = ?';
        $record_id = TestTableSerializedContentTestHarness::EXISTING_ID;
        $r = static::$conn->fetchRecords($query, 'i', $record_id);

        self::assertEquals($r[0]->name, $o->name->value);
        self::assertEquals($r[0]->int_col, $o->int_col->value);
        self::assertEquals($r[0]->bool_col, $o->bool_col->value);
        if ($r[0]->date) {
            self::assertEquals(PageUtils::formatDate($r[0]->date, 'Y-m-d'), $o->date->value);
        }
        else {
            self::assertEmpty($o->date->value);
        }
        self::assertEquals($r[0]->slot, $o->slot->value);
    }

    /**
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException|NotImplementedException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     */
    public function testReadInvalidObject()
    {
        $obj = new SerializedContentChild();
        $obj->id->setInputValue(563);
        try {
            $obj->read();
        } catch (RecordNotFoundException $e) {
            $this->assertMatchesRegularExpression('/record could not be found/', $e->getMessage());
        }
        $this->assertEmpty($obj->vc_col1->value);
    }

    /**
     * @throws NotImplementedException
     * @throws InvalidTypeException
     */
    public function testReadList()
    {
        $query = "CALL testListSelect();";
        $title_cb = function ($o) {
            return ($o->title->value);
        };
        $vc_cb = function ($o) {
            return ($o->vc_col->value);
        };
        $obj = new SerializedContentChild();
        $obj->readList('array_container', 'LittledTests\TestHarness\PageContent\Serialized\SerializedContentTitleTestHarness', $query);
        $this->assertGreaterThan(0, count($obj->array_container));
        $this->assertContains('test one', array_map($title_cb, $obj->array_container));
        $this->assertContains('test four', array_map($title_cb, $obj->array_container));
        $this->assertContains('bar', array_map($vc_cb, $obj->array_container));
        $this->assertContains('biz', array_map($vc_cb, $obj->array_container));
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\ReadListTestDataProvider::readListProvider
     * @return void
     * @throws InvalidTypeException
     * @throws NotImplementedException|ConfigurationUndefinedException
     */
    function testReadListWithArguments(ReadListTestDataProvider $data)
    {
        $o = new SketchbookTestHarness($data->record_id);
        $content_type_id = $o::getContentTypeId();
        $o->readList($data->property_name, $data->class_name, 'CALL keywordSelectLinked(?,?)', 'ii', $data->record_id, $content_type_id);
        $this->assertCount(count($data->records), $o->keyword_list);
        if (0 < count($data->records)) {
            $this->assertContains($data->records[0]->term->value, array_map(function ($kw) {
                return $kw->term->value;
            }, $o->keyword_list));
        }
    }

    /**
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testReadNew()
    {
        $obj = new SerializedContentChild();
        $obj->vc_col1->setInputValue('foo read test');
        $obj->vc_col2->setInputValue('bar read test');
        $obj->int_col->setInputValue(8452);
        $obj->bool_col->setInputValue(false);
        $obj->save();

        $o2 = new SerializedContentChild();
        $o2->id->setInputValue($obj->id->value);
        $o2->read();

        $this->assertEquals($obj->id->value, $o2->id->value);
        $this->assertEquals($obj->vc_col1->value, $o2->vc_col1->value);
        $this->assertEquals($obj->vc_col2->value, $o2->vc_col2->value);
        $this->assertEquals($obj->int_col->value, $o2->int_col->value);
        $this->assertEquals($obj->bool_col->value, $o2->bool_col->value);
    }

    /**
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testReadNonExistentRecord()
    {
        $obj = new SerializedContentChild();
        $obj->id->setInputValue(99988999);
        $this->expectException(RecordNotFoundException::class);
        $obj->read();
    }

    /**
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     */
    public function testReadNullID()
    {
        $obj = new SerializedContentChild();
        try {
            $obj->read();
        } catch (ContentValidationException $ex) {
            $this->assertEquals("Record id not set.", $ex->getMessage());
        }
    }

    /**
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testReadNullValues()
    {
        $obj = new SerializedContentChild();
        $obj->vc_col1->setInputValue('foo read test');
        $obj->vc_col2->setInputValue('');
        $obj->int_col->setInputValue(null);
        $obj->bool_col->setInputValue(null);
        $obj->save();

        $o2 = new SerializedContentChild();
        $o2->id->setInputValue($obj->id->value);
        $o2->read();

        $this->assertEquals($obj->id->value, $o2->id->value);
        $this->assertEquals('', $o2->vc_col2->value);
        $this->assertNull($o2->int_col->value);
        $this->assertNull($o2->bool_col->value);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\OneToOneLinkSerializedContentTestDataProvider::readOneToOneLinkTestProvider()
     * @param OneToOneLinkSerializedContentTestData $data
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testReadOneToOneLinkUsingProcedure(OneToOneLinkSerializedContentTestData $data)
    {
        $o = (new OneToOneLinkProcLCTestHarness())
            ->setRecordId($data->record_id);
        $o->read();

        self::assertEquals($data->expected->name, $o->name->value);
        self::assertEquals($data->expected->status_id, $o->status_id->value);
        self::assertEquals($data->expected->status, $o->status);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\Serialized\OneToOneLinkSerializedContentTestDataProvider::readOneToOneLinkTestProvider()
     * @param OneToOneLinkSerializedContentTestData $data
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     */
    public function testReadOneToOneLinkUsingQuery(OneToOneLinkSerializedContentTestData $data)
    {
        $o = (new OneToOneLinkLCTestHarness())
            ->setRecordId($data->record_id);
        $o->read();

        self::assertEquals($data->expected->name, $o->name->value);
        self::assertEquals($data->expected->status_id, $o->status_id->value);
        self::assertEquals('', $o->status);
    }
}