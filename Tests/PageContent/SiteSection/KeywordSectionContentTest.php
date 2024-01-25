<?php
namespace LittledTests\PageContent\SiteSection;

use Exception;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\Keyword\Keyword;
use Littled\PageContent\SiteSection\KeywordSectionContent;
use LittledTests\DataProvider\PageContent\SiteSection\KeywordSectionContentTestData;
use LittledTests\TestHarness\PageContent\SiteSection\KeywordSectionContentNonDefaultKey;
use LittledTests\TestHarness\PageContent\SiteSection\KeywordSectionContentTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\KeywordTestTableTestHarness;
use PHPUnit\Framework\TestCase;


class KeywordSectionContentTest extends TestCase
{
	/** @var int Test content type to use on test KSC objects. */
	const TEST_CONTENT_TYPE_ID = 6037; /* "test_table" in littledamien database */
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

        $term = 'test-read3';
        $stmt->execute();

        $parent_id = self::TEST_PARENT_ID_FOR_DELETE;
        $term = 'test del 01';
        $stmt->execute();

        $term = 'test-del_02';
        $stmt->execute();

        $term = 'test-del_03';
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

	/**
	 * @param int $parent_id
	 * @param int $type_id
	 * @return int Number of matching records.
	 * @throws InvalidQueryException|Exception
     */
	public function getKeywordCount(int $parent_id, int $type_id): int
    {
		$query = 'SEL'.'ECT COUNT(1) as `count` FROM `keyword`'.
			" WHERE `parent_id` = $parent_id AND `type_id` = $type_id";
		return((new MySQLConnection())->fetchRecords($query)[0]->count);
	}

    /**
     * @return void
     * @throws Exception
     */
    public function testAddKeyword()
    {
        $o = new KeywordSectionContentTestHarness();
        $prev_count = count($o->keywords);

        try {
            $o->addKeyword('test new term');
            self::fail('Expected exception not thrown.');
        }
        catch(Exception $ex) {
            self::assertMatchesRegularExpression('/parent record was not provided/i', $ex->getMessage());
        }

        $o->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
        $o->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
        $o->addKeyword('test new term');
        self::assertCount($prev_count + 1, $o->keywords);
        self::assertEquals('test new term', $o->keywords[0]->term->value);

        $o->addKeyword('test II new term');
        self::assertCount($prev_count + 2, $o->keywords);
        self::assertEquals('test II new term', $o->keywords[1]->term->value);
        self::assertTrue($o->keywords[1]->parent_id->required);

        $o->addKeyword('3rd term', false);
        self::assertCount($prev_count + 3, $o->keywords);
        self::assertEquals('3rd term', $o->keywords[2]->term->value);
        self::assertFalse($o->keywords[2]->parent_id->required);
    }

    public function testClearKeywordData()
    {
        $o = new KeywordSectionContentTestHarness();
        $o->keyword_input->value = "foo,bar,biz,bash";
        $o->keywords = array('foo', 'bar', 'biz', 'bash');

        $o->clearKeywordData();

        self::assertEquals('', $o->keyword_input->value);
        self::assertIsArray($o->keywords);
        self::assertCount(0, $o->keywords);
        self::assertFalse($o->hasKeywordData());
    }

	/**
	 * @throws Exception
	 */
	public function testCollectFromInput()
	{
        $o = new KeywordSectionContentTestHarness();
		$input = array(
			$o->id->key => '47',
			$o->content_properties->id->key => '629'
		);

		$o->collectRequestData($input);

		self::assertEquals(47, $o->id->value);
		self::assertEquals(629, $o->content_properties->id->value);
	}

	/**
	 * @throws Exception
	 */
	public function testCollectKeywordInputWhenEmpty()
	{
        $o = new KeywordSectionContentTestHarness();
		$o->collectKeywordInput();
		self::assertEquals('', $o->keyword_input->value);
		self::assertIsArray($o->keywords);
		self::assertCount(0, $o->keywords);
		self::assertFalse($o->hasKeywordData());
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws Exception
	 */
	public function testCollectKeywordInput()
	{
        $o = new KeywordSectionContentTestHarness();
		$src = array($o->keyword_input->key => "foo , bar, biz,bash, hhoo,haa dee,didly, dah ");
		$o->collectKeywordInput($src);
		$keywords = $o->getKeywordTermsArray(false);
		self::assertContains('foo', $keywords);
		self::assertContains('bar', $keywords);
		self::assertContains('biz', $keywords);
		self::assertContains('bash', $keywords);
		self::assertContains('hhoo', $keywords);
		self::assertContains('haa dee', $keywords);
		self::assertContains('didly', $keywords);
		self::assertContains('dah', $keywords);

		self::assertFalse($o->keywords[0]->parent_id->required);
		self::assertFalse($o->keywords[count($o->keywords)-1]->parent_id->required);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws Exception
	 */
	public function testCollectKeywordInputWithBadValues()
	{
        $o = new KeywordSectionContentTestHarness();
        $inject_test = "[before script]<'.'script>alert('what');<'.'/script>[after script]";
        $key = ",foo , bar, 0,,625,$inject_test, dah ,";
		$src = array($o->keyword_input->key => $key);
		$o->collectKeywordInput($src);
		$keywords = $o->getKeywordTermsArray(false);
		self::assertCount(6, $keywords);
		self::assertContains('foo', $keywords);
		self::assertContains('0', $keywords);
		self::assertContains('625', $keywords);
		self::assertContains("[before script]alert(&#039;what&#039;);[after script]", $keywords);
		self::assertContains('dah', $keywords);
	}

    public function testConstructorDefaultValues()
    {
        $o = new KeywordSectionContentTestHarness();
        self::assertNull($o->id->value);
        self::assertNotNull($o->content_properties->id->value);
        self::assertEquals('', $o->keyword_input->value);
        self::assertEquals('kwText', $o->keyword_input->key);
        self::assertFalse($o->keyword_input->is_database_field);
        self::assertIsArray($o->keywords);
        self::assertCount(0, $o->keywords);
        self::assertFalse($o->hasKeywordData());
    }

    /**
     * @throws ConfigurationUndefinedException
     */
    public function testConstructorPassedValues()
    {
        $o = new KeywordSectionContentTestHarness(83, 629);
        self::assertEquals(83, $o->id->value);
        self::assertEquals(629, $o->content_properties->id->value);
        self::assertEquals('kwText', $o->keyword_input->key);
    }

    /**
	 * @throws ContentValidationException
	 * @throws NotImplementedException
     */
	public function testDelete()
	{
        $o = new KeywordSectionContentTestHarness();
        // record id not provided
		try {
			$o->delete();
		}
		catch(ContentValidationException $ex) {
			self::assertEquals('Id not provided.', $ex->getMessage());
		}

        // content type not provided
		$o->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		try {
			$o->delete();
		}
		catch(NotImplementedException $ex) {
			self::assertMatchesRegularExpression('/Table name not set./', $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws Exception
	 */
	public function testDeleteKeywords()
	{
        $o = new KeywordSectionContentTestHarness();
        $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID);

        // Attempting to delete keywords without specifying their parent record or content type.
		try {
			$o->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			self::assertMatchesRegularExpression("/A parent record was not provided/", $ex->getMessage());
		}

        // Attempting to delete keywords without specifying a content type.
		$o->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		$o->content_properties->id->value = null;
		try {
			$o->deleteKeywords();
		}
		catch(Exception $ex) {
			self::assertMatchesRegularExpression("/A content type was not specified/", $ex->getMessage());
		}

        // Attempting to delete keyword while specifying content type but not parent record id
		$o->id->value = null;
		$o->content_properties->id->value = self::TEST_CONTENT_TYPE_ID;
		try {
			$o->deleteKeywords();
		}
		catch(ContentValidationException $ex) {
			self::assertMatchesRegularExpression("/A parent record was not provided/", $ex->getMessage());
		}

        // Confirm that the object still retains keyword list after attempts to delete them fail.
		self::assertGreaterThan(0, $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID));

        // Confirm object's keyword list is empty after deleting keyword database records
		$o->id->value = self::TEST_PARENT_ID_FOR_DELETE;
		$o->content_properties->id->value = self::TEST_CONTENT_TYPE_ID;
		$o->deleteKeywords();
		self::assertEquals(0, $this->getKeywordCount(self::TEST_PARENT_ID_FOR_DELETE, self::TEST_CONTENT_TYPE_ID));
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     */
	public function testFormatKeywordListFromObjectProperties()
	{
        $o = new KeywordSectionContentTestHarness();
		$keywords = $o->formatKeywordList(false);
		self::assertEquals("", $keywords);

		$o->keywords[] = new Keyword('foo',
            self::TEST_PARENT_ID_FOR_DELETE,
            self::TEST_CONTENT_TYPE_ID);
		$o->keywords[] = new Keyword('biz bash',
            self::TEST_PARENT_ID_FOR_DELETE,
            self::TEST_CONTENT_TYPE_ID);
		$o->keywords[] = new Keyword('6425',
            self::TEST_PARENT_ID_FOR_DELETE,
            self::TEST_CONTENT_TYPE_ID);
		$keywords = $o->formatKeywordList(false);
		self::assertEquals("foo, biz bash, 6425", $keywords);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     */
	public function testFormatKeywordListFromDatabase()
	{
        $o = new KeywordSectionContentTestHarness();
		try {
			$o->formatKeywordList();
		}
		catch(ContentValidationException $ex) {
			self::assertMatchesRegularExpression("/could not perform operation.*parent record .*not provided/i", $ex->getMessage());
		}

		$o->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		try {
			$o->formatKeywordList();
		}
		catch(ContentValidationException $ex) {
			self::assertEquals("Could not perform operation. Content type not specified.", $ex->getMessage());
		}

		$o->id->setInputValue(self::TEST_PARENT_ID_FOR_READ+1);
		$o->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		self::assertEquals("", $o->formatKeywordList());

		$o->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		self::assertEquals("test read 1, test-read3, test_read_2", $o->formatKeywordList());
	}

    /**
     * @throws ContentValidationException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     */
    function testGetKeywordTermsArray()
    {
        $o = new KeywordSectionContentTestHarness();
        $o->setRecordId(889988);
        $terms = $o->getKeywordTermsArray();
        self::assertGreaterThan(0, count($terms));
        self::assertMatchesRegularExpression('/test/i', $terms[0]);
    }

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\SiteSection\KeywordSectionContentTestDataProvider::hasKeywordDataTestProvider()
     * @param KeywordSectionContentTestData $data
     * @return void
     */
	public function testHasKeywordData(KeywordSectionContentTestData $data)
	{
        $o = new KeywordSectionContentTestHarness();
        $data->copy($o);
        self::assertEquals($data->expected, $o->hasKeywordData());
	}

    /**
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws ConfigurationUndefinedException
     */
    public function testRead()
    {
        $o = new KeywordTestTableTestHarness();
        $o->setRecordId(KeywordTestTableTestHarness::EXISTING_PARENT_ID);
        $o->read();

        // confirm that keywords are retrieved from the database when retrieving record data
        self::assertGreaterThan(0, $o->keywords);

        // confirm that the keyword_input property value contains the keyword terms retrieved from the database
        self::assertNotEmpty($o->keyword_input->value);
        foreach($o->keywords as $keyword) {
            self::assertStringContainsString($keyword->term->value, $o->keyword_input->value);
        }
    }

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws RecordNotFoundException
	 */
	public function testSave()
	{
        $o = new KeywordSectionContentTestHarness();
		try {
			$o->save();
		}
		catch (Exception $e) {
			self::assertInstanceOf(NotImplementedException::class, $e);
			self::assertMatchesRegularExpression("/table name not set/i", $e->getMessage());
		}

		$o->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		try {
			$o->save();
		}
		catch (NotImplementedException $ex) {
			self::assertMatchesRegularExpression("/table name not set/i", $ex->getMessage());
		}
        catch(ContentValidationException $ex) {
            self::assertMatchesRegularExpression('/content type was not specified/i', $ex->getMessage());
        }
    }

    /**
     * @throws ContentValidationException
     * @throws Exception
     */
	public function testSaveKeywords()
	{
        $o = new KeywordSectionContentTestHarness();
		try {
			$o->saveKeywords();
		}
		catch(ContentValidationException $ex) {
			self::assertMatchesRegularExpression("/could not serialize keyword/i", $ex->getMessage());
			self::assertMatchesRegularExpression("/parent record.* not provided/i", $ex->getMessage());
		}

		$o->id->setInputValue(self::TEST_PARENT_ID_FOR_DELETE);
		try {
			$o->saveKeywords();
		}
		catch(ContentValidationException $ex) {
			self::assertMatchesRegularExpression("/content type was not specified/i", $ex->getMessage());
		}

		/* test it silently skips over saving if there are no terms to save */
		$o->content_properties->id->setInputValue(self::TEST_PARENT_ID_FOR_DELETE);
		$o->saveKeywords();

		self::assertCount(0, $o->keywords);

		$o->addKeyword('test new term');

        $o->saveKeywords();
    }

    function testSetKeywordKey()
    {
        $o = new KeywordSectionContentTestHarness();
        self::assertEquals('kw', $o->getKeywordKey());
        self::assertEquals('kwText', $o->keyword_input->key);

        $c = new KeywordSectionContentNonDefaultKey();
        self::assertEquals('ckw', $c->getKeywordKey());
        self::assertEquals('ckwText', $c->keyword_input->key);
    }

    /**
     * @throws ContentValidationException
     * @throws Exception
     */
	public function testValidateInput()
	{
        $o = new KeywordSectionContentTestHarness();
		try {
			$o->validateInput();
		}
		catch(ContentValidationException $ex) {
			self::assertEquals("Errors were found in the content.", $ex->getMessage());
			self::assertGreaterThan(0, count($o->validationErrors()));
		}

        $o->id->setInputValue(self::TEST_PARENT_ID_FOR_READ);
		$o->content_properties->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
        $o->content_properties->read();
        $o->validateInput();
		self::assertCount(0, $o->validationErrors());

		$o->addKeyword('test');
		$o->keywords[0]->term->value = '';
		try {
		    $o->validateInput();
        }
        catch(ContentValidationException $ex) {
			$expected_err = "Keyword is required.";
		    self::assertEquals($o->validation_message, $ex->getMessage());
		    self::assertContains($expected_err, $o->validationErrors());
        }
	}
}