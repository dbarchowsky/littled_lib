<?php
namespace Littled\Tests\PageContent\SiteSection;


use Littled\Database\MySQLConnection;
use Littled\Exception\ContentValidationException;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\PageContent\SiteSection\SiteSection;
use PHPUnit\Framework\TestCase;

class SiteSectionTest extends TestCase
{
	const UNIT_TEST_IDENTIFIER = "Unit Test";
	const TEST_ID_FOR_DELETE = 6000;
	const TEST_ID_FOR_READ = 6001;

	/** @var SiteSection Test SiteSection object. */
	public $obj;
	/** @var MySQLConnection database connection */
	public $conn;

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function setUpBeforeClass()
	{
		$conn = new MySQLConnection();
		$query = "INSERT INTO `site_section` (".
			"`id`".
			",`name`".
			",`slug`".
			",`root_dir`".
			",`image_path`".
			",`sub_dir`".
			",`image_label`".
			",`width`".
			",`height`".
			",`med_width`".
			",`med_height`".
			",`save_mini`".
			",`mini_width`".
			",`mini_height`".
			",`format`".
			",`param_prefix`".
			",`table`".
			",`parent_id`".
			",`is_cached`".
			",`gallery_thumbnail`".
			") VALUES (".
			SiteSectionTest::TEST_ID_FOR_DELETE.
			",'".SiteSectionTest::UNIT_TEST_IDENTIFIER." for delete'".
			",'unit_test_slug'".
			",'path/to/section/'".
			",''".
			",''".
			",'pic'".
			",2048".
			",1880".
			",null".
			",null".
			",false".
			",null".
			",null".
			",'png'".
			",''".
			",'unit_test'".
			",null".
			",0".
			",0)";
		$conn->query($query);

		$query = "INSERT INTO `site_section` (".
			"`id`".
			",`name`".
			",`slug`".
			",`root_dir`".
			",`image_path`".
			",`sub_dir`".
			",`image_label`".
			",`width`".
			",`height`".
			",`med_width`".
			",`med_height`".
			",`save_mini`".
			",`mini_width`".
			",`mini_height`".
			",`format`".
			",`param_prefix`".
			",`table`".
			",`parent_id`".
			",`is_cached`".
			",`gallery_thumbnail`".
			") VALUES (".
			SiteSectionTest::TEST_ID_FOR_READ.
			",'".SiteSectionTest::UNIT_TEST_IDENTIFIER." for reading'".
			",'unit_test_slug'".
			",'path/to/section/'".
			",'path/to/images/'".
			",'sub/dir/'".
			",'pic'".
			",2048".
			",1880".
			",1024".
			",980".
			",true".
			",540".
			",460".
			",'png'".
			",'ut_'".
			",'unit_test'".
			",".SiteSectionTest::TEST_ID_FOR_DELETE.
			",0".
			",0)";
		$conn->query($query);
	}

