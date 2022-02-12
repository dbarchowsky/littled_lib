<?php
namespace Littled\Tests\PageContent\SiteSection;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\App\LittledGlobals;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\ContentTemplate;
use PHPUnit\Framework\TestCase;
use Exception;

class ContentTemplateTest extends TestCase
{
    /** @var string */
	const UNIT_TEST_IDENTIFIER = 'unit test';
    /** @var int */
    const TEST_CONTENT_TYPE_ID = 33;    /** "Content Template" record in site_section table */

	/** @var ContentTemplate Test ContentTemplate object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;
	/** @var int ID of test content template record for reading. */
	public $test_record_id;

	/**
     * @throws NotImplementedException|Exception
     */
	public static function setUpBeforeClass(): void
	{
        if (!defined('COMMON_TEMPLATE_DIR')) {
            define('COMMON_TEMPLATE_DIR', '/var/www/html/vendor/dbarchowsky/littled/templates/');
        }
        if (!defined('CMS_COMMON_TEMPLATE_DIR')) {
            define('CMS_COMMON_TEMPLATE_DIR', '/var/www/html/vendor/dbarchowsky/littled_cms/templates/');
        }

        $type_id = self::TEST_CONTENT_TYPE_ID;
        $name = ContentTemplateTest::UNIT_TEST_IDENTIFIER.' for reading';
        $path = '/path/to/templates/template.php';
        $location = 'local';
		$query = 'INS'.'ERT INTO `'.ContentTemplate::getTableName().'`'.
			' (`site_section_id`, `name`, `path`, `location`) VALUES (?,?,?,?)';
		$conn = new MySQLConnection();
		$conn->query($query, 'isss', $type_id, $name, $path, $location);
        LittledGlobals::setLocalTemplatesPath(COMMON_TEMPLATE_DIR);
        LittledGlobals::setSharedTemplatesPath(CMS_COMMON_TEMPLATE_DIR);
	}

    /**
     * @throws Exception
     */
    public static function tearDownAfterClass(): void
	{
        $pattern = ContentTemplateTest::UNIT_TEST_IDENTIFIER.'%';
		$query = "DEL"."ETE FROM `".ContentTemplate::getTableName()."` WHERE LOWER(`name`) LIKE ?";
		$conn = new MySQLConnection();
		$conn->query($query, 's', $pattern);
        LittledGlobals::setLocalTemplatesPath('');
        LittledGlobals::setSharedTemplatesPath('');
	}

	/**
	 * Set up object before each test.
	 */
	public function setUp(): void
	{
		parent::setUp();
		$this->obj = new ContentTemplate();
		$this->conn = new MySQLConnection();
	}

    /**
     * @throws NotImplementedException
     */
	public function testTableName()
	{
		$this->assertEquals('content_template', $this->obj::getTableName());
	}

	public function testConstruct()
	{
		$obj = new ContentTemplate(5, 10, 'Test Section', '/path/to/templates/', 'template-file.php', 'top');
		$this->assertEquals(5, $obj->id->value);
		$this->assertEquals(10, $obj->parentID->value);
		$this->assertEquals('Test Section', $obj->name->value);
		$this->assertEquals('/path/to/templates/', $obj->template_dir->value);
		$this->assertEquals('template-file.php', $obj->path->value);
		$this->assertEquals('top', $obj->location->value);
	}

	public function testConstructDefaultValues()
	{
		$obj = new ContentTemplate();
		$this->assertNull($obj->id->value);
		$this->assertNull($obj->parentID->value);
		$this->assertEquals('', $obj->name->value);
		$this->assertEquals('', $obj->template_dir->value);
		$this->assertEquals('', $obj->path->value);
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
		$this->obj->path->setInputValue('my-template.php');
		$this->assertTrue($this->obj->hasData());

		$this->obj->path->setInputValue('');
		$this->assertFalse($this->obj->hasData());

		$this->obj->template_dir->setInputValue('/path/to/templates/');
		$this->obj->content_id->setInputValue(72);
		$this->obj->location->setInputValue('bottom');
		$this->assertFalse($this->obj->hasData());

		$this->obj->name->setInputValue('test name');
		$this->obj->path->setInputValue('template.php');
		$this->assertTrue($this->obj->hasData());
	}

    /**
     * @return void
     * @throws Exception
     */
	public function testGetFullPath()
	{
		/* default path value */
		$this->assertEquals('', $this->obj->formatFullPath());

		/* just path */
        $this->obj->path->setInputValue('template.php');
        if (!defined('APP_BASE_DIR')) {
            $this->assertEquals($this->obj->path->value, $this->obj->formatFullPath());
            define('APP_BASE_DIR', '/var/www/html/');
        }
        $this->assertEquals(APP_BASE_DIR.$this->obj->path->value, $this->obj->formatFullPath());

		/* path and template directory with no trailing slash */
		$this->obj->template_dir->setInputValue('/templates/html');
		$this->assertEquals(APP_BASE_DIR.'templates/html/template.php', $this->obj->formatFullPath());

		/* path and template directory with trailing slash */
		$this->obj->template_dir->value = "{$this->obj->template_dir->value}/";
		$this->assertEquals(APP_BASE_DIR.'templates/html/template.php', $this->obj->formatFullPath());

		/* location value set to SHARED */
		$this->obj->location->setInputValue('shared');
		$this->assertEquals(COMMON_TEMPLATE_DIR.'template.php', $this->obj->formatFullPath());

		/* location value set to SHARED CMS */
		$this->obj->location->setInputValue('shared-cms');
		$this->assertEquals(CMS_COMMON_TEMPLATE_DIR.'template.php', $this->obj->formatFullPath());
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
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

		$this->obj->content_id->setInputValue(2);
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertNotContains("Content type is required.", $this->obj->validationErrors);
		}

		$this->obj->path->setInputValue('template.php');
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
			$this->assertNotContains("Template file is required.", $this->obj->validationErrors);
		}

		$this->obj->template_dir->setInputValue('templates/html/');
		try {
			$this->obj->validateInput();
		}
		catch (ContentValidationException $ex) {
        }
		$this->assertEmpty($this->obj->validationErrors);
	}

    /**
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws NotImplementedException
     * @throws Exception
     */
	public function testValidateInputDuplicateContentType()
	{
        $section_name = 'Content Template';

        // Retrieve test content template record
		$query = "SEL"."ECT `name`".
            " FROM `".$this->obj->getTableName()."`".
            " WHERE site_section_id = ? AND `name` LIKE ?";
        $name_filter = self::UNIT_TEST_IDENTIFIER.'%';
        $content_type_id = self::TEST_CONTENT_TYPE_ID;
		$data = $this->conn->fetchRecords($query, 'is', $content_type_id, $name_filter);
        if (count($data) < 1) {
            throw new Exception('Could not retrieve test record.');
        }
		$template_name = $data[0]->name;

        // test the duplicate validator method directly
		$o2 = new ContentTemplate(null, $content_type_id, $template_name, "", "another-template.php", "local");
        $this->assertEquals($section_name, $o2->testForDuplicateTemplate());

        // test request input validation which calls the duplicate validator method
        try {
            $o2->validateInput();
        }
        catch(Exception $ex) {
            $this->assertMatchesRegularExpression('/Error validating content templates./', $ex->getMessage());
            $this->assertContains("A \"$template_name\" template already exists for the \"$section_name\" area of the site.", $o2->validationErrors);
        }

        // test the duplicate validator with a unique template name
        $o2->name->setInputValue('Very unique template name');
        $this->assertEquals('', $o2->testForDuplicateTemplate());
	}

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     * @throws Exception
     */
	public function testRead()
	{
        $name = ContentTemplateTest::UNIT_TEST_IDENTIFIER.' for reading';
		$query = "SEL"."ECT `id` FROM `".ContentTemplate::getTableName()."` WHERE `name` = ?";
		$data = $this->conn->fetchRecords($query, 's', $name);

		$this->obj->id->setInputValue($data[0]->id);
		$this->obj->read();

		$this->assertEquals(self::TEST_CONTENT_TYPE_ID, $this->obj->content_id->value);
		$this->assertEquals(ContentTemplateTest::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals('/path/to/templates/template.php', $this->obj->path->value);
		$this->assertEquals('local', $this->obj->location->value);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
     * @throws Exception
	 */
	public function testSave()
	{
		/* set object property values */
		$this->obj->content_id->setInputValue(45);
		$this->obj->name->setInputValue("Unit Test");
		$this->obj->template_dir->setInputValue("unit_tests/");
		$this->obj->path->setInputValue("test-template.php");
		$this->obj->location->setInputValue("shared");
		$this->obj->path->setInputValue($this->obj->formatFullPath());

		/* save new record */
		$this->obj->save();

		/* fetch the record data */
		$query = "SEL"."ECT * FROM `".$this->obj::getTableName()."` WHERE `id` = ?";
		$data = $this->conn->fetchRecords($query, 's', $this->obj->id->value);

		/* compare fetched record data to object property values */
		$this->assertEquals($this->obj->id->value, $data[0]->id);
		$this->assertEquals($this->obj->content_id->value, $data[0]->site_section_id);
		$this->assertEquals($this->obj->name->value, $data[0]->name);
		$this->assertEquals($this->obj->path->value, $data[0]->path);
		$this->assertEquals($this->obj->location->value, $data[0]->location);
	}
}