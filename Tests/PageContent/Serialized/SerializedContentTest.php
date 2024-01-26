<?php

namespace LittledTests\PageContent\Serialized;

use Littled\Exception\InvalidValueException;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentChild;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentTitleTestHarness;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Exception;


class SerializedContentTest extends SerializedContentTestBase
{
    /**
     * @throws ContentValidationException
     */
    function testBypassValidation()
    {
        $obj = new SerializedContentChild();
        $obj->vc_col1->setAsNotRequired();

        // confirm no validation errors with no required fields
        $obj->validateInput();
        $this->assertCount(0, $obj->validationErrors());

        // set one property value as required with bypassValidation called with default value (TRUE)
        $obj->vc_col2->setAsRequired();
        $obj->bypassValidation();
        $obj->validateInput();
        $this->assertCount(0, $obj->validationErrors());

        // pass explicit TRUE value to bypassValidation()
        $obj->bypassValidation();
        $obj->validateInput();
        $this->assertCount(0, $obj->validationErrors());

        // turn off validation bypass to confirm errors are caught
        $obj->bypassValidation(false);
        $this->expectExceptionMessageMatches('/required information is missing/i');
        $obj->validateInput();
    }

    /**
     * @throws Exception
     */
    public function testColumnExists()
    {
        $obj = new SerializedContentChild();
        $this->assertTrue($obj->columnExists('vc_col1'));
        $this->assertFalse($obj->columnExists('not_a_column'));

        /* test that internal table name value cannot be overridden */
        $this->assertFalse($obj->columnExists('name', 'video_reel'));
    }

    function testGetRecordId()
    {
        $test_id = 125;
        $sc = new SerializedContentChild();
        $this->assertNull($sc->getRecordId());

        $sc->setRecordId($test_id);
        $this->assertEquals($test_id, $sc->getRecordId());
    }

    /**
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws Exception
     */
    public function testGetTypeName()
    {
        $obj = new SerializedContentTitleTestHarness();
        $obj->title->setInputValue("Sketchbook");
        $obj->vc_col->setInputValue("TestValue");
        $obj->int_col->setInputValue(52);
        $obj->save();

        $query = "SEL" . "ECT MAX(`id`) AS `insert_id` FROM `" . SerializedContentTitleTestHarness::getTableName() . "`";
        $data = static::$conn->fetchRecords($query);
        $insert_id = $data[0]->insert_id;

        $result = $obj->getTypeName(SerializedContentTitleTestHarness::getTableName(), $insert_id, 'title');
        $this->assertEquals($obj->title->value, $result);
    }

    /**
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
     * @throws RecordNotFoundException
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     */
    public function testRecordExists()
    {
        $obj = new SerializedContentChild();

        /* test default id value (null) */
        $this->assertFalse($obj->recordExists());

        /* test id = 0 */
        $obj->id->setInputValue(0);
        $this->assertFalse($obj->recordExists());

        /* test id is a valid integer, but not a value that exists in the database */
        $obj->id->setInputValue(999999);
        $this->assertFalse($obj->recordExists());

        /* test valid id value */
        $obj->id->setInputValue(null);
        $obj->vc_col1->setInputValue('foo');
        $obj->save();

        $this->assertGreaterThan(0, $obj->id->value);
        $this->assertTrue($obj->recordExists());
    }

    public function testSetIdKey()
    {
        $o = new SerializedContentChild();
        self::assertEquals($o::getDefaultIdKey(), $o->id->key);

        $new_key = 'myCustomKey';
        $o2 = (new SerializedContentChild())->setIdKey($new_key);
        self::assertEquals($new_key, $o2->id->key);
    }
}