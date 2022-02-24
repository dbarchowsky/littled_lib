<?php
namespace Littled\Tests\PageContent\SiteSection;
require_once(realpath(dirname(__FILE__)) . "/../../bootstrap.php");

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidTypeException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use Littled\Tests\PageContent\SiteSection\DataProvider\ContentTemplateData;
use Littled\PageContent\SiteSection\ContentProperties;
use PHPUnit\Framework\TestCase;
use Exception;

class ContentPropertiesTest extends TestCase
{
	const UNIT_TEST_IDENTIFIER = "Unit Test";
	const TEST_CONTENT_LABEL_READING = self::UNIT_TEST_IDENTIFIER.' for reading';
	const TEST_ID_FOR_DELETE = 6000;
	const TEST_ID_FOR_READ = 6001;
	const TEST_CONTENT_TYPE_ID = 6037;
	const TEST_IMAGE_LABEL = 'pic';

	/** @var ContentProperties Test SiteSection object. */
	public ContentProperties $obj;
	/** @var MySQLConnection database connection */
	public MySQLConnection $conn;

    /**
     * @return void
     * @throws Exception
     */
    protected static function clearTestRecords()
    {
        $c = new MySQLConnection();
        $query = "DELETE FROM `site_section` WHERE id in (?,?)";
        $id1 = ContentPropertiesTest::TEST_ID_FOR_DELETE;
        $id2 = ContentPropertiesTest::TEST_ID_FOR_READ;
        $c->query($query, 'ii', $id1, $id2);
    }

    /**
     * @return void
     * @throws Exception
     */
    protected static function createTestRecords()
    {
        $conn = new MySQLConnection();
        $mysqli = $conn->getMysqli();
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
            ") VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $id = ContentPropertiesTest::TEST_ID_FOR_DELETE;
        $name = ContentPropertiesTest::UNIT_TEST_IDENTIFIER;
        $slug = 'unit_test_slug';
        $root_dir = 'path/to/section';
        $image_path = '';
        $sub_dir = '';
        $image_label = ContentPropertiesTest::TEST_IMAGE_LABEL;
        $width = 2048;
        $height = 1880;
        $med_width = null;
        $med_height = null;
        $save_mini = false;
        $mini_width = null;
        $mini_height = null;
        $format = 'png';
        $param_prefix = '';
        $table = 'unit_test';
        $parent_id = null;
        $is_cached = false;
        $gallery_thumbnail = false;

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('issssssiiiiiiisssiii',
            $id, $name, $slug, $root_dir, $image_path, $sub_dir, $image_label, $width, $height,
            $med_width, $med_height, $save_mini, $mini_width, $mini_height, $format, $param_prefix, $table,
            $parent_id, $is_cached, $gallery_thumbnail);
        if(!$stmt->execute()) {
            throw new Exception('Error executing query: '.$mysqli->error);
        }

        $id = ContentPropertiesTest::TEST_ID_FOR_READ;
        $name = ContentPropertiesTest::TEST_CONTENT_LABEL_READING;
        $image_path = 'path/to/images/';
        $sub_dir = 'sub/dir/';
        $med_width = 1024;
        $med_height = 980;
        $save_mini = true;
        $mini_width = 540;
        $mini_height = 460;
        $param_prefix = 'ut_';
        $parent_id = ContentPropertiesTest::TEST_ID_FOR_DELETE;
        if(!$stmt->execute()) {
            throw new Exception('Error executing query: '.$mysqli->error);
        }

        self::createTempContentTemplateRecords($conn);
    }

    /**
     * @param MySQLConnection $conn
     * @return void
     * @throws Exception
     */
    protected static function createTempContentTemplateRecords(MySQLConnection $conn)
    {
        $template_data = array(
            new ContentTemplateData(0, 'listings-'.self::UNIT_TEST_IDENTIFIER, '/path/to/listings-template.php'),
            new ContentTemplateData(0, 'details-'.self::UNIT_TEST_IDENTIFIER, '/path/to/details-template.php'),
            new ContentTemplateData(0, 'edit-'.self::UNIT_TEST_IDENTIFIER, '/path/to/edit-template.php'),
        );

        // retrieve max id
        $query = 'SELECT MAX(id) as `max_id` FROM content_template';
        $data = $conn->fetchRecords($query);
        if (count($data) < 1) {
            throw new Exception('Content template id not available.');
        }
        $new_id = $data[0]->max_id + 100;

        // prepare insert statement
        $query = "INSERT INTO `content_template` (id, site_section_id, name, path) VALUES (?,?,?,?)";
        $mysqli = $conn->getMysqli();
        $stmt = $mysqli->prepare($query);
        $content_type_id = self::TEST_ID_FOR_READ;
        $stmt->bind_param('iiss', $new_id, $content_type_id, $name, $path);

        // execute insert statements
        foreach($template_data as $td) {
            $name = $td->name;
            $path = $td->template;
            if (!$stmt->execute()) {
                throw new Exception('Error creating temp content template. '.$stmt->error);
            }
            $new_id++;
        }
    }

    /**
     * @param MySQLConnection $conn
     * @return void
     * @throws Exception
     */
    protected static function clearTempContentTemplateRecords(MySQLConnection $conn)
    {
        $query = "DELETE FROM `content_template` WHERE `name` like '%-".self::UNIT_TEST_IDENTIFIER."'";
        $conn->query($query);
    }

	/**
	 * @throws Exception
     */
	public static function setUpBeforeClass(): void
	{
        self::clearTestRecords();
        self::createTestRecords();
	}

	/**
	 * @throws Exception
     */
	public static function tearDownAfterClass(): void
	{
        $name_filter = ContentPropertiesTest::UNIT_TEST_IDENTIFIER."%";
		$conn = new MySQLConnection();

        $query = "DELETE FROM `site_section` WHERE `name` LIKE ?";
		$conn->query($query, 's', $name_filter);

		$query = "DEL"."ETE FROM `".ContentTemplate::getTableName()."` WHERE `name` LIKE ?";
		$conn->query($query, 's', $name_filter);

		$query = "DEL"."ETE FROM `".ContentRoute::getTableName()."` WHERE `operation` LIKE ?";
		$conn->query($query, 's', $name_filter);

        self::clearTempContentTemplateRecords($conn);
	}

	public function setUp(): void
	{
		parent::setUp();
		$this->conn = new MySQLConnection();
		$this->obj = new ContentProperties();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
		$this->conn->closeDatabaseConnection();
	}

	/**
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws NotImplementedException
	 * @throws ConfigurationUndefinedException
	 */
	protected function addContentRoutes(int $n)
	{
		for($i=0; $i<$n; $i++) {
			$name = self::UNIT_TEST_IDENTIFIER.sprintf(' %02d', $i);
			$route = new ContentRoute(null, self::TEST_CONTENT_TYPE_ID, $name, 'https://localhost');
			$route->save();
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	protected function addContentTemplates()
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
	 * @throws InvalidQueryException|Exception
     */
	protected function addSectionOperations(int $site_section_id)
	{
		$query = "INSERT INTO section_operations (".
			"`section_id`".
			",`label`".
			",`id_param`".
			") VALUES (?,?,?)";
        $label = self::UNIT_TEST_IDENTIFIER.' content properties';
        $id_key = 'putid';
		$this->conn->query($query, '', $site_section_id, $label, $id_key);
	}

	/**
	 * @param int $site_section_id
	 * @return array
	 * @throws InvalidQueryException|Exception
     */
	protected function fetchExtraProperties(int $site_section_id): array
	{
		$query = "CALL siteSectionExtraPropertiesSelect(?)";
		$data = $this->conn->fetchRecords($query, 'i', $site_section_id);
		if (count($data) > 0) {
			return (array($data[0]->id_param, $data[0]->parent, $data[0]->label));
		}
		return array('', '', '');
	}

	/**
	 * @param ?string $id_key
	 * @param ?string $parent_name
	 * @param string $label
	 * @param int $template_count
	 * @return void
	 */
	protected function postReadAssertions(?string $id_key, ?string $parent_name, string $label, int $template_count)
	{
		$this->assertEquals(self::TEST_ID_FOR_READ, $this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." for reading", $this->obj->name->value);
		$this->assertEquals($id_key, $this->obj->id_key);
		$this->assertEquals($parent_name, $this->obj->parent);
		$this->assertEquals($label, $this->obj->label);
		$this->assertCount($template_count, $this->obj->templates);
	}

	/**
	 * @throws NotImplementedException
	 * @throws Exception
	 */
	protected function removeContentRoutes(SerializedContent $o)
	{
		$section_id = self::TEST_CONTENT_TYPE_ID;
		$name = self::UNIT_TEST_IDENTIFIER.'%';
		$query = "DEL"."ETE FROM `".ContentRoute::getTableName()."` WHERE site_section_id = ? AND operation LIKE ?";
		$o->query($query, 'is', $section_id, $name);
	}

	/**
	 * @param ContentProperties $site_section Object containing templates.
	 * @throws ContentValidationException
     * @throws NotImplementedException
	 */
	protected function removeContentTemplates(ContentProperties $site_section)
	{
		foreach($site_section->templates as $template) {
			$template->delete();
		}
	}

	/**
	 * @param int $site_section_id Record id of site section to link content operations to.
	 * @throws InvalidQueryException|Exception
     */
	protected function removeSectionOperations(int $site_section_id)
	{
		$query = "DELETE FROM `section_operations` WHERE `section_id` = ?";
		$this->conn->query($query, 'i', $site_section_id);
	}

	/**
	 * @throws NotImplementedException
	 */
	function testTableName()
    {
        $this->assertEquals('site_section', ContentProperties::getTableName());
    }

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	public function testClearValues()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->read();
		$this->obj->clearValues();
		$this->assertCount(0, $this->obj->templates);
		$this->assertEquals('', $this->obj->id_key);
		$this->assertEquals('', $this->obj->parent);
		$this->assertEquals('', $this->obj->label);
	}

	public function testDefaultValues()
	{
		$this->assertEquals(ContentProperties::ID_KEY, $this->obj->id->key);
		$this->assertEquals('', $this->obj->name->value);
		$this->assertNull($this->obj->width->value);
		$this->assertEquals('', $this->obj->format->value);
		$this->assertNull($this->obj->parent_id->value);
		$this->assertTrue(is_array($this->obj->templates));
		$this->assertFalse($this->obj->is_cached->value);
		$this->assertFalse($this->obj->save_mini->value);
		$this->assertFalse($this->obj->gallery_thumbnail->value);
	}

	/**
	 * @throws ContentValidationException
	 * @throws RecordNotFoundException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws ConfigurationUndefinedException
	 */
	function testGetContentLabel()
	{
		$o = new ContentProperties();
		$this->assertEquals('', $o->getContentLabel());

		$o->label = 'My Assigned Label';
		$this->assertEquals('', $o->getContentLabel());

		$o->name->setInputValue('My Assigned Label');
		$o->label = '';
		$this->assertEquals('My Assigned Label', $o->getContentLabel());

		$o->id->setInputValue(self::TEST_ID_FOR_READ);
		$o->read();
		$this->assertEquals(ContentPropertiesTest::TEST_CONTENT_LABEL_READING, $o->getContentLabel());
		$this->assertEquals(ContentPropertiesTest::TEST_IMAGE_LABEL, $o->label);
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
     * @throws ContentValidationException
     * @throws NotImplementedException
     * @throws Exception
     */
	public function testDelete()
	{
		$this->obj->id->setInputValue(ContentPropertiesTest::TEST_ID_FOR_DELETE);

		$query = "SEL"."ECT `id` FROM ".ContentProperties::getTableName()." WHERE `parent_id` = ?";
        $parent_id = ContentPropertiesTest::TEST_ID_FOR_DELETE;
		$data = $this->conn->fetchRecords($query, 'i', $parent_id);
		$child_id = $data[0]->id;
		$this->assertNotNull($child_id);

		$this->obj->delete();

		$this->assertGreaterThan(0, $this->obj->id->value);

		$query = "SEL"."ECT COUNT(1) AS `count` FROM `". ContentProperties::getTableName()."` WHERE `id` = ?";
		$data = $this->conn->fetchRecords($query, 'i', $this->obj->id->value);
		$this->assertEquals(0, $data[0]->count);

		$query = "SEL"."ECT `parent_id` FROM `".ContentProperties::getTableName()."` WHERE `id` = ?";
		$data = $this->conn->fetchRecords($query, 'i', $child_id);
		$this->assertNull($data[0]->parent_id);
	}

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws RecordNotFoundException
	 */
	function testGetContentRouteByOperation()
	{
		$cp = new ContentProperties();
		$cp->id->value = self::TEST_CONTENT_TYPE_ID;
		$cp->read();

		$this->assertGreaterThan(0, count($cp->routes));

		$route = $cp->getContentRouteByOperation('listings');
		$this->assertInstanceOf(ContentRoute::class, $route);
		$this->assertEquals('listings', $route->operation->value);

		$route = $cp->getContentRouteByOperation('nonexistent-template');
		$this->assertNull($route);
	}

	/**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     */
    function testGetContentTemplateByName()
    {
        $cp = new ContentProperties();
        $cp->id->value = self::TEST_ID_FOR_READ;
        $cp->read();

        $template = $cp->getContentTemplateByName('listings-'.self::UNIT_TEST_IDENTIFIER);
        $this->assertInstanceOf(ContentTemplate::class, $template);
        $this->assertEquals('listings-'.self::UNIT_TEST_IDENTIFIER, $template->name->value);

        $template = $cp->getContentTemplateByName('nonexistent-template');
        $this->assertNull($template);
    }

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function testGetParentID()
	{
		$p = new ContentProperties();
		$p->name->setInputValue(self::UNIT_TEST_IDENTIFIER." parent record");
		$p->save();

		$c = new ContentProperties(self::TEST_ID_FOR_READ);
		$c->read();
		$c->parent_id->setInputValue($p->id->value);
		$c->save();

		$parent_id = $c->getParentID();
		$this->assertEquals($p->id->value, $parent_id);

		$p->delete();
	}

	public function testHasData()
	{
		$obj = new ContentProperties();
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

    /**
     * @throws ConfigurationUndefinedException
     * @throws Exception
     */
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
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ConnectionException
     * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidTypeException
     * @throws RecordNotFoundException
     */
    public function testRead()
    {
        $this->obj->id->value = 2;
        $this->obj->read();
        $this->assertEquals('Idea', $this->obj->name->value);
    }

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function testReadRoutes()
	{
		$add_amount = 2;
		$cp = new ContentProperties();
		$cp->id->setInputValue(self::TEST_CONTENT_TYPE_ID);
		$cp->readRoutes();
		$original_count = count($cp->routes);
		$this->assertGreaterThan(0, $original_count);

		$this->addContentRoutes($add_amount);

		$cp->readRoutes();
		$this->assertCount($original_count+$add_amount, $cp->routes);
		$names = array_map(function($i) { return $i->operation->value; }, $cp->routes);
		for($i=0; $i<$add_amount;$i++) {
			$expected = self::UNIT_TEST_IDENTIFIER . sprintf(' %02d', $i);
			$this->assertContains($expected, $names);
		}
		$this->removeContentRoutes($cp);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
	 * @throws InvalidQueryException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function testReadTemplates()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->readTemplates();
		$this->assertCount(3, $this->obj->templates);

		$this->addContentTemplates();

		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->readTemplates();
		$this->assertCount(6, $this->obj->templates);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." edit", $this->obj->templates[5]->name->value);

		$this->removeContentTemplates($this->obj);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	public function testReadWithoutTemplatesOrExtraProperties()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);

		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);

		/* without templates or extra properties */
        $this->obj->read();
        $this->postReadAssertions($id_param, $parent_name, $label, 0);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function testReadWithTemplates()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);
		$this->addContentTemplates();

        $this->obj->read();
        $this->postReadAssertions($id_param, $parent_name, $label, 3);

		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." edit", $this->obj->templates[2]->name->value);

		$this->removeContentTemplates($this->obj);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
     * @throws RecordNotFoundException
	 */
	public function testReadWithExtraProperties()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);

		$this->addSectionOperations(self::TEST_ID_FOR_READ);

		$this->obj->read();

		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);

        $this->postReadAssertions($id_param, $parent_name, $label, 0);

		/* cleanup */
		$this->removeSectionOperations(self::TEST_ID_FOR_READ);
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws InvalidQueryException
	 * @throws InvalidTypeException
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function testReadWithTemplatesAndExtraProperties()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);

		/* link templates and section operations */
		$this->addContentTemplates();
		$this->addSectionOperations(self::TEST_ID_FOR_READ);

		$this->obj->read();

		list($id_param, $parent_name, $label) = $this->fetchExtraProperties($this->obj->id->value);

        $this->postReadAssertions($id_param, $parent_name, $label, 3);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER." edit", $this->obj->templates[2]->name->value);

		/* cleanup */
		$this->removeContentTemplates($this->obj);
		$this->removeSectionOperations(self::TEST_ID_FOR_READ);
	}
}