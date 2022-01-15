<?php
namespace Littled\Tests\PageContent\Serialized;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Tests\PageContent\Serialized\TestObjects\SerializedContentChild;
use Littled\Tests\PageContent\Serialized\TestObjects\SerializedContentNameTestHarness;
use Littled\Tests\PageContent\Serialized\TestObjects\SerializedContentTitleTestHarness;
use Littled\Tests\PageContent\Serialized\TestObjects\SerializedContentNonDefaultColumn;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use PHPUnit\Framework\TestCase;
use Exception;

class SerializedContentTest extends TestCase
{
	/** @var SerializedContent Test object. */
	public $obj;
	/** @var MySQLConnection Database connection. */
	public $conn;

	/**
	 * @throws NotImplementedException Table name is not set in inherited classes.
	 * @throws InvalidQueryException Error executing query.
     * @throws Exception
     */
	public static function setUpBeforeClass() : void
	{
		$c = new MySQLConnection();

		$query = "DROP TABLE IF EXISTS `".SerializedContentChild::getTableName()."`";
        $c->query($query);

        $query = "CREATE TABLE `".SerializedContentChild::getTableName()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`vc_col1` VARCHAR(50),".
			"`vc_col2` VARCHAR(255),".
			"`int_col` INT,".
			"`bool_col` BOOLEAN);";
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
     * @throws InvalidQueryException Error executing query.
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
		$this->obj = new SerializedContent();
		$this->conn = new MySQLConnection();
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
        $sc = new SerializedContent();
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

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws InvalidTypeException
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
     * @throws InvalidTypeException
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
     * @throws InvalidTypeException
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
     * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     */
	public function testReadInvalidObject()
	{
		$obj = new SerializedContentChild();
		$obj->id = 563;
		try {
			$obj->read();
		}
		catch(InvalidTypeException $ex) {
			$this->assertEquals("Record id not in expected format.", $ex->getMessage());
		}
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
    	$obj->readList('array_container', 'Littled\Tests\PageContent\Serialized\TestObjects\SerializedContentTitleTestHarness', $query);
    	$this->assertGreaterThan(0, count($obj->array_container));
    	$this->assertContains('test one', array_map($title_cb, $obj->array_container));
	    $this->assertContains('test four', array_map($title_cb, $obj->array_container));
	    $this->assertContains('bar', array_map($vc_cb, $obj->array_container));
	    $this->assertContains('biz', array_map($vc_cb, $obj->array_container));
    }

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws InvalidTypeException
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
     * @throws InvalidQueryException
	 */
    public function testGetNameColumnIdentifier()
    {
    	/* test when there is no matching column */
	    $c = new SerializedContentChild();
    	$this->assertEquals('', $c->getNameColumnIdentifier());

    	$title = new SerializedContentTitleTestHarness();
		$this->assertEquals('title', $title->getNameColumnIdentifier());

	    $name = new SerializedContentNameTestHarness();
	    $this->assertEquals('name', $name->getNameColumnIdentifier());
    }

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws NotImplementedException
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