	/**
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public static function tearDownAfterClass()
	{
		$conn = new MySQLConnection();
		$query = "DELETE FROM `site_section` WHERE `name` LIKE '".SiteSectionTest::UNIT_TEST_IDENTIFIER."%'";
		$conn->query($query);
		$query = "DELETE FROM `content_template` WHERE `name` LIKE '".SiteSectionTest::UNIT_TEST_IDENTIFIER."%'";
		$conn->query($query);
	}

	public function setUp()
	{
		$this->conn = new MySQLConnection();
		$this->obj = new SiteSection();
	}

	/**
	 * @param int $site_section_id ID of parent site section record.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	protected function addContentTemplates($site_section_id)
	{
		$t = new ContentTemplate(null, self::TEST_ID_FOR_READ, self::UNIT_TEST_IDENTIFIER." details", "", "template.php", "local");
		$t->save();
		$t->id->setInputValue(null);
		$t->name->setInputValue(self::UNIT_TEST_IDENTIFIER." listings");
		$t->save();
		$t->id->setInputValue(null);
		$t->name->setInputValue(self::UNIT_TEST_IDENTIFIER." edit");
		$t->save();
	}

	/**
	 * @param int $site_section_id Site section to link content operations to.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function addSectionOperations($site_section_id)
	{
		$query = "INSERT INTO section_operations (".
			"`section_id`".
			",`label`".
			",`id_param`".
			") VALUES (".
			$site_section_id.
			", '".self::UNIT_TEST_IDENTIFIER." content properties'".
			", 'putid')";
		$this->conn->query($query);
	}

	/**
	 * @param $site_section_id
	 * @return array
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	protected function fetchExtraProperties($site_section_id)
	{
		$query = "CALL siteSectionExtraPropertiesSelect({$site_section_id})";
		$data = $this->conn->fetchRecords($query);
		if (count($data) > 0) {
			return (array($data[0]->id_param, $data[0]->parent, $data[0]->label));
		}
		return (array('', '', ''));
	}

	/**
	 * @param SiteSection $site_section Object containing templates.
	 * @throws ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	protected function removeContentTemplates($site_section)
	{
		/** @var ContentTemplate $template */
		foreach($site_section->templates as $template) {
			$template->delete();
		}
	}

	/**
	 * @param int $site_section_id Id of site section to link content operations to.
	 * @throws \Littled\Exception\InvalidQueryException
	 */
	public function removeSectionOperations($site_section_id)
	{
		$query = "DELETE FROM `section_operations` WHERE `section_id` = {$site_section_id}";
		$this->conn->query($query);
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
	public function testClearValues()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->read();
		$this->obj->clearValues();
		$this->assertEquals(0, count($this->obj->templates));
		$this->assertEquals('', $this->obj->id_param);
		$this->assertEquals('', $this->obj->parent);
		$this->assertEquals('', $this->obj->label);
	}

	public function testDefaultValues()
	{
		$this->assertEquals(SiteSection::ID_PARAM, $this->obj->id->key);
		$this->assertEquals('', $this->obj->name->value);
		$this->assertNull($this->obj->width->value);
		$this->assertEquals('', $this->obj->format->value);
		$this->assertNull($this->obj->parent_id->value);
		$this->assertTrue(is_array($this->obj->templates));
		$this->assertFalse($this->obj->is_cached->value);
		$this->assertFalse($this->obj->save_mini->value);
		$this->assertFalse($this->obj->gallery_thumbnail->value);
	}

	public function testInitialize()
	{
		$this->obj->name->setInputValue(self::UNIT_TEST_IDENTIFIER);
		$this->obj->root_dir->setInputValue("path/to/section/");
		$this->obj->image_path->setInputValue("path/to/images/");
		$this->obj->sub_dir->setInputValue("sub/dir/");
		$this->obj->image_label->setInputValue("pic");
		$this->obj->width->setInputValue(2048);
		$this->obj->height->setInputValue(1600);
		$this->obj->med_width->setInputValue(1024);
		$this->obj->med_height->setInputValue(960);
		$this->obj->save_mini->setInputValue(true);
		$this->obj->mini_width->setInputValue(540);
		$this->obj->mini_height->setInputValue(480);
		$this->obj->format->setInputValue('png');
		$this->obj->param_prefix->setInputValue('ut_');
		$this->obj->table->setInputValue('unit_test');
		$this->obj->parent_id->setInputValue(142);
		$this->obj->is_cached->setInputValue(false);
		$this->obj->gallery_thumbnail->setInputValue(true);

		$this->assertNull($this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER, $this->obj->name->value);
		$this->assertEquals('path/to/section/', $this->obj->root_dir->value);
		$this->assertEquals('path/to/images/', $this->obj->image_path->value);
		$this->assertEquals(255, $this->obj->image_path->sizeLimit);
		$this->assertEquals('sub/dir/', $this->obj->sub_dir->value);
		$this->assertEquals('pic', $this->obj->image_label->value);
		$this->assertEquals(2048, $this->obj->width->value);
		$this->assertEquals(1600, $this->obj->height->value);
		$this->assertEquals(1024, $this->obj->med_width->value);
		$this->assertEquals(960, $this->obj->med_height->value);
		$this->assertTrue($this->obj->save_mini->value);
		$this->assertEquals(540, $this->obj->mini_width->value);
		$this->assertEquals(480, $this->obj->mini_height->value);
		$this->assertEquals(480, $this->obj->mini_height->value);
		$this->assertEquals('png', $this->obj->format->value);
		$this->assertEquals('ut_', $this->obj->param_prefix->value);
		$this->assertEquals('unit_test', $this->obj->table->value);
		$this->assertEquals(142, $this->obj->parent_id->value);
		$this->assertFalse($this->obj->is_cached->value);
		$this->assertTrue($this->obj->gallery_thumbnail->value);
	}

	/**
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 */
	public function testDelete()
	{
		$this->obj->id->setInputValue(SiteSectionTest::TEST_ID_FOR_DELETE);

		$query = "SELECT `id` FROM `".SiteSection::TABLE_NAME()."` WHERE `parent_id` = ".SiteSectionTest::TEST_ID_FOR_DELETE;
		$data = $this->conn->fetchRecords($query);
		$child_id = $data[0]->id;
		$this->assertNotNull($child_id);

		$this->obj->delete();

		$this->assertGreaterThan(0, $this->obj->id->value);

		$query = "SELECT COUNT(1) AS `count` FROM `".SiteSection::TABLE_NAME()."` WHERE `id` = {$this->obj->id->value}";
		$data = $this->conn->fetchRecords($query);
		$this->assertEquals(0, $data[0]->count);

		$query = "SELECT `parent_id` FROM `".SiteSection::TABLE_NAME()."` WHERE `id` = {$child_id}";
		$data = $this->conn->fetchRecords($query);
		$this->assertNull($data[0]->parent_id);
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
	public function testGetParentID()
	{
		$p = new SiteSection();
		$p->name->setInputValue(self::UNIT_TEST_IDENTIFIER." parent record");
		$p->save();

		$c = new SiteSection(self::TEST_ID_FOR_READ);
		$c->read();
		$c->parent_id->setInputValue($p->id->value);
		$c->save();

		$parent_id = $c->getParentID();
		$this->assertEquals($p->id->value, $parent_id);

		$p->delete();
	}

	public function testHasData()
	{
		$obj = new SiteSection();
		$this->assertFalse($obj->hasData());

		$obj->id->setInputValue(839);
		$this->assertTrue($obj->hasData());

		$obj->id->setInputValue(null);
		$obj->name->setInputValue(self::UNIT_TEST_IDENTIFIER);
		$this->assertTrue($obj->hasData());

		$obj->name->setInputValue('');
		$this->assertFalse($obj->hasData());

		$obj->name->setInputValue(null);
		$this->assertFalse($obj->hasData());

		$obj->id->setInputValue(8267);
		$obj->name->setInputValue('foo bar biz');
		$this->assertTrue($obj->hasData());
	}

	public function testPluralLabel()
	{
		$this->assertEquals('', $this->obj->pluralLabel(0));
		$this->assertEquals('', $this->obj->pluralLabel(1));
		$this->assertEquals('', $this->obj->pluralLabel(2));

		$this->obj->name->setInputValue('thing');
		$this->assertEquals('things', $this->obj->pluralLabel(0));
		$this->assertEquals('thing', $this->obj->pluralLabel(1));
		$this->assertEquals('things', $this->obj->pluralLabel(2));

		$this->obj->name->setInputValue('thingy');
		$this->assertEquals('thingies', $this->obj->pluralLabel(0));
		$this->assertEquals('thingy', $this->obj->pluralLabel(1));
		$this->assertEquals('thingies', $this->obj->pluralLabel(2));
	}

	/**
	 * @throws \Littled\Exception\ConfigurationUndefinedException
	 * @throws \Littled\Exception\ConnectionException
	 * @throws \Littled\Exception\ContentValidationException
	 * @throws \Littled\Exception\InvalidQueryException
	 * @throws \Littled\Exception\NotImplementedException
	 * @throws \Littled\Exception\RecordNotFoundException
	 */
	public function testReadTemplates()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->readTemplates();
		$this->assertEquals(0, count($this->obj->templates));

		$this->addContentTemplates(self::TEST_ID_FOR_READ);

		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->readTemplates();
		$this->assertEquals(3, count($this->obj->templates));
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." edit", $this->obj->templates[2]->name->value);

		$this->removeContentTemplates($this->obj);
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
	public function testReadWithoutTemplatesOrExtraProperties()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);

		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);

		/* without templates or extra properties */
		$this->obj->read();

		$this->assertEquals(self::TEST_ID_FOR_READ, $this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals($id_param, $this->obj->id_param);
		$this->assertEquals($parent_name, $this->obj->parent);
		$this->assertEquals($label, $this->obj->label);
		$this->assertEquals(0, count($this->obj->templates));
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
	public function testReadWithTemplates()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);
		$this->addContentTemplates($this->obj->id->value);
		$this->obj->read();

		$this->assertEquals(self::TEST_ID_FOR_READ, $this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals($id_param, $this->obj->id_param);
		$this->assertEquals($parent_name, $this->obj->parent);
		$this->assertEquals($label, $this->obj->label);
		$this->assertEquals(3, count($this->obj->templates));
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." edit", $this->obj->templates[2]->name->value);

		$this->removeContentTemplates($this->obj);
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
	public function testReadWithExtraProperties()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);

		$this->addSectionOperations(self::TEST_ID_FOR_READ);

		$this->obj->read();

		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);

		$this->assertEquals(self::TEST_ID_FOR_READ, $this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals($id_param, $this->obj->id_param);
		$this->assertEquals($parent_name, $this->obj->parent);
		$this->assertEquals($label, $this->obj->label);
		$this->assertEquals(0, count($this->obj->templates));

		/* cleanup */
		$this->removeSectionOperations(self::TEST_ID_FOR_READ);
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
	public function testReadWithTemplatesAndExtraProperties()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);

		/* link templates and section operations */
		$this->addContentTemplates(self::TEST_ID_FOR_READ);
		$this->addSectionOperations(self::TEST_ID_FOR_READ);

		$this->obj->read();

		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);

		$this->assertEquals(self::TEST_ID_FOR_READ, $this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals($id_param, $this->obj->id_param);
		$this->assertEquals($parent_name, $this->obj->parent);
		$this->assertEquals($label, $this->obj->label);
		$this->assertEquals(3, count($this->obj->templates));
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." edit", $this->obj->templates[2]->name->value);

		/* cleanup */
		$this->removeContentTemplates($this->obj);
		$this->removeSectionOperations(self::TEST_ID_FOR_READ);
	}
}