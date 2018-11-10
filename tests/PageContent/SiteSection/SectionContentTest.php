<?php
namespace Littled\Tests\PageContent\SiteSection;


use DeepCopy\Filter\SetNullFilter;
use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\SiteSection\SectionContent;
use Littled\PageContent\SiteSection\ContentProperties;
use PHPUnit\Framework\TestCase;

class SectionContentTest extends TestCase
{
	/** @var SectionContent Test SectionContent object. */
	public $obj;
	/** @var MySQLConnection Test database connection. */
	public $conn;

	public function setUp()
	{
		parent::setUp();
		$this->obj = new SectionContent();
		$this->conn = new MySQLConnection();
	}

	public function testConstructorDefaultValues()
	{
		$this->assertNull($this->obj->id->value);
		$this->assertNull($this->obj->contentProperties->id->value);
	}

	public function testConstructorWithIDs()
	{
		$obj = new SectionContent(45, 88);
		$this->assertEquals(45, $obj->id->value);
		$this->assertEquals(88, $obj->contentProperties->id->value);
	}

	public function testCollectFromInput()
	{
		$src = array(
			'id' => 82,
			ContentProperties::ID_PARAM => 33,
			'ssna' => 'Unit Test Request Input',
			'ssrd'=> 'path/to/root/',
			'ssdr' => 'path/to/images/',
			'ssiw' => 2148,
			'ssih' => 1784,
			'ssmn' => '1',
			'ssgt' => 'false'
			);
		$this->obj->collectFromInput($src);

		$this->assertEquals(82, $this->obj->id->value);
		/* Site Section data should not be collected & should remain with default values */
		$this->assertNull($this->obj->contentProperties->id->value);
		$this->assertEquals('', $this->obj->contentProperties->name->value);
		$this->assertNull($this->obj->contentProperties->width->value);
		$this->assertFalse($this->obj->contentProperties->save_mini->value);
		$this->assertFalse($this->obj->contentProperties->is_cached->value);
	}

	public function testGetContentTypeIDUsingInternalValue()
	{
		$this->obj->contentProperties->id->setInputValue(CONTENT_TEMPLATE_CONTENT_TYPE_ID);
		$this->assertEquals(CONTENT_TEMPLATE_CONTENT_TYPE_ID, $this->obj->getContentTypeID());
	}

	public function testGetContentTypeIDUsingDatabaseValue()
	{
		/* returns null because SECTION_ID constant is not defined for SectionContent, only for inherited classes. */
		$this->assertNull($this->obj->getContentTypeID());
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testRetrieveSectionPropertiesUsingDefaultValue()
	{
		try {
			$this->obj->retrieveSectionProperties();
		}
		catch(ContentValidationException $ex) {
			$this->assertContains("Cannot retrieve section properties.", $ex->getMessage());
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\InvalidTypeException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testRetrieveSectionProperties()
	{
		$this->obj->contentProperties->id->setInputValue(CONTENT_TEMPLATE_CONTENT_TYPE_ID);
		$this->obj->retrieveSectionProperties();
		$this->assertEquals("Content Template", $this->obj->contentProperties->name->value);
		$this->assertEquals("/hostmgr/content-properties", $this->obj->contentProperties->root_dir->value);
		$this->assertFalse($this->obj->contentProperties->save_mini->value);
		$this->assertEquals("content_template", $this->obj->contentProperties->table->value);
		$this->assertEquals(27, $this->obj->contentProperties->parent_id->value);
	}
}