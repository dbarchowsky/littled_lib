<?php
namespace LittledTests\PageContent\SiteSection;

use Exception;
use Littled\Database\DBUtils;
use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\SiteSection\ContentProperties;
use Littled\PageContent\SiteSection\SectionContent;
use LittledTests\TestHarness\PageContent\SiteSection\SectionContentTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\SectionContentWithoutTypeTestHarness;
use LittledTests\TestHarness\PageContent\SiteSection\TestTableSectionContentTestHarness;
use PHPUnit\Framework\TestCase;

class SectionContentTest extends TestCase
{
    public const TEST_RECORD_ID = 2207; /* from table `test_table` */
	/** @var SectionContentTestHarness Test SectionContent object. */
	public SectionContentTestHarness $obj;
	/** @var MySQLConnection Test database connection. */
	public MySQLConnection $conn;
    /** @var int */
    protected const CONTENT_TEMPLATE_CONTENT_TYPE_ID = 33; /* "Content Template" record in site_section table */

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new SectionContentTestHarness();
        $this->conn = new MySQLConnection();
    }

	protected function tearDown(): void
	{
		parent::tearDown();
		$this->conn->closeDatabaseConnection();
	}

	public function testConstructorDefaultValues()
	{
		$this->assertNull($this->obj->id->value);
		$this->assertNotNull($this->obj->content_properties->id->value);
	}

	public function testConstructorWithUndefinedContentType()
	{
		try {
			new SectionContentWithoutTypeTestHarness();
			$this->assertEquals(false, true, 'Expected ConfigurationUndefined exception not thrown.');
		}
		catch(Exception $e) {
			$this->assertInstanceOf(ConfigurationUndefinedException::class, $e);
		}
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	public function testConstructorWithIDs()
	{
		$obj = new SectionContentTestHarness(45, 88);
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
		$this->assertNotNull($this->obj->content_properties->id->value);
		$this->assertEquals('', $this->obj->content_properties->name->value);
		$this->assertFalse($this->obj->content_properties->is_cached->value);
	}

    /**
     * @throws NotImplementedException
     * @throws ConnectionException
     * @throws ConfigurationUndefinedException
     * @throws ContentValidationException
     * @throws RecordNotFoundException
     * @throws Exception
     */
    function testDelete()
    {
        $o = new TestTableSectionContentTestHarness();
        $start_count = static::getRecordCount($o);

        $next_id = DBUtils::lookupNextAvailableRecordId($o::getTableName());
        $o->id->setInputValue($next_id);
        $o->name->setInputValue('test record');
        $o->save();

        $add_count = static::getRecordCount($o);
        $this->assertEquals($start_count+1, $add_count);

        $status = $o->delete();
        $this->assertMatchesRegularExpression('/successfully deleted '.$o->getContentLabel().'/i', $status);

        $deleted_count = static::getRecordCount($o);
        $this->assertEquals($start_count, $deleted_count);
    }

	function testGetContentLabel()
	{
        $o = new SectionContentTestHarness();
		$this->assertEquals('', $o->getContentLabel());

		$o->content_properties->name->value = 'my assigned value';
        $o->content_properties->label->value = '';
		$this->assertEquals($o->content_properties->name->value, $o->getContentLabel());
		$this->assertEquals($o->content_properties->label->value, $o->getLabel());

		$o->content_properties->label->value = 'my assigned label';
		$this->assertEquals($o->content_properties->label->value, $o->getContentLabel());
		$this->assertEquals($o->content_properties->label->value, $o->getLabel());
	}

	public function testGetContentTypeIdUsingInternalValue()
	{
		$this->obj->content_properties->id->setInputValue(self::CONTENT_TEMPLATE_CONTENT_TYPE_ID);
        // this value comes from object static property
		try {
			$this->obj::getContentTypeId();
		}
		catch(ConfigurationUndefinedException $ex) {
			$this->assertMatchesRegularExpression('/content type not set/i', $ex->getMessage());
		}
        // this value comes from database record
        $this->assertEquals(self::CONTENT_TEMPLATE_CONTENT_TYPE_ID, $this->obj->getContentPropertyId());
	}

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws NotImplementedException
	 */
	public function testRetrieveContentProperties()
	{
		$this->obj->content_properties->id->setInputValue(self::CONTENT_TEMPLATE_CONTENT_TYPE_ID);
		$this->obj->retrieveSectionProperties();
		$this->assertEquals("Content Template", $this->obj->content_properties->name->value);
		$this->assertEquals("/sections/", $this->obj->content_properties->root_dir->value);
		$this->assertEquals("content_template", $this->obj->content_properties->table->value);
		$this->assertEquals(27, $this->obj->content_properties->parent_id->value);
	}

    /**
     * @throws NotImplementedException
     */
    static function getRecordCount(SectionContent $o): int
    {
        $data = $o->fetchRecords('SELECT COUNT(1) AS `count` FROM `'.$o::getTableName().'`');
        return $data[0]->count;
    }
}