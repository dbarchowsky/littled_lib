<?php
namespace Littled\Tests\PageContent\SiteSection;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\PageContent\SiteSection\ContentProperties;
use PHPUnit\Framework\TestCase;

class SectionContentTest extends TestCase
{
	/** @var SectionContent Test SectionContent object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;
    /** @var int */
    protected const CONTENT_TEMPLATE_CONTENT_TYPE_ID = 33; /* "Content Template" record in site_section table */

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new SectionContent();
        $this->conn = new MySQLConnection();
    }

    public function testConstructorDefaultValues()
	{
		$this->assertNull($this->obj->id->value);
		$this->assertNull($this->obj->content_properties->id->value);
	}

	public function testConstructorWithIDs()
	{
		$obj = new SectionContent(45, 88);
		$this->assertEquals(45, $obj->id->value);
		$this->assertEquals(88, $obj->content_properties->id->value);
	}

	public function testCollectFromInput()
	{
		$src = array(
			'id' => 82,
			ContentProperties::ID_KEY => 33,
			'ssna' => 'Unit Test Request Input',
			'ssrd'=> 'path/to/root/',
			'ssdr' => 'path/to/images/',
			'ssiw' => 2148,
			'ssih' => 1784,
			'ssmn' => '1',
			'ssgt' => 'false'
			);
		$this->obj->collectRequestData($src);

		$this->assertEquals(82, $this->obj->id->value);
		/* Site Section data should not be collected & should remain with default values */
		$this->assertNull($this->obj->content_properties->id->value);
		$this->assertEquals('', $this->obj->content_properties->name->value);
		$this->assertNull($this->obj->content_properties->width->value);
		$this->assertFalse($this->obj->content_properties->save_mini->value);
		$this->assertFalse($this->obj->content_properties->is_cached->value);
	}

	public function testGetContentTypeIDUsingInternalValue()
	{
		$this->obj->content_properties->id->setInputValue(self::CONTENT_TEMPLATE_CONTENT_TYPE_ID);
        // this value comes from object static property
		$this->assertNull($this->obj::getContentId());
        // this value comes from database record
        $this->assertEquals(self::CONTENT_TEMPLATE_CONTENT_TYPE_ID, $this->obj->getContentTypeId());
	}

	public function testGetContentTypeIDUsingDatabaseValue()
	{
		/* returns null because SECTION_ID constant is not defined for SectionContent, only for inherited classes. */
		$this->assertNull($this->obj->getContentId());
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	public function testRetrieveSectionPropertiesUsingDefaultValue()
	{
		try {
			$this->obj->retrieveSectionProperties();
		}
		catch(ContentValidationException $ex) {
			$this->assertStringContainsString("Cannot retrieve section properties.", $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	public function testRetrieveContentProperties()
	{
		$this->obj->content_properties->id->setInputValue(self::CONTENT_TEMPLATE_CONTENT_TYPE_ID);
		$this->obj->retrieveSectionProperties();
		$this->assertEquals("Content Template", $this->obj->content_properties->name->value);
		$this->assertEquals("/sections/", $this->obj->content_properties->root_dir->value);
		$this->assertFalse($this->obj->content_properties->save_mini->value);
		$this->assertEquals("content_template", $this->obj->content_properties->table->value);
		$this->assertEquals(27, $this->obj->content_properties->parent_id->value);
	}
}