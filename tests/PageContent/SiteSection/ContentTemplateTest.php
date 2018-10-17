<?php
namespace Littled\Tests\PageContent\SiteSection;


use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\SiteSection\ContentTemplate;
use PHPUnit\Framework\TestCase;

class ContentTemplateTest extends TestCase
{
	const UNIT_TEST_IDENTIFIER = 'unit test';

	/** @var ContentTemplate Test ContentTemplate object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;
	/** @var int ID of test content template record for reading. */
	public $test_record_id;

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public static function setUpBeforeClass()
	{
		$query = "INSERT INTO `".ContentTemplate::TABLE_NAME()."` (".
			"`site_section_id`, `name`, `path`, `location`".
			") VALUES (".
			CONTENT_TEMPLATE_CONTENT_TYPE_ID.", '".ContentTemplateTest::UNIT_TEST_IDENTIFIER." for reading', '/path/to/templates/template.php', 'local')";
		$conn = new MySQLConnection();
		$conn->query($query);
	}

	public static function tearDownAfterClass()
	{
		$query = "DELETE FROM `".ContentTemplate::TABLE_NAME()."` WHERE LOWER(`name`) LIKE '".ContentTemplateTest::UNIT_TEST_IDENTIFIER."%'";
		$conn = new MySQLConnection();
		$conn->query($query);
	}

	/**
	 * Set up object before each test.
	 */
	public function setUp()
	{
		parent::setUp();
		$this->obj = new ContentTemplate();
		$this->conn = new MySQLConnection();
	}

	/**
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function testTableName()
	{
		$this->assertEquals('content_template', $this->obj::TABLE_NAME());
	}

	public function testConstruct()
	{
		$obj = new ContentTemplate(5, 10, 'Test Section', '/path/to/templates/', 'template-file.php', 'top');
		$this->assertEquals(5, $obj->id->value);
		$this->assertEquals(10, $obj->parentID->value);
		$this->assertEquals('Test Section', $obj->name->value);
		$this->assertEquals('/path/to/templates/', $obj->templatePath->value);
		$this->assertEquals('template-file.php', $obj->templateFile->value);
		$this->assertEquals('top', $obj->location->value);
	}

	public function testConstructDefaultValues()
	{
		$obj = new ContentTemplate();
		$this->assertNull($obj->id->value);
		$this->assertNull($obj->parentID->value);
		$this->assertEquals('', $obj->name->value);
		$this->assertEquals('', $obj->templatePath->value);
		$this->assertEquals('', $obj->templateFile->value);
		$this->assertEquals('', $obj->location->value);
	}

	public function testHasData()
	{
		$this->assertFalse($this->obj->hasData());

		$this->obj->id->setInputValue(33);
		$this->assertTrue($this->obj->hasData());

		$this->obj->id->setInputValue(null);
		$this->assertFalse($this->obj->hasData());

		$this->obj->name->setInputValue('fookazi');
		$this->assertTrue($this->obj->hasData());

		$this->obj->name->setInputValue('');
		$this->obj->templateFile->setInputValue('mytemplate.php');
		$this->assertTrue($this->obj->hasData());

		$this->obj->templateFile->setInputValue('');
		$this->assertFalse($this->obj->hasData());

		$this->obj->templatePath->setInputValue('/path/to/templates/');
		$this->obj->contentTypeID->setInputValue(72);
		$this->obj->location->setInputValue('bottom');
		$this->assertFalse($this->obj->hasData());

		$this->obj->name->setInputValue('test name');
		$this->obj->templateFile->setInputValue('template.php');
		$this->assertTrue($this->obj->hasData());
	}

	public function testGetFullPath()
	{
		/* default path value */
		$this->assertEquals('', $this->obj->formatFullPath());

		/* just path */
		$this->obj->templateFile->setInputValue('template.php');
		$this->assertEquals($this->obj->templateFile->value, $this->obj->formatFullPath());

		/* path and template directory with no trailing slash */
		$this->obj->templatePath->setInputValue('/templates/html');
		$this->assertEquals(APP_BASE_DIR.'templates/html/template.php', $this->obj->formatFullPath());

		/* path and template directory with trailing slash */
		$this->obj->templatePath->value = "{$this->obj->templatePath->value}/";
		$this->assertEquals(APP_BASE_DIR.'templates/html/template.php', $this->obj->formatFullPath());

		/* location value set to SHARED */
		$this->obj->location->setInputValue('shared');
		$this->assertEquals(COMMON_TEMPLATE_DIR.'template.php', $this->obj->formatFullPath());

		/* location value set to SHARED CMS */
		$this->obj->location->setInputValue('shared-cms');
		$this->assertEquals(CMS_COMMON_TEMPLATE_DIR.'template.php', $this->obj->formatFullPath());
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function testValidateInput()
	{
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertEquals("Error validating content templates.", $ex->getMessage());
			$this->assertContains("Name is required.", $this->obj->validationErrors);
			$this->assertContains("Content type is required.", $this->obj->validationErrors);
			$this->assertContains("Template file is required.", $this->obj->validationErrors);
			$this->assertContains("Either a template path or location must be specified.", $this->obj->validationErrors);
		}

		$this->obj->name->setInputValue("Sketchbooks");
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertNotContains("Name is required.", $this->obj->validationErrors);
		}

		$this->obj->contentTypeID->setInputValue(2);
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertNotContains("Content type is required.", $this->obj->validationErrors);
		}

		$this->obj->templateFile->setInputValue('template.php');
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertNotContains("Template file is required.", $this->obj->validationErrors);
		}

		$this->obj->templatePath->setInputValue('templates/html/');
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			;
		}
		$this->assertEmpty($this->obj->validationErrors);
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testValidateInputDuplicateContentType()
	{
		$content_type_id = 33; /* must be a valid site_section id */
		$template_name = self::UNIT_TEST_IDENTIFIER;

		$query = "SELECT `name` FROM `site_section` WHERE `id` = {$content_type_id}";
		$data = $this->conn->fetchRecords($query);
		$section_name = $data[0]->name;

		$this->obj->contentTypeID->setInputValue($content_type_id);
		$this->obj->name->setInputValue($template_name);
		$this->obj->templateFile->setInputValue("template.php");
		$this->obj->location->setInputValue('local');
		$this->obj->save();

		$o2 = new ContentTemplate(null, $content_type_id, $template_name, "", "another-template.php", "local");
		try {
			$o2->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertContains("Error validating content templates.", $ex->getMessage());
			$this->assertContains("A \"{$template_name}\" template already exists for the \"{$section_name}\" area of the site.", $o2->validationErrors);
		}
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testRead()
	{
		$query = "SELECT `id` FROM `".ContentTemplate::TABLE_NAME()."` WHERE `name` = '".ContentTemplateTest::UNIT_TEST_IDENTIFIER." for reading'";
		$data = $this->conn->fetchRecords($query);

		$this->obj->id->setInputValue($data[0]->id);
		$this->obj->read();

		$this->assertEquals(CONTENT_TEMPLATE_CONTENT_TYPE_ID, $this->obj->contentTypeID->value);
		$this->assertEquals(ContentTemplateTest::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals('/path/to/templates/template.php', $this->obj->templateFile->value);
		$this->assertEquals('local', $this->obj->location->value);
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testSave()
	{
		/* set object property values */
		$this->obj->contentTypeID->setInputValue(45);
		$this->obj->name->setInputValue("Unit Test");
		$this->obj->templatePath->setInputValue("unit_tests/");
		$this->obj->templateFile->setInputValue("test-template.php");
		$this->obj->location->setInputValue("shared");
		$this->obj->templateFile->setInputValue($this->obj->formatFullPath());

		/* save new record */
		$this->obj->save();

		/* fetch the record data */
		$query = "SELECT * FROM `".$this->obj::TABLE_NAME()."` WHERE `id` = {$this->obj->id->value}";
		$data = $this->conn->fetchRecords($query);

		/* compare fetched record data to object property values */
		$this->assertEquals($this->obj->id->value, $data[0]->id);
		$this->assertEquals($this->obj->contentTypeID->value, $data[0]->site_section_id);
		$this->assertEquals($this->obj->name->value, $data[0]->name);
		$this->assertEquals($this->obj->templateFile->value, $data[0]->path);
		$this->assertEquals($this->obj->location->value, $data[0]->location);
	}
}