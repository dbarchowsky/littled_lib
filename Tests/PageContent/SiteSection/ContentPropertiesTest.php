<?php
namespace LittledTests\PageContent\SiteSection;

use Littled\Database\MySQLConnection;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ConnectionException;
use Littled\Exception\ContentValidationException;
use Littled\Exception\InvalidQueryException;
use Littled\Exception\InvalidValueException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\RecordNotFoundException;
use Littled\PageContent\Serialized\SerializedContent;
use Littled\PageContent\SiteSection\ContentRoute;
use Littled\PageContent\SiteSection\ContentTemplate;
use LittledTests\DataProvider\PageContent\SiteSection\ContentTemplateData;
use Littled\PageContent\SiteSection\ContentProperties;
use LittledTests\TestHarness\PageContent\SiteSection\ContentPropertiesTestHarness;
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
        $query = "CALL siteSectionUpdate(?,?,?,?,?,?,?,?,?,?,?)";
	    $stmt = $mysqli->prepare($query);
	    $stmt->bind_param('issssssiiii',
		    $id,
		    $name,
		    $label,
		    $id_key,
		    $slug,
		    $root_dir,
		    $table,
		    $parent_id,
		    $is_cached,
		    $is_sortable,
		    $gallery_thumbnail);

        $id = ContentPropertiesTest::TEST_ID_FOR_DELETE;
        $name = ContentPropertiesTest::UNIT_TEST_IDENTIFIER;
	    $label = 'unit test';
	    $id_key = 'testId';
        $slug = 'unit_test_slug';
        $root_dir = 'path/to/section';
        $table = 'unit_test';
        $parent_id = null;
        $is_cached = false;
	    $is_sortable = true;
        $gallery_thumbnail = false;

        if(!$stmt->execute()) {
            throw new Exception('Error executing query: '.$mysqli->error);
        }

	    $stmt = $mysqli->prepare($query);
	    $stmt->bind_param('issssssiiii',
		    $id,
		    $name,
			$label,
			$id_key,
		    $slug,
		    $root_dir,
		    $table,
		    $parent_id,
		    $is_cached,
			$is_sortable,
		    $gallery_thumbnail);

        $id = ContentPropertiesTest::TEST_ID_FOR_READ;
        $name = ContentPropertiesTest::TEST_CONTENT_LABEL_READING;
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
        $query = 'INS'.'ERT INTO `content_template` (id, site_section_id, name, path) VALUES (?,?,?,?) '.
            'ON DUPLICATE KEY UPDATE site_section_id = ?, '.
            'name = ?, '.
            'path = ?';
        $mysqli = $conn->getMysqli();
        $stmt = $mysqli->prepare($query);
        $content_type_id = self::TEST_ID_FOR_READ;
        $stmt->bind_param('iississ', $new_id, $content_type_id, $name, $path, $content_type_id, $name, $path);

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
     * array_map() callback that returns all the route values currently stored in a ContentProperties object
     * @param ContentRoute $o
     * @return mixed
     */
    protected static function mapRouteValues(ContentRoute $o)
    {
        return $o->route->value;
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
     * @throws InvalidQueryException
     * @throws InvalidValueException
	 * @throws NotImplementedException
	 * @throws ConfigurationUndefinedException
	 */
	protected function addContentRoutes(int $n)
	{
		for($i=0; $i<$n; $i++) {
			$name = self::UNIT_TEST_IDENTIFIER.sprintf(' %02d', $i);
			$route = new ContentRoute(null, self::TEST_CONTENT_TYPE_ID, $name, sprintf('/temp-test%02d', $i), 'https://localhost');
			$route->save();
		}
	}

	/**
	 * @throws ContentValidationException
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
     * @throws InvalidQueryException
     * @throws InvalidValueException
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
			",`id_key`".
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
			return (array($data[0]->id_key, $data[0]->parent, $data[0]->label));
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
		$this->assertEquals($id_key, $this->obj->id_key->value);
		$this->assertEquals($parent_name, $this->obj->parent);
		$this->assertEquals($label, $this->obj->label->value);
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
		$query = 'DEL'.'ETE FROM `section_operations` WHERE `section_id` = ?';
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
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	public function testClearValues()
	{
		$this->obj->id->setInputValue(self::TEST_ID_FOR_READ);
		$this->obj->read();
		$this->obj->clearValues();
		$this->assertCount(0, $this->obj->templates);
		$this->assertEquals('', $this->obj->label->value);
		$this->assertEquals('', $this->obj->id_key->value);
		$this->assertEquals('', $this->obj->parent);
	}

	public function testDefaultValues()
	{
		$this->assertEquals(ContentProperties::ID_KEY, $this->obj->id->key);
		$this->assertEquals('', $this->obj->name->value);
		$this->assertEquals('', $this->obj->label->value);
		$this->assertEquals('', $this->obj->id_key->value);
		$this->assertNull($this->obj->parent_id->value);
		$this->assertTrue(is_array($this->obj->templates));
		$this->assertFalse($this->obj->is_cached->value);
		$this->assertFalse($this->obj->gallery_thumbnail->value);
	}

	/**
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
	 * @throws RecordNotFoundException
	 */
	function testGetContentLabel()
	{
		$o = new ContentProperties();
		$this->assertEquals('', $o->getContentLabel());

		$o->label->value = 'My Assigned Label';
		$this->assertEquals($o->label->value, $o->getContentLabel());

		$o->label->setInputValue('');
		$o->name->value = 'My Assigned Name';
		$this->assertEquals($o->name->value, $o->getContentLabel());

		$o->id->setInputValue(self::TEST_ID_FOR_READ);
		$o->read();
		$this->assertEquals(ContentPropertiesTest::TEST_CONTENT_LABEL_READING, $o->getContentLabel());
	}

	public function testInitialize()
	{
		$this->obj->name->setInputValue(self::UNIT_TEST_IDENTIFIER);
		$this->obj->root_dir->setInputValue("path/to/section/");
		$this->obj->table->setInputValue('unit_test');
		$this->obj->parent_id->setInputValue(142);
		$this->obj->is_cached->setInputValue(false);
		$this->obj->gallery_thumbnail->setInputValue(true);

		$this->assertNull($this->obj->id->value);
		$this->assertEquals(self::UNIT_TEST_IDENTIFIER, $this->obj->name->value);
		$this->assertEquals('path/to/section/', $this->obj->root_dir->value);
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
     * @throws InvalidValueException
     * @throws NotImplementedException
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
     * @throws InvalidValueException
     * @throws NotImplementedException
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
	 * @throws NotImplementedException
	 * @throws RecordNotFoundException|InvalidValueException
     */
	public function testGetParentID()
	{
		$p = new ContentProperties();
		$p->name->setInputValue(self::UNIT_TEST_IDENTIFIER." parent record");
		$p->save();

		$c = (new ContentProperties)->setRecordId(self::TEST_ID_FOR_READ);
		$c->read();
		$c->parent_id->setInputValue($p->id->value);
		$c->save();

		$parent_id = $c->getParentID();
		$this->assertEquals($p->id->value, $parent_id);

		$p->delete();
	}

    /**
     * @dataProvider \LittledTests\DataProvider\PageContent\SiteSection\ContentPropertiesTestDataProvider::hasDataTestDataProvider()
     * @param bool $expected
     * @param int|null $id
     * @param string|null $name
     * @return void
     */
	public function testHasData(bool $expected, ?int $id, ?string $name)
	{
		$o = new ContentProperties();
        if ($name !== '[use defaults]') {
            $o->id->setInputValue($id);
            $o->name->setInputValue($name);
        }
		self::assertEquals($expected, $o->hasData());
	}

    public function testNewRouteInstance()
    {
        $record_id = 99;
        $site_section_id = 1011;

        $obj = new ContentPropertiesTestHarness();
        $route = $obj->publicNewRouteInstance($record_id, $site_section_id, 'listings', 'my-route', 'https://localhost');
        $this->assertEquals($record_id, $route->id->value);
        $this->assertEquals($site_section_id, $route->site_section_id->value);
        $this->assertEquals('listings', $route->operation->value);
        $this->assertEquals('my-route', $route->route->value);
        $this->assertEquals('https://localhost', $route->api_route->value);
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
     * @throws InvalidValueException
     * @throws NotImplementedException
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
     * @throws InvalidValueException
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

        // test route values from original data set
        $routes = array_map([ContentPropertiesTest::class, 'mapRouteValues'], $cp->routes);
        $this->assertContains('/test/[#]', $routes);
        $this->assertContains('/tests', $routes);

		$this->addContentRoutes($add_amount);

		$cp->readRoutes();
		$this->assertCount($original_count+$add_amount, $cp->routes);
		$names = array_map(function($i) { return $i->operation->value; }, $cp->routes);
		for($i=0; $i<$add_amount;$i++) {
			$expected = self::UNIT_TEST_IDENTIFIER . sprintf(' %02d', $i);
			$this->assertContains($expected, $names);
		}
        // test route values from new records
        $routes = array_map([ContentPropertiesTest::class, 'mapRouteValues'], $cp->routes);
        $this->assertContains('/temp-test01', $routes);

		$this->removeContentRoutes($cp);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
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
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
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
     * @throws InvalidValueException
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
	 * @return void
	 * @throws ConfigurationUndefinedException
	 * @throws ConnectionException
	 * @throws ContentValidationException
     * @throws InvalidQueryException
     * @throws InvalidValueException
     * @throws NotImplementedException
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
     * @throws InvalidValueException
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