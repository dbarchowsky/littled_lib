<?php
namespace Littled\Tests\PageContent\SiteSection;


use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use Littled\Tests\PageContent\SiteSection\TestHarness\KeywordSectionContentNonDefaultKey;
use Littled\Tests\PageContent\SiteSection\TestHarness\KeywordSectionContentTestHarness;
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
	public KeywordSectionContent $obj;
	/** @var MySQLConnection database connection */
	public MySQLConnection $conn;

	/**
	 * @throws Exception
     */
	public static function setUpBeforeClass(): void
	{
		parent::setUpBeforeClass();
		$conn = new MySQLConnection();
        $mysqli = $conn::getMysqli();
        $term = $parent_id = null;
        $content_type_id = self::TEST_CONTENT_TYPE_ID;
        $query = 'INSERT INTO `keyword` (`term`, `parent_id`, `type_id`) VALUES (?,?,?)';
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('sii', $term, $parent_id, $content_type_id);

        $parent_id = self::TEST_PARENT_ID_FOR_READ;
        $term = 'test read 1';
        $stmt->execute();

        $term = 'test_read_2';
        $stmt->execute();

        $term = 'testread3';
        $stmt->execute();

        $parent_id = self::TEST_PARENT_ID_FOR_DELETE;
        $term = 'test del 01';
        $stmt->execute();

        $term = 'testdel_02';
        $stmt->execute();

        $term = 'testdel_03';
        $stmt->execute();
	}

	/**
	 * @throws InvalidQueryException|Exception
     */
	public static function tearDownAfterClass(): void
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

	function __construct()
	{
		parent::__construct();
		$this->conn = new MySQLConnection();
		$this->obj = new KeywordSectionContentTestHarness();
	}

	/**
	 * @param int $parent_id
	 * @param int $type_id
	 * @return int Number of matching records.
	 * @throws InvalidQueryException|Exception
     */
	public function getKeywordCount(int $parent_id, int $type_id): int
    {
		$query = "SELECT COUNT(1) as `count` FROM `keyword`".
			" WHERE `parent_id` = $parent_id AND `type_id` = $type_id";
		return($this->conn->fetchRecords($query)[0]->count);
	}

	public function testConstructorDefaultValues()
	{
		$this->assertNull($this->obj->id->value);
		$this->assertNull($this->obj->content_properties->id->value);
		$this->assertEquals('', $this->obj->keywordInput->value);
		$this->assertEquals('kwText', $this->obj->keywordInput->key);
		$this->assertFalse($this->obj->keywordInput->is_database_field);
		$this->assertTrue(is_array($this->obj->keywords));
		$this->assertCount(0, $this->obj->keywords);
		$this->assertFalse($this->obj->hasKeywordData());
	}

	public function testConstructorPassedValues()
	{
		$obj = new KeywordSectionContentTestHarness(83, 629);
		$this->assertEquals(83, $obj->id->value);
		$this->assertEquals(629, $obj->content_properties->id->value);
		$this->assertEquals('kwText', $obj->keywordInput->key);
	}

    /**
     * @return void
     * @throws Exception
     */
	public function testAddKeyword()
	{
        $obj = new KeywordSectionContentTestHarness();
        $prev_count = count($obj->keywords);

        try {
            $obj->addKeyword('test new term');
        }
        catch(Exception $ex) {
            $this->assertMatchesRegularExpression('/parent record was not provided/i', $ex->getMessage());
        }

        $obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
        try {
            $obj->addKeyword('test new term');
        }
        catch(Exception $ex) {
            $this->assertMatchesRegularExpression('/content type was not specified/i', $ex->getMessage());
        }

        $obj->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$obj->addKeyword('test new term');
		$this->assertCount($prev_count + 1, $obj->keywords);
		$this->assertEquals('test new term', $obj->keywords[0]->term->value);

		$obj->addKeyword('test II new term');
		$this->assertCount($prev_count + 2, $obj->keywords);
		$this->assertEquals('test II new term', $obj->keywords[1]->term->value);
	}

	public function testClearKeywordData()
	{
		$this->obj->keywordInput->value = "foo,bar,biz,bash";
		$this->obj->keywords = array('foo', 'bar', 'biz', 'bash');

		$this->obj->clearKeywordData();

		$this->assertEquals('', $this->obj->keywordInput->value);
		$this->assertTrue(is_array($this->obj->keywords));
		$this->assertCount(0, $this->obj->keywords);
		$this->assertFalse($this->obj->hasKeywordData());
	}

	public function testCollectFromInput()
	{
		$input = array(
			$this->obj->id->key => '47',
			$this->obj->content_properties->id->key => '629'
		);

		$this->obj->collectRequestData($input);

		$this->assertEquals(47, $this->obj->id->value);
		$this->assertEquals(629, $this->obj->content_properties->id->value);
	}

	public function testCollectKeywordInputWhenEmpty()
	{
		$this->obj->collectKeywordInput();
		$this->assertEquals('', $this->obj->keywordInput->value);
		$this->assertTrue(is_array($this->obj->keywords));
		$this->assertCount(0, $this->obj->keywords);
		$this->assertFalse($this->obj->hasKeywordData());
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
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
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     */
	public function testCollectKeywordInputWithBadValues()
	{
        $inject_test = "[before script]<script>alert('what');</script>[after script]";
        $key = ",foo , bar, 0,,625,$inject_test, dah ,";
		$src = array($this->obj->keywordInput->key => $key);
		$this->obj->collectKeywordInput($src);
		$keywords = $this->obj->getKeywordTermsArray(false);
		$this->assertCount(6, $keywords);
		$this->assertContains('foo', $keywords);
		$this->assertContains('0', $keywords);
		$this->assertContains('625', $keywords);
		$this->assertContains("[before script]alert(&#039;what&#039;);[after script]", $keywords);
		$this->assertContains('dah', $keywords);
	}

	/**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
     */
	public function testDelete()
	{
        // record id not provided
		try {
			$this->obj->delete();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals('Id not provided.', $ex->getMessage());
		}

        // content type not provided
		$this->obj->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		try {
			$this->obj->delete();
		}
		catch(NotImplementedException $ex) {
			$this->assertMatchesRegularExpression('/Table name not set./', $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws Exception
	 */
	public function testDeleteKeywords()
	{
        $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID);

        // Attempting to delete keywords without specifying their parent record or content type.
		try {
			$this->obj->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertMatchesRegularExpression("/A parent record was not provided/", $ex->getMessage());
		}

        // Attempting to delete keywords without specifying a content type.
		$this->obj->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		$this->obj->content_properties->id->value = null;
		try {
			$this->obj->deleteKeywords();
		}
		catch(Exception $ex) {
			$this->assertMatchesRegularExpression("/A content type was not specified/", $ex->getMessage());
		}

        // Attempting to delete keyword while specifying content type but not parent record id
		$this->obj->id->value = null;
		$this->obj->content_properties->id->value = self::TEST_CONTENT_TYPE_ID;
		try {
			$this->obj->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			$this->assertMatchesRegularExpression("/A parent record was not provided/", $ex->getMessage());
		}

        // Confirm that the object still retains keyword list after attempts to delete them fail.
		$this->assertGreaterThan(0, $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID));

        // Confirm object's keyword list is empty after deleting keyword database records
		$this->obj->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		$this->obj->content_properties->id->value = self::TEST_CONTENT_TYPE_ID;
		$this->obj->deleteKeywords();
		$this->assertEquals(0, $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID));
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     */
	public function testFormatKeywordListFromObjectProperties()
	{
		$keywords = $this->obj->formatKeywordList(false);
		$this->assertEquals("", $keywords);

		$this->obj->keywords[] = new Keyword('foo',
            self::TEST_PARENT_ID_FOR_DELETE,
            self::TEST_CONTENT_TYPE_ID);
		$this->obj->keywords[] = new Keyword('biz bash',
            self::TEST_PARENT_ID_FOR_DELETE,
            self::TEST_CONTENT_TYPE_ID);
		$this->obj->keywords[] = new Keyword('6425',
            self::TEST_PARENT_ID_FOR_DELETE,
            self::TEST_CONTENT_TYPE_ID);
		$keywords = $this->obj->formatKeywordList(false);
		$this->assertEquals("foo, biz bash, 6425", $keywords);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
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
		$this->obj->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$this->assertEquals("", $this->obj->formatKeywordList());

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		$this->assertEquals("test read 1, testread3, test_read_2", $this->obj->formatKeywordList());
	}

    /**
     * @return void
     * @throws Exception
     */
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

        $this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
        $this->obj->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
        $this->obj->addKeyword('a');
        $this->assertTrue($this->obj->hasKeywordData());

		$this->obj->keywords[0]->term->setInputValue('');
		$this->assertFalse($this->obj->hasKeywordData());

		$this->obj->keywords[] = new Keyword('test keyword', self::TEST_CONTENT_TYPE_ID, self::TEST_PARENT_ID_FOR_READ);
		$this->assertTrue($this->obj->hasKeywordData());

		$this->obj->keywords[] = new Keyword(' spaced  ', self::TEST_CONTENT_TYPE_ID, self::TEST_PARENT_ID_FOR_READ);
		$this->assertTrue($this->obj->hasKeywordData());
	}

	/**
     * @throws NotImplementedException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws RecordNotFoundException
	 */
	public function testSave()
	{
		try {
			$this->obj->save();
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals("A content type was not specified.", $ex->getMessage());
		}

		$this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		try {
			$this->obj->save();
		}
		catch (NotImplementedException $ex) {
			$this->assertMatchesRegularExpression("/table name not set/i", $ex->getMessage());
		}
        catch(ContentValidationException $ex) {
            $this->assertMatchesRegularExpression('/content type was not specified/i', $ex->getMessage());
        }
    }

    /**
     * @throws ContentValidationException
     * @throws Exception
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
			$this->assertMatchesRegularExpression("/content type was not specified/i", $ex->getMessage());
		}

		/* test it silently skips over saving if there are no terms to save */
		$this->obj->content_properties->id->setInputValue(self::TEST_PARENT_ID_FOR_DELETE);
		$this->obj->saveKeywords();

		$this->assertCount(0, $this->obj->keywords);

		$this->obj->addKeyword('test new term');

        $this->obj->saveKeywords();
    }

    function testSetKeywordKey()
    {
        $o = new KeywordSectionContentTestHarness();
        $this->assertEquals('kw', $o->getKeywordKey());
        $this->assertEquals('kwText', $o->keywordInput->key);

        $c = new KeywordSectionContentNonDefaultKey();
        $this->assertEquals('ckw', $c->getKeywordKey());
        $this->assertEquals('ckwText', $c->keywordInput->key);
    }

    /**
     * @throws ContentValidationException
     * @throws Exception
     */
	public function testValidateInput()
	{
		try {
			$this->obj->validateInput();
		}
		catch(ContentValidationException $ex) {
			$this->assertEquals("Errors were found in the content.", $ex->getMessage());
			$this->assertGreaterThan(0, count($this->obj->validationErrors));
			$this->assertContains("Content type is required.", $this->obj->validationErrors);
		}

        $this->obj->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		$this->obj->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
        $this->obj->content_properties->read();
        $this->obj->validateInput();
		$this->assertCount(0, $this->obj->validationErrors);

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