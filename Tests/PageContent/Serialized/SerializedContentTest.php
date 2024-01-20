<?php
namespace LittledTests\PageContent\Serialized;

use Littled\Exception\NotInitializedException;
use LittledTests\DataProvider\PageContent\Serialized\ReadListTestDataProvider;
use LittledTests\PageContent\SiteSection\SectionContentTest;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentChild;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentNameTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentTestUtility;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentTitleTestHarness;
use LittledTests\TestHarness\PageContent\Serialized\SerializedContentNonDefaultColumn;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use LittledTests\TestHarness\PageContent\Serialized\SketchbookTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use PHPUnit\Framework\TestCase;
use Exception;


class SerializedContentTest extends TestCase
{
	public SerializedContent $obj;
	public MySQLConnection $conn;

	/**
	 * @throws NotImplementedException Table name is not set in inherited classes.
     * @throws Exception
     */
	public static function setUpBeforeClass() : void
	{
		$c = new MySQLConnection();

		$query = "DR"."OP TABLE IF EXISTS `".SerializedContentChild::getTableName()."`";
        $c->query($query);

        $query = 'CREATE TABLE `'.SerializedContentChild::getTableName().'` ('.
			'`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,'.
			'`vc_col1` VARCHAR(50),'.
			'`vc_col2` VARCHAR(255),'.
			'`int_col` INT,'.
			'`bool_col` BOOLEAN,'.
            '`date_col` DATETIME);';
		$c->query($query);

		$query = "DROP TABLE IF EXISTS `".SerializedContentTitleTestHarness::getTableName();
        $c->query($query);

        $query = "CREATE TABLE `".SerializedContentTitleTestHarness::getTableName()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`title` VARCHAR(50),".
			"`vc_col` VARCHAR(255),".
			"`int_col` INT);";
		$c->query($query);

		$query = "DROP TABLE IF EXISTS `".SerializedContentNameTestHarness::getTableName();
        $c->query($query);

        $query ="CREATE TABLE `".SerializedContentNameTestHarness::getTableName()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`name` VARCHAR(50),".
			"`vc_col` VARCHAR(255),".
			"`bool_col` BOOLEAN,".
			"`date_col` DATE);";
		$c->query($query);

		$query = "DROP TABLE IF EXISTS `".SerializedContentNonDefaultColumn::getTableName();
        $c->query($query);

		$query = "CREATE TABLE `".SerializedContentNonDefaultColumn::getTableName()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`name` VARCHAR(50),".
			"`vc_col` VARCHAR(255),".
			"`bool_col` BOOLEAN,".
			"`date_col` DATE,".
			"`non_default` VARCHAR(50));";
		$c->query($query);

		$query = "DROP PROCEDURE IF EXISTS `testListSelect`;";
		$c->query($query);

		$query = "CRE"."ATE PROCEDURE `testListSelect`() ".
			"BEGIN ".
			"SEL"."ECT * from `".SerializedContentTitleTestHarness::getTableName()."` ".
			"ORDER BY `title`; ".
			"END;";
		$c->query($query);

		$c->query("INS"."ERT INTO `".SerializedContentTitleTestHarness::getTableName()."` (`title`,`vc_col`,`int_col`) VALUES ('test one','foo',67);");
        $c->query("INS"."ERT INTO `".SerializedContentTitleTestHarness::getTableName()."` (`title`,`vc_col`,`int_col`) VALUES ('test two','bar',860);");
        $c->query("INS"."ERT INTO `".SerializedContentTitleTestHarness::getTableName()."` (`title`,`vc_col`,`int_col`) VALUES ('test three','biz',1032);");
        $c->query("INS"."ERT INTO `".SerializedContentTitleTestHarness::getTableName()."` (`title`,`vc_col`,`int_col`) VALUES ('test four','bash',94);");
	}

    /**
     * @throws NotImplementedException Table name is not set in inherited classes.
     * @throws Exception
     */
	public static function tearDownAfterClass(): void
	{
		$c = new MySQLConnection();
		$c->query("DROP PROCEDURE IF EXISTS `testListSelect`");
		$c->query('D'.'ROP TABLE `'.SerializedContentChild::getTableName().'`');
		$c->query('D'.'ROP TABLE `'.SerializedContentNameTestHarness::getTableName().'`');
		$c->query('D'.'ROP TABLE `'.SerializedContentTitleTestHarness::getTableName().'`');
		$c->query('D'.'ROP TABLE `'.SerializedContentNonDefaultColumn::getTableName().'`');
	}

	public function setUp(): void
	{
		$this->obj = new SerializedContentChild();
		$this->conn = new MySQLConnection();
	}

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
        $obj->bypassValidation(true);
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
        $original_ids = $o->fetchRecords('SEL'.'ECT id FROM '.$o::getTableName());

        $o->int_col->value = 563;
        $o->name->value = 'foobar';
        $o->bool_col->value = true;
        $o->date->value =  '2/25/2023';

        $o->executeInsertQuery();
        self::assertGreaterThan(0, $o->getRecordId());
        self::assertNotContains($o->getRecordId(), array_map(function($e) { return $e->id; }, $original_ids));

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

		$query = "SEL"."ECT MAX(`id`) AS `insert_id` FROM `".SerializedContentTitleTestHarness::getTableName()."`";
		$data = $this->conn->fetchRecords($query);
		$insert_id = $data[0]->insert_id;

		$result = $obj->getTypeName(SerializedContentTitleTestHarness::getTableName(), $insert_id, 'title');
		$this->assertEquals($obj->title->value, $result);
	}

	/**
	 * @throws ContentValidationException
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

		$query = "SEL"."ECT MAX(`id`) as last_insert_id FROM `".SerializedContentChild::getTableName()."`";
		$data = $this->conn->fetchRecords($query);

		$this->assertEquals($data[0]->last_insert_id, $src->id->value);
		$this->assertEquals('Once upon a time', $src->vc_col2->value);

		$data = $this->conn->fetchRecords("SEL"."ECT * FROM `".SerializedContentChild::getTableName()."` WHERE `id` = {$src->id->value}");
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

		$this->conn->fetchRecords("SEL"."ECT * FROM `".SerializedContentChild::getTableName()."` WHERE `id` = {$obj->id->value}");
		$this->assertEquals('foo', $obj->vc_col1->value);
		$this->assertEquals('', $obj->vc_col2->value);
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
		$o1->name->setInputValue('foozizzle');
		$o1->nonDefaultCol->setInputValue('drooplizzle');
		$o1->save();

		$query = "SEL"."ECT * FROM `".SerializedContentNonDefaultColumn::getTableName()."` WHERE `id` = {$o1->id->value}";
		$data = $this->conn->fetchRecords($query);

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

	    $data = $this->conn->fetchRecords("SEL"."ECT * FROM `".SerializedContentChild::getTableName()."` WHERE `id` = ?", 'i', $obj->id->value);

	    $this->assertNotNull($data[0]->vc_col1);
	    $this->assertNull($data[0]->vc_col2);
	    $this->assertNull($data[0]->int_col);
	    $this->assertNull($data[0]->bool_col);

	    $obj->id->value = null; /* save new record */
	    $obj->vc_col1->value = null;
	    $obj->vc_col2->value = 'bar';
	    $obj->save();

	    $data = $this->conn->fetchRecords("SEL"."ECT * FROM `".SerializedContentChild::getTableName()."` WHERE `id` = ?", 'i', $obj->id->value);

	    $this->assertNull($data[0]->vc_col1);
	    $this->assertNotNull($data[0]->vc_col2);
	    $this->assertNull($data[0]->int_col);
	    $this->assertNull($data[0]->bool_col);
    }

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
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

    public function testSetIdKey()
    {
        $o = new SerializedContentChild();
        self::assertEquals($o::getDefaultIdKey(), $o->id->key);

        $new_key = 'myCustomKey';
        $o2 = (new SerializedContentChild())->setIdKey($new_key);
        self::assertEquals($new_key, $o2->id->key);
    }

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 */
    public function testRead()
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
     * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws NotImplementedException
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
     * @throws NotImplementedException
     */
	public function testReadNullID()
	{
		$obj = new SerializedContentChild();
		try {
			$obj->read();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Record id not set.", $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException|NotImplementedException
     */
	public function testReadInvalidObject()
	{
		$obj = new SerializedContentChild();
		$obj->id->setInputValue(563);
		try {
			$obj->read();
		}
		catch(RecordNotFoundException $e) {
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
	    $title_cb = function($o) {
	    	return ($o->title->value);
	    };
	    $vc_cb = function($o) {
	        return($o->vc_col->value);
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
            $this->assertContains($data->records[0]->term->value, array_map(function($kw) { return $kw->term->value; }, $o->keyword_list));
        }
    }

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
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
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
    public function testUpdateNonExistentRecord ()
    {
        $obj = new SerializedContentChild();
        $obj->id->value = 999999;
        $obj->vc_col1->value = 'foo';
        $obj->vc_col2->value = 'bar';

        $this->expectException(RecordNotFoundException::class);
        $obj->save();

        $this->assertEquals(999999, $obj->id->value);
    }

	/**
	 * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws NotImplementedException
     * @throws NotInitializedException
     * @throws RecordNotFoundException
     */
    public function testDelete()
    {
    	$obj = new SerializedContentChild();

    	/* test valid id value */
	    $obj->id->setInputValue(null);
	    $obj->vc_col1->setInputValue('bar');
        $obj->save();
        $result = $obj->delete();
	    $this->assertMatchesRegularExpression("/has been deleted/", $result);
    }

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
     */
    public function testDeleteDefaultIDValue()
    {
	    $obj = new SerializedContentChild();

	    /* test default id value (null) */
	    $this->expectException(ContentValidationException::class);
	    $obj->delete();
    }

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
     */
    public function testDeleteInvalidIDValue()
    {
	    $obj = new SerializedContentChild();

	    /* test invalid id value */
	    $obj->id->setInputValue(0);
	    $this->expectException(ContentValidationException::class);
	    $obj->delete();
    }

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
     */
	public function testDeleteNonexistentID()
	{
		$obj = new SerializedContentChild();

		/* test invalid id value */
		$obj->id->setInputValue(997799);
		$status = $obj->delete();
		$this->assertMatchesRegularExpression("/could not be found/", $status);
	}
}