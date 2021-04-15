<?php
namespace Littled\Tests\PageContent;


use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanInput;
use Littled\Request\IntegerInput;
use Littled\Request\StringInput;
use Littled\Request\StringTextField;
use PHPUnit\Framework\TestCase;


/**
 * Class SerializedContentChild
 * @package Littled\Tests\PageContent
 */
class SerializedContentChild extends SerializedContent
{
	/** @var StringInput Test string input property */
	public $vc_col1;
	/** @var StringInput Test string input property */
	public $vc_col2;
	/** @var IntegerInput Test integer input property */
	public $int_col;
	/** @var BooleanInput Test boolean input property */
	public $bool_col;
	/** @var mixed Test plain mixed variable value property */
	public $prop1;
	/** @var mixed Another test plain mixed variable value property */
	public $prop2;
	/** @var array Test array container */
	public $array_container;

	/**
	 * SerializedContentChild constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->vc_col1 = new StringInput('Test varchar value 1', 'p_vc1', true, '', 50);
		$this->vc_col2 = new StringInput('Test varchar value 1', 'p_vc2', false, '', 255);
		$this->int_col = new IntegerInput('Test int value', 'p_int');
		$this->bool_col = new BooleanInput('Test bool value', 'p_bool');

	}

	public static function TABLE_NAME()
	{
		return ('serialized_content_unit_tests');
	}

	public function hasData()
	{
		if ($this->vc_col2->value !== null && strlen($this->vc_col2->value) > 0) { return(true); }
		if ($this->vc_col1->value !== null && strlen($this->vc_col1->value) > 0) { return(true); }
		if ($this->int_col->value !== null) { return(true); }
		if ($this->bool_col->value !== null) { return(true); }
		return (parent::hasData());
	}
}


class SerializedContentTitleTestHarness extends SerializedContent
{
	/** @var StringInput Title property */
	public $title;
	/** @var StringInput Test string property */
	public $vc_col;
	/** @var IntegerInput Test integer property */
	public $int_col;

	public function __construct()
	{
		parent::__construct();
		$this->title = new StringInput('Title field', 'ptit', true, '', 50);
		$this->vc_col = new StringInput('String field', 'pstr', false, '', 255);
		$this->int_col = new IntegerInput('Integer field', 'pint');
	}

	public static function TABLE_NAME()
	{
		return ('serialized_content_title_test');
	}

	public function hasData()
	{
		if ($this->title->value !== null && strlen($this->title->value) > 0) { return(true); }
		if ($this->vc_col->value !== null && strlen($this->vc_col->value) > 0) { return(true); }
		if ($this->int_col->value !== null) { return(true); }
		parent::hasData(); // TODO: Change the autogenerated stub
	}
}


class SerializedContentNameTestHarness extends SerializedContent
{
	public $name;
	public $vc_col;
	public $bool_col;
	public $date_col;

	public static function TABLE_NAME()
	{
		return ('serialized_content_name_test');
	}

	public function __construct()
	{
		parent::__construct();
		$this->name = new StringInput('Name field', 'pname', true, '', 50);
		$this->vc_col = new StringInput('String field', 'pstr', false, '', 255);
		$this->bool_col = new IntegerInput('Boolean field', 'pbool');
		$this->date_col = new IntegerInput('Date field', 'pdate');
	}

	public function hasData()
	{
		return ($this->id->value > 0 || strlen("".$this->name->value) > 0);
	}
}


class SerializedContentNonDefaultColumn extends SerializedContentNameTestHarness
{
	/** @var StringTextField Column to use to test non-default column names */
	public $nonDefaultCol;

	public static function TABLE_NAME()
	{
		return ('serialized_content_column_test');
	}

	public function __construct()
	{
		parent::__construct();
		$this->nonDefaultCol = new StringTextField('Non-default column', 'pnfc', true, null, 50);
		$this->nonDefaultCol->columnName = 'non_default';
	}

	public function hasData()
	{
		$result = parent::hasData();
		if ($this->nonDefaultCol->value) {
			$result = true;
		}
		return ($result);
	}
}


class SerializedContentTest extends TestCase
{
	/** @var SerializedContent Test object. */
	public $obj;
	/** @var MySQLConnection Database connection. */
	public $conn;

	/**
	 * @throws NotImplementedException Table name is not set in inherited classes.
	 * @throws InvalidQueryException Error executing query.
	 */
	public static function setUpBeforeClass() : void
	{
		$c = new MySQLConnection();

		$query = "DROP TABLE IF EXISTS `".SerializedContentChild::TABLE_NAME()."`; ".
			"CREATE TABLE `".SerializedContentChild::TABLE_NAME()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`vc_col1` VARCHAR(50),".
			"`vc_col2` VARCHAR(255),".
			"`int_col` INT,".
			"`bool_col` BOOLEAN);";
		$c->query($query);

		$query = "DROP TABLE IF EXISTS `".SerializedContentTitleTestHarness::TABLE_NAME()."`; ".
			"CREATE TABLE `".SerializedContentTitleTestHarness::TABLE_NAME()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`title` VARCHAR(50),".
			"`vc_col` VARCHAR(255),".
			"`int_col` INT);";
		$c->query($query);

		$query = "DROP TABLE IF EXISTS `".SerializedContentNameTestHarness::TABLE_NAME()."`; ".
			"CREATE TABLE `".SerializedContentNameTestHarness::TABLE_NAME()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`name` VARCHAR(50),".
			"`vc_col` VARCHAR(255),".
			"`bool_col` BOOLEAN,".
			"`date_col` DATE);";
		$c->query($query);

		$query = "DROP TABLE IF EXISTS `".SerializedContentNonDefaultColumn::TABLE_NAME()."`; ".
			"CREATE TABLE `".SerializedContentNonDefaultColumn::TABLE_NAME()."` (".
			"`id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,".
			"`name` VARCHAR(50),".
			"`vc_col` VARCHAR(255),".
			"`bool_col` BOOLEAN,".
			"`date_col` DATE,".
			"`non_default` VARCHAR(50));";
		$c->query($query);

		$query = "DROP PROCEDURE IF EXISTS `testListSelect`;";
		$c->query($query);

		$query = "CREATE PROCEDURE `testListSelect`() ".
			"BEGIN ".
			"SELECT * from `".SerializedContentTitleTestHarness::TABLE_NAME()."` ".
			"ORDER BY `title`; ".
			"END;";
		$c->query($query);

		$query = "INSERT INTO `".SerializedContentTitleTestHarness::TABLE_NAME()."` (`title`,`vc_col`,`int_col`) VALUES ('test one','foo',67);".
			"INSERT INTO `".SerializedContentTitleTestHarness::TABLE_NAME()."` (`title`,`vc_col`,`int_col`) VALUES ('test two','bar',860);".
			"INSERT INTO `".SerializedContentTitleTestHarness::TABLE_NAME()."` (`title`,`vc_col`,`int_col`) VALUES ('test three','biz',1032);".
			"INSERT INTO `".SerializedContentTitleTestHarness::TABLE_NAME()."` (`title`,`vc_col`,`int_col`) VALUES ('test four','bash',94);";
		$c->query($query);
	}

	/**
	 * @throws NotImplementedException Table name is not set in inherited classes.
	 * @throws \Littled\Exception\InvalidQueryException Error executing query.
	 */
	public static function tearDownAfterClass(): void
	{
		$c = new MySQLConnection();
		$query = "DROP PROCEDURE IF EXISTS `testListSelect`;";
		$c->query($query);
		$query = 'DROP TABLE `'.SerializedContentChild::TABLE_NAME().'`';
		$c->query($query);
		$query = 'DROP TABLE `'.SerializedContentNameTestHarness::TABLE_NAME().'`';
		$c->query($query);
		$query = 'DROP TABLE `'.SerializedContentTitleTestHarness::TABLE_NAME().'`';
		$c->query($query);
		$query = 'DROP TABLE `'.SerializedContentNonDefaultColumn::TABLE_NAME().'`';
		$c->query($query);
	}

	public function setUp(): void
	{
		$this->obj = new SerializedContent();
		$this->conn = new MySQLConnection();
	}

	/**
	 * @throws NotImplementedException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testColumnExists()
	{
		$obj = new SerializedContentChild();
		$this->assertTrue($obj->columnExists('vc_col1'));
		$this->assertFalse($obj->columnExists('not_a_column'));

		/* test that internal table name value cannot be overridden */
		$this->assertFalse($obj->columnExists('title', SerializedContentTitleTestHarness::TABLE_NAME()));
	}

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 */
	public function testGetTypeName()
	{
		$obj = new SerializedContentTitleTestHarness();
		$obj->title->setInputValue("Sketchbook");
		$obj->vc_col->setInputValue("TestValue");
		$obj->int_col->setInputValue(52);
		$obj->save();

		$query = "SELECT MAX(`id`) AS `insert_id` FROM `".SerializedContentTitleTestHarness::TABLE_NAME()."`";
		$data = $this->conn->fetchRecords($query);
		$insert_id = $data[0]->insert_id;

		$result = $obj->getTypeName(SerializedContentTitleTestHarness::TABLE_NAME(), $insert_id, 'title', 'id');
		$this->assertEquals($obj->title->value, $result);
	}

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
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
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException No record exists that matches the id value.
	 */
	public function testSave()
	{
		$src = new SerializedContentChild();
		$src->vc_col1->value = 'foo';
		$src->vc_col2->value = 'Once upon a time';
		$src->int_col->value = 2874;
		$src->bool_col->value = true;
		$src->save();

		$query = "SELECT MAX(`id`) as last_insert_id FROM `".SerializedContentChild::TABLE_NAME()."`";
		$data = $this->conn->fetchRecords($query);

		$this->assertEquals($data[0]->last_insert_id, $src->id->value);
		$this->assertEquals('Once upon a time', $src->vc_col2->value);

		$data = $this->conn->fetchRecords("SELECT * FROM `".SerializedContentChild::TABLE_NAME()."` WHERE `id` = {$src->id->value}");
		$this->assertEquals(1, count($data));
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
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException No record exists that matches the id value.
	 */
	public function testSaveDefaultValues()
	{
		$obj = new SerializedContentChild();
		$this->expectException(ContentValidationException::class);
		$obj->save();

		$obj->vc_col1->value = 'foo';
		$obj->save();

		$data = $this->conn->fetchRecords("SELECT * FROM `".SerializedContentChild::TABLE_NAME()."` WHERE `id` = {$obj->id->value}");
		$this->assertEquals('foo', $obj->vc_col1->value);
		$this->assertEquals('', $obj->vc_col2->value);
		$this->assertNull($obj->int_col->value);
		$this->assertNull($obj->bool_col->value);
	}

	public function testSaveNonDefaultColumns()
	{
		$o1 = new SerializedContentNonDefaultColumn();
		$o1->name->setInputValue('foozizzle');
		$o1->nonDefaultCol->setInputValue('drooplizzle');
		$o1->save();

		$query = "SELECT * FROM `".SerializedContentNonDefaultColumn::TABLE_NAME()."` WHERE `id` = {$o1->id->value}";
		$data = $this->conn->fetchRecords($query);

		$this->assertEquals($o1->nonDefaultCol->value, $data[0]->non_default);
	}

	/**
	 * @throws NotImplementedException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws RecordNotFoundException No record exists that matches the id value.
	 */
    public function testSaveNullValues()
    {
	    $obj = new SerializedContentChild();
	    $obj->vc_col1->value = 'foo';
	    $obj->vc_col2->value = null;
	    $obj->save();

	    $data = $this->conn->fetchRecords("SELECT * FROM `".SerializedContentChild::TABLE_NAME()."` WHERE `id` = {$obj->id->value}");

	    $this->assertNotNull($data[0]->vc_col1);
	    $this->assertNull($data[0]->vc_col2);
	    $this->assertNull($data[0]->int_col);
	    $this->assertNull($data[0]->bool_col);

	    $obj->id->value = null; /* save new record */
	    $obj->vc_col1->value = null;
	    $obj->vc_col2->value = 'bar';
	    $obj->save();

	    $data = $this->conn->fetchRecords("SELECT * FROM `".SerializedContentChild::TABLE_NAME()."` WHERE `id` = {$obj->id->value}");

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
	 * @throws InvalidQueryException
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
	 * @throws InvalidQueryException
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
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
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
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
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
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
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
	 * @throws InvalidQueryException
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
    	$obj->readList('array_container', 'Littled\Tests\PageContent\SerializedContentTitleTestHarness', $query);
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
	 * @throws InvalidQueryException
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
	 * @throws NotImplementedException
	 */
	public function testTableNameValueIsNotSet()
	{
		$this->expectException(NotImplementedException::class);
		$this->obj::TABLE_NAME();
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
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
	 * @throws NotImplementedException
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
	 * @throws InvalidQueryException
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
	    $this->assertContains("has been deleted", $result);
    }

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws \Littled\Exception\InvalidQueryException
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
	 * @throws \Littled\Exception\InvalidQueryException
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
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testDeleteNonexistentID()
	{
		$obj = new SerializedContentChild();

		/* test invalid id value */
		$obj->id->setInputValue(997799);
		$status = $obj->delete();
		$this->assertContains("could not be found", $status);
	}
}