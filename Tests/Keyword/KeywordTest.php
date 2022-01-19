<?php
namespace Littled\Tests\Keyword;

use Littled\Database\MySQLConnection;
use Littled\Keyword\Keyword;
use PHPUnit\Framework\TestCase;

/**
 * Class KeywordTest
 * @package Littled\Keyword
 */
class KeywordTest extends TestCase
{
	const TEST_KEYWORD_TERM = 'Unit Test';
	const TEST_PARENT_ID = 98989898;
	const TEST_TYPE_ID = 4; /* type = "sketchbooks" */

	/** @var Keyword Test keyword object. */
	public $obj;
	/** @var MySQLConnection Database connection. */
	public $conn;

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function setUpBeforeClass()
	{
		$query = "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES (".
			"'".KeywordTest::TEST_KEYWORD_TERM."',".KeywordTest::TEST_PARENT_ID.",".KeywordTest::TEST_TYPE_ID.")";
		$conn = new MySQLConnection();
		$conn->query($query);
	}

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function tearDownAfterClass()
	{
		$query = "DELETE FROM `keyword` WHERE term LIKE '".KeywordTest::TEST_KEYWORD_TERM."%'";
		$conn = new MySQLConnection();
		$conn->query($query);
	}

	public function setUp()
	{
		$this->obj = new Keyword('', null, null);
		$this->conn = new MySQLConnection();
	}

	public function getKeywordCount()
	{
		$query = "SELECT COUNT(1) AS `record_count` FROM `keyword`";
		$data = $this->conn->fetchRecords($query);
		return ($data[0]->record_count);
	}

	public function testInitialize()
	{
		$obj = new Keyword('unit test', 8924, 9);
		$this->assertEquals('unit test', $obj->term->value);
		$this->assertEquals(9, $obj->type_id->value);
		$this->assertEquals(8924, $obj->parent_id->value);
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testExists()
	{
		$this->obj->term->setInputValue(KeywordTest::TEST_KEYWORD_TERM);
		$this->obj->type_id->setInputValue(KeywordTest::TEST_TYPE_ID);
		$this->obj->parent_id->setInputValue(KeywordTest::TEST_PARENT_ID);
		$this->assertTrue($this->obj->exists());

		$this->obj->term->setInputValue(KeywordTest::TEST_KEYWORD_TERM." foo bar");
		$this->assertFalse($this->obj->exists());

		$this->obj->term->setInputValue(KeywordTest::TEST_KEYWORD_TERM);
		$this->obj->type_id->setInputValue(KeywordTest::TEST_TYPE_ID + 10);
		$this->assertFalse($this->obj->exists());

		$this->obj->type_id->setInputValue(KeywordTest::TEST_TYPE_ID);
		$this->obj->parent_id->setInputValue(KeywordTest::TEST_PARENT_ID+1);
		$this->assertFalse($this->obj->exists());

		$this->obj->term->setInputValue(KeywordTest::TEST_KEYWORD_TERM);
		$this->obj->type_id->setInputValue(null);
		$this->obj->parent_id->setInputValue(null);
		$this->assertFalse($this->obj->exists());
	}

	public function testHasData()
	{
		/* default value */
		$this->assertFalse($this->obj->hasData());

		/* search term set */
		$this->obj->term->setInputValue('foo');
		$this->assertFalse($this->obj->hasData());

		/* setting other property values */
		$this->obj->type_id->setInputValue(99);
		$this->obj->parent_id->setInputValue(989898);
		$this->assertTrue($this->obj->hasData());

		$this->obj->term->setInputValue('');
		$this->assertFalse($this->obj->hasData());

		$this->obj->term->setInputValue(null);
		$this->assertFalse($this->obj->hasData());

		$this->obj->term->setInputValue('biz bash');
		$this->assertTrue($this->obj->hasData());
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testSaveWithDefaultValue()
	{
		$prev_count = $this->getKeywordCount();

		$this->obj->save();

		$new_count = $this->getKeywordCount();
		$this->assertEquals($prev_count, $new_count);
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testSaveDuplicateValue()
	{
		$obj = new Keyword(KeywordTest::TEST_KEYWORD_TERM, KeywordTest::TEST_PARENT_ID, KeywordTest::TEST_TYPE_ID);
		$prev_count = $this->getKeywordCount();

		$obj->save();

		$new_count = $this->getKeywordCount();
		$this->assertEquals($prev_count, $new_count);
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testSave()
	{
		$obj = new Keyword(KeywordTest::TEST_KEYWORD_TERM . " testSave()", KeywordTest::TEST_PARENT_ID, KeywordTest::TEST_TYPE_ID);
		$prev_count = $this->getKeywordCount();

		$obj->save();
		$new_count = $this->getKeywordCount();
		$this->assertEquals($prev_count+1, $new_count);
	}

	public function testClear()
	{
		$obj = new Keyword(KeywordTest::TEST_KEYWORD_TERM, KeywordTest::TEST_PARENT_ID, KeywordTest::TEST_TYPE_ID);
		$obj->clearValues();
		$this->assertEquals('', $obj->term->value);
		$this->assertEquals(null, $obj->type_id->value);
		$this->assertEquals(null, $obj->parent_id->value);
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testDelete()
	{
		$obj = new Keyword(KeywordTest::TEST_KEYWORD_TERM . " testDelete()", KeywordTest::TEST_PARENT_ID, KeywordTest::TEST_TYPE_ID);
		$obj->save();

		$prev_count = $this->getKeywordCount();

		$status = $obj->delete();
		$new_count = $this->getKeywordCount();
		$this->assertEquals($prev_count-1, $new_count);
		$this->assertEquals("The keyword \"{$obj->term->value}\" was successfully deleted.", $status);
	}
}