<?php
namespace Littled\Tests\PageContent\SiteSection;


use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use PHPUnit\Framework\TestCase;


class KeywordSectionContentTest extends TestCase
{
	/** @var int Test content type to use on test KSC objects. */
	const TEST_CONTENT_TYPE_ID = 8; /* "news" in damienjay database */
	/** @var int Test parent record id that is safe for testing deletion operations. */
	const TEST_PARENT_ID_FOR_DELETE = 998899;
	/** @var int Test parent record id that is safe for testing deletion operations. */
	const TEST_PARENT_ID_FOR_READ = 889988;
	/** @var KeywordSectionContent test KeywordSectionContent object */
	public $obj;
	/** @var MySQLConnection database connection */
	public $conn;

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$conn = new MySQLConnection();
		$query = "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES ('test read 1',".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ.",".
			KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.");";
		$query .= "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES ('test_read_2',".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ.",".
			KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.");";
		$query .= "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES ('testread3',".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ.",".
			KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.");";
		$query .= "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES ('test del 01',".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_DELETE.",".
			KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.");";
		$query .= "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES ('testdel_02',".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_DELETE.",".
			KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.");";
		$query .= "INSERT INTO `keyword` (`term`,`parent_id`,`type_id`) VALUES ('testdel_03',".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_DELETE.",".
			KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.");";
		$conn->query($query);
	}

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		$conn = new MySQLConnection();
		$query = "DELETE FROM `keyword` WHERE parent_id = ".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_DELETE.
			" AND ".KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.";";
		$query .= "DELETE FROM `keyword` WHERE parent_id = ".
			KeywordSectionContentTest::TEST_PARENT_ID_FOR_READ.
			" AND ".KeywordSectionContentTest::TEST_CONTENT_TYPE_ID.";";
		$conn->query($query);
	}

	public function setUp()
	{
		parent::setUp();
		$this->conn = new MySQLConnection();
		$this->obj = new KeywordSectionContent();
	}

	/**
	 * @param int $parent_id
	 * @param int $type_id
	 * @return int Number of matching records.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function getKeywordCount($parent_id, $type_id)
	{
		$query = "SELECT COUNT(1) as `count` FROM `keyword`".
			" WHERE `parent_id` = {$parent_id} AND `type_id` = {$type_id}";
		return($this->conn->fetchRecords($query)[0]->count);
	}

	public function testConstructorDefaultValues()
	{
		$this->assertNull($this->obj->id->value);
		$this->assertNull($this->obj->contentProperties->id->value);
		$this->assertEquals('', $this->obj->keywordInput->value);
		$this->assertEquals('kwte', $this->obj->keywordInput->key);
		$this->assertFalse($this->obj->keywordInput->isDatabaseField);
		$this->assertTrue(is_array($this->obj->keywords));
		$this->assertEquals(0, count($this->obj->keywords));
		$this->assertFalse($this->obj->hasKeywordData());
	}

	public function testConstructorPassedValues()
	{
		$obj = new KeywordSectionContent(83, 629, 'biz');
		$this->assertEquals(83, $obj->id->value);
		$this->assertEquals(629, $obj->contentProperties->id->value);
		$this->assertEquals('bizte', $obj->keywordInput->key);
	}

	public function testAddKeyword()
	{
		$prev_count = count($this->obj->keywords);

		$this->obj->addKeyword('test new term');
		$this->assertEquals($prev_count+1, count($this->obj->keywords));
		$this->assertEquals('test new term', $this->obj->keywords[0]->term->value);

		$this->obj->addKeyword('test II new term');
		$this->assertEquals($prev_count+2, count($this->obj->keywords));
		$this->assertEquals('test II new term', $this->obj->keywords[1]->term->value);
	}

	public function testClearKeywordData()
	{
		$this->obj->keywordInput->value = "foo,bar,biz,bash";
		$this->obj->keywords = array('foo', 'bar', 'biz', 'bash');
		$this->obj->hasKeywordData = true;

		$this->obj->clearKeywordData();

		$this->assertEquals('', $this->obj->keywordInput->value);
		$this->assertTrue(is_array($this->obj->keywords));
		$this->assertEquals(0, count($this->obj->keywords));
		$this->assertFalse($this->obj->hasKeywordData());
	}

	public function testCollectFromInput()
	{
		$input = array(
			$this->obj->id->key => '47',
			$this->obj->contentProperties->id->key => '629'
		);

		$this->obj->collectRequestData($input);

		$this->assertEquals(47, $this->obj->id->value);
		$this->assertEquals(629, $this->obj->contentProperties->id->value);
	}

	public function testCollectKeywordInputWhenEmpty()
	{
		$this->obj->collectKeywordInput();
		$this->assertEquals('', $this->obj->keywordInput->value);
		$this->assertTrue(is_array($this->obj->keywords));
		$this->assertEquals(0, count($this->obj->keywords));
		$this->assertFalse($this->obj->hasKeywordData());
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testCollectKeywordInput()
	{
		$src = array($this->obj->keywordInput->key => "foo , bar, biz,bash, hhoo,haa dee,didly, dah ");
		$this->obj->collectKeywordInput($src);
		$keywords = $this->obj->getKeywordTermsArray(false);
		$this->assertContains('foo', $keywords);
		$this->assertContains('bar', $keywords);
		$this->assertContains('biz', $keywords);
		$this->assertContains('bash', $keywords);
		$this->assertContains('hhoo', $keywords);
		$this->assertContains('haa dee', $keywords);
		$this->assertContains('didly', $keywords);
		$this->assertContains('dah', $keywords);
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testCollectKeywordInputWithBadValues()
	{
		$src = array($this->obj->keywordInput->key => ",,foo , bar, 0,,625,before script <script>print ('what');</script> after script, dah ,");
		$this->obj->collectKeywordInput($src);
		$keywords = $this->obj->getKeywordTermsArray(false);
		$this->assertEquals(6, count($keywords));
		$this->assertContains('foo', $keywords);
		$this->assertContains('0', $keywords);
		$this->assertContains('625', $keywords);
		$this->assertContains("before script print (&#39;what&#39;); after script", $keywords);
		$this->assertContains('dah', $keywords);
	}

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testDelete()
	{
		try {
			$this->obj->delete();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals('Id not provided.', $ex->getMessage());
		}

		$this->obj->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		try {
			$this->obj->delete();
		}
		catch(NotImplementedException $ex) {
			$this->assertEquals('TABLE_NAME() not implemented in inherited class.', $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testDeleteKeywords()
	{
		try {
			$this->obj->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. A parent record was not provided.", $ex->getMessage());
		}

		$this->obj->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		$this->obj->contentProperties->id->value = null;
		try {
			$this->obj->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. A content type was not provided.", $ex->getMessage());
		}

		$this->obj->id->value = null;
		$this->obj->contentProperties->id->value = self::TEST_CONTENT_TYPE_ID;
		try {
			$this->obj->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. A parent record was not provided.", $ex->getMessage());
		}

		$this->assertGreaterThan(0, $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID));

		$this->obj->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		$this->obj->contentProperties->id->value = self::TEST_CONTENT_TYPE_ID;
		$this->obj->deleteKeywords();
		$this->assertEquals(0, $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID));
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testFormatKeywordListFromObjectProperties()
	{
		$keywords = $this->obj->formatKeywordList(false);
		$this->assertEquals("", $keywords);

		array_push($this->obj->keywords, new Keyword('foo',
			self::TEST_PARENT_ID_FOR_DELETE,
			self::TEST_CONTENT_TYPE_ID));
		array_push($this->obj->keywords, new Keyword('biz bash',
			self::TEST_PARENT_ID_FOR_DELETE,
			self::TEST_CONTENT_TYPE_ID));
		array_push($this->obj->keywords, new Keyword('6425',
			self::TEST_PARENT_ID_FOR_DELETE,
			self::TEST_CONTENT_TYPE_ID));
		$keywords = $this->obj->formatKeywordList(false);
		$this->assertEquals("foo, biz bash, 6425", $keywords);
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testFormatKeywordListFromDatabase()
	{
		try {
			$this->obj->formatKeywordList();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. Record not specified.", $ex->getMessage());
		}

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		try {
			$this->obj->formatKeywordList();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. Content type not specified.", $ex->getMessage());
		}

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ+1);
		$this->obj->contentProperties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$this->assertEquals("", $this->obj->formatKeywordList());

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		$this->assertEquals("test read 1, testread3, test_read_2", $this->obj->formatKeywordList());
	}

	public function testHasKeywordData()
	{
		$this->assertFalse($this->obj->hasKeywordData());

		$this->obj->keywordInput->setInputValue('a');
		$this->assertTrue($this->obj->hasKeywordData());

		$this->obj->keywordInput->setInputValue('0');
		$this->assertTrue($this->obj->hasKeywordData());

		$this->obj->keywordInput->setInputValue(' first, second,,third, last ');
		$this->assertTrue($this->obj->hasKeywordData());

		$this->obj->keywordInput->setInputValue(null);
		$this->assertFalse($this->obj->hasKeywordData());

		array_push($this->obj->keywords, new Keyword(null, self::TEST_CONTENT_TYPE_ID, self::TEST_PARENT_ID_FOR_READ));
		$this->assertFalse($this->obj->hasKeywordData());

		$this->obj->keywords[0]->term->setInputValue('a');
		$this->assertTrue($this->obj->hasKeywordData());

		array_push($this->obj->keywords, new Keyword('test keyword', self::TEST_CONTENT_TYPE_ID, self::TEST_PARENT_ID_FOR_READ));
		$this->assertTrue($this->obj->hasKeywordData());

		array_push($this->obj->keywords, new Keyword(' spaced  ', self::TEST_CONTENT_TYPE_ID, self::TEST_PARENT_ID_FOR_READ));
		$this->assertTrue($this->obj->hasKeywordData());
	}

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testSave()
	{
		try {
			$this->obj->save();
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals("A content type was not specified.", $ex->getMessage());
		}

		$this->obj->contentProperties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		try {
			$this->obj->save();
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals("Record has no data to save.", $ex->getMessage());
		}

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		try {
			$this->obj->save();
		}
		catch (NotImplementedException $ex) {
			$this->assertEquals("TABLE_NAME() not implemented in inherited class.", $ex->getMessage());
		}
	}

    /**
     * @throws ContentValidationException
     * @throws \Littled\Exception\ConfigurationUndefinedException
     * @throws \Littled\Exception\ConnectionException
     * @throws \Littled\Exception\InvalidQueryException
     */
	public function testSaveKeywords()
	{
		try {
			$this->obj->saveKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. A parent record was not provided.", $ex->getMessage());
		}

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_DELETE);
		try {
			$this->obj->saveKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Could not perform operation. A content type was not provided.", $ex->getMessage());
		}

		/* test it silently skips over saving if there are no terms to save */
		$this->obj->contentProperties->id->setInputValue(self::TEST_PARENT_ID_FOR_DELETE);
		$this->obj->saveKeywords();

		$this->assertEquals(0, count($this->obj->keywords));

		$this->obj->addKeyword('test new term');

		try {
			$this->obj->saveKeywords();
		}
		catch(NotImplementedException $ex) {
			$this->assertEquals("TABLE_NAME() not implemented in inherited class.", $ex->getMessage());
		}
	}

    /**
     * @throws ContentValidationException
     */
	public function testValidateInput()
	{
		try {
			$this->obj->validateInput();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Errors were found in the content.", $ex->getMessage());
			$this->assertEquals(1, count($this->obj->validationErrors));
			$this->assertContains("Content type is required.", $this->obj->validationErrors);
		}

		$this->obj->contentProperties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$this->obj->validateInput();
		$this->assertEquals(0, count($this->obj->validationErrors));

		$this->obj->addKeyword('test');
		$this->obj->keywords[0]->term->value = '';
		try {
		    $this->obj->validateInput();
        }
        catch(ContentValidationException $ex) {
		    $this->assertEquals("Errors were found in the content.", $ex->getMessage());
		    $this->assertContains("Keyword is required.", $this->obj->validationErrors);
        }
	}
}