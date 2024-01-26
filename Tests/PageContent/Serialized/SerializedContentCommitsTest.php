<?php

namespace LittledTests\PageContent\Serialized;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use LittledTests\PageContent\SiteSection\SectionContentTest;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentChild;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentNonDefaultColumn;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentTestUtility;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use Exception;

class SerializedContentCommitsTest extends SerializedContentTestBase
{
    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws RecordNotFoundException
     * @throws ContentValidationException
     * @throws Exception
     */
    function testExecuteInsertQuery()
    {
        $o = new TestTableSectionContentTestHarness();
        $original_ids = $o->fetchRecords('SEL' . 'ECT id FROM ' . $o::getTableName());

        $o->int_col->value = 563;
        $o->name->value = 'foobar';
        $o->bool_col->value = true;
        $o->date->value = '2/25/2023';

        $o->executeInsertQuery();
        self::assertGreaterThan(0, $o->getRecordId());
        self::assertNotContains($o->getRecordId(), array_map(function ($e) {
            return $e->id;
        }, $original_ids));

        $o2 = new TestTableSectionContentTestHarness($o->getRecordId());
        $o2->read();
        self::assertEquals(563, $o->int_col->value);
        self::assertEquals('foobar', $o->name->value);
        self::assertEquals('2/25/2023', $o->date->value);
        self::assertTrue($o->bool_col->value);

        $o2->delete();
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
    function testExecuteUpdateQuery()
    {
        // retrieve original property values from database
        $o = new TestTableSectionContentTestHarness();
        $o->setRecordId(SectionContentTest::TEST_RECORD_ID);
        $o->read();
        $start_values = $o->formatDatabaseColumnList();

        // assign new property values
        $new_values = (object)array(
            'name' => "new value's new\n value",
            'int_col' => 5294,
            'bool_col' => false,
            'date' => '2/25/2023'
        );
        $o->name->setInputValue($new_values->name);
        $o->int_col->setInputValue($new_values->int_col);
        $o->bool_col->setInputValue($new_values->bool_col);
        $o->date->setInputValue($new_values->date);

        // commit changes
        $o->executeUpdateQuery();

        // retrieve new property values from database
        $o2 = new TestTableSectionContentTestHarness();
        $o2->setRecordId($o->getRecordId());
        $o2->read();

        // confirm update query committed new values
        $this->assertSame($new_values->name, $o2->name->value);
        $this->assertSame($new_values->int_col, $o2->int_col->value);
        $this->assertEquals(strtotime($new_values->date), strtotime($o2->date->value));
        $this->assertSame($new_values->bool_col, $o2->bool_col->value);
        $this->assertNotSame(SerializedContentTestUtility::lookupColumnListValue($start_values, 'name'), $o2->name->value);

        // restore record to its original state
        $o->name->setInputValue(SerializedContentTestUtility::lookupColumnListValue($start_values, 'name'));
        $o->int_col->setInputValue(SerializedContentTestUtility::lookupColumnListValue($start_values, 'int_col'));
        $o->date->setInputValue(SerializedContentTestUtility::lookupColumnListValue($start_values, 'date'));
        $o->bool_col->setInputValue(SerializedContentTestUtility::lookupColumnListValue($start_values, 'bool_col'));
        $o->save();
    }

    /**
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws RecordNotFoundException No record exists that matches the id value.
     * @throws Exception
     */
    public function testSave()
    {
        $src = new SerializedContentChild();
        $src->vc_col1->value = 'foo';
        $src->vc_col2->value = 'Once upon a time';
        $src->int_col->value = 2874;
        $src->bool_col->value = true;
        $src->save();

        $query = "SEL" . "ECT MAX(`id`) as last_insert_id FROM `" . SerializedContentChild::getTableName() . "`";
        $data = static::$conn->fetchRecords($query);

        $this->assertEquals($data[0]->last_insert_id, $src->id->value);
        $this->assertEquals('Once upon a time', $src->vc_col2->value);

        $data = static::$conn->fetchRecords("SEL" . "ECT * FROM `" . SerializedContentChild::getTableName() . "` WHERE `id` = {$src->id->value}");
        $this->assertCount(1, $data);
        $this->assertEquals($src->id->value, $data[0]->id);
        $this->assertEquals($src->vc_col1->value, $data[0]->vc_col1);
        $this->assertEquals($src->vc_col2->value, $data[0]->vc_col2);
        $this->assertEquals($src->int_col->value, $data[0]->int_col);
        $this->assertEquals($src->bool_col->value, $data[0]->bool_col);
    }

    /**
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws RecordNotFoundException No record exists that matches the id value.
     * @throws Exception
     */
    public function testSaveDefaultValues()
    {
        $obj = new SerializedContentChild();
        $this->expectException(ContentValidationException::class);
        $obj->save();

        $obj->vc_col1->value = 'foo';
        $obj->save();

        $query = 'SEL' . 'ECT * FROM `' . SerializedContentChild::getTableName() . '` WHERE `id` = ?';
        $data = static::$conn->fetchRecords($query, 'i', $obj->id->value);

        $this->assertEquals($data[0]->vc_col1, $obj->vc_col1->value);
        $this->assertEquals($data[0]->vc_col2, $obj->vc_col2->value);
        $this->assertNull($obj->int_col->value);
        $this->assertNull($obj->bool_col->value);
    }

    /**
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
    public function testSaveNonDefaultColumns()
    {
        $o1 = new SerializedContentNonDefaultColumn();
        $o1->name->setInputValue('fooZizZle');
        $o1->nonDefaultCol->setInputValue('droPizLe');
        $o1->save();

        $query = 'SEL' . 'ECT * FROM `' . SerializedContentNonDefaultColumn::getTableName() . '` WHERE `id` = ?';
        $data = static::$conn->fetchRecords($query, 'i', $o1->id->value);

        $this->assertEquals($o1->nonDefaultCol->value, $data[0]->non_default);
    }

    /**
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws RecordNotFoundException No record exists that matches the id value.
     * @throws Exception
     */
    public function testSaveNullValues()
    {
        $obj = new SerializedContentChild();
        $obj->vc_col1->value = 'foo';
        $obj->vc_col2->value = null;
        $obj->save();

        $data = static::$conn->fetchRecords("SEL" . "ECT * FROM `" . SerializedContentChild::getTableName() . "` WHERE `id` = ?", 'i', $obj->id->value);

        $this->assertNotNull($data[0]->vc_col1);
        $this->assertNull($data[0]->vc_col2);
        $this->assertNull($data[0]->int_col);
        $this->assertNull($data[0]->bool_col);

        $obj->id->value = null; /* save new record */
        $obj->vc_col1->value = null;
        $obj->vc_col2->value = 'bar';
        $obj->save();

        $data = static::$conn->fetchRecords("SEL" . "ECT * FROM `" . SerializedContentChild::getTableName() . "` WHERE `id` = ?", 'i', $obj->id->value);

        $this->assertNull($data[0]->vc_col1);
        $this->assertNotNull($data[0]->vc_col2);
        $this->assertNull($data[0]->int_col);
        $this->assertNull($data[0]->bool_col);
    }

    /**
     * @throws ContentValidationException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException No record exists that matches the id value.
     */
    public function testUpdate()
    {
        $obj = new SerializedContentChild();
        $obj->vc_col1->value = 'foo';
        $obj->vc_col2->value = 'bar';
        $obj->save();
        $original_id = $obj->id->value;

        $obj->vc_col2->value = 'biz';
        $obj->save();
        $this->assertEquals($original_id, $obj->id->value);
        $this->assertEquals('foo', $obj->vc_col1->value);
        $this->assertEquals('biz', $obj->vc_col2->value);
        $this->assertNull($obj->int_col->value);
        $this->assertNull($obj->bool_col->value);

        $obj->int_col->value = 65;
        $obj->save();
        $this->assertEquals($original_id, $obj->id->value);
        $this->assertEquals('foo', $obj->vc_col1->value);
        $this->assertEquals('biz', $obj->vc_col2->value);
        $this->assertEquals(65, $obj->int_col->value);
        $this->assertNull($obj->bool_col->value);

        $obj->vc_col1->value = '';
        $obj->bool_col->value = false;
        $obj->save();
        $this->assertEquals($original_id, $obj->id->value);
        $this->assertEquals('', $obj->vc_col1->value);
        $this->assertEquals('biz', $obj->vc_col2->value);
        $this->assertEquals(65, $obj->int_col->value);
        $this->assertFalse($obj->bool_col->value);
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
    public function testUpdateNonExistentRecord()
    {
        $obj = new SerializedContentChild();
        $obj->id->value = 999999;
        $obj->vc_col1->value = 'foo';
        $obj->vc_col2->value = 'bar';

        $this->expectException(RecordNotFoundException::class);
        $obj->save();

        $this->assertEquals(999999, $obj->id->value);
    }
}