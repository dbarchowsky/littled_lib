<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\PageContent\PageContentChild;
use Littled\Tests\TestHarness\PageContent\PageContentWithFiltersTestHarness;
use Littled\Utility\LittledUtility;
use PHPUnit\Framework\TestCase;


class PageContentTest extends TestCase
{
    public PageContent $obj;
    /** @var string */
    protected const STATIC_TEMPLATE_PATH = 'assets/templates/page-content-test-static.php';
    /** @var string */
    protected const DYNAMIC_TEMPLATE_PATH = 'assets/templates/page-content-test-variable.php';

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new PageContent();
    }

	protected function tearDown(): void
	{
		parent::tearDown();
		$this->obj->closeDatabaseConnection();
	}

    protected static function getDynamicTemplatePath(): string
    {
        return LittledUtility::joinPaths(APP_BASE_DIR, 'Tests', self::DYNAMIC_TEMPLATE_PATH);
    }

	/**
	 * @dataProvider \Littled\Tests\DataProvider\PageContent\PageContentTestDataProvider::collectEditActionTestProvider()
	 * @param string $expected
	 * @param array $data
	 * @param string|null $assigned_value
	 * @return void
	 */
	function testCollectEditAction(string $expected, array $data, ?string $assigned_value)
	{
		$o = new PageContent();
		if ($assigned_value !== null) {
			$o->edit_action = $assigned_value;
		}
		$o->collectEditAction($data);
		$this->assertEquals($expected, $o->edit_action);
	}

    /**
     * @throws NotImplementedException
     */
    function testFormatQueryString()
    {
        $o = new PageContentWithFiltersTestHarness();

        $_POST['intFilter'] = 64;
        $_POST['name'] = 'testValue';
        $_POST['boolFilter'] = true;
        $_POST['dateBefore'] = '2022-07-02';
        $o->filters->collectFilterValues();

        // confirm return value
        $query_string = $o->formatQueryString();
        $parts = explode('&', $query_string);
        $this->assertContains('intFilter=64', $parts);
        $this->assertContains('name=testValue', $parts);
        $this->assertContains('dateBefore=07%2F02%2F2022', $parts);
        $this->assertNotContains('dateAfter=', $parts);

        // confirm internal property value
        $parts = explode('&', $o->getQueryString());
        $this->assertContains('intFilter=64', $parts);
        $this->assertContains('name=testValue', $parts);
        $this->assertContains('dateBefore=07%2F02%2F2022', $parts);
        $this->assertNotContains('dateAfter=', $parts);

        // confirm exclude filters
        $parts = explode('&', $o->formatQueryString(['name', 'intFilter']));
        $this->assertNotContains('intFilter=64', $parts);
        $this->assertNotContains('name=testValue', $parts);
        $this->assertContains('dateBefore=07%2F02%2F2022', $parts);
        $this->assertNotContains('dateAfter=', $parts);
        $_POST = [];
    }

	function testGetContentLabel()
	{
		$o = new PageContentChild();
		$this->assertEquals('', $o->getContentLabel());

		$o->content->content_properties->name->setInputValue('my assigned value');
		$this->assertEquals('my assigned value', $o->getContentLabel());
	}

    /**
     * @dataProvider \Littled\Tests\DataProvider\PageContent\PageContentTestDataProvider::getRecordIdProvider()
     * @param int|null $record_id
     * @param int|null $expected
     * @return void
     */
    function testGetRecordId(?int $record_id, ?int $expected)
    {
        $o = new PageContentChild();
        $o->content->id->value = $record_id;
        $this->assertEquals($expected, $o->getRecordId());
    }

    /**
     * @throws NotImplementedException
     */
    function testGetQueryString()
    {
        $o = new PageContentWithFiltersTestHarness();
        $this->assertEquals('', $o->getQueryString());

        $_POST['intFilter'] = 849;
        $_POST['name'] = 'this is a test';
        $o->filters->collectFilterValues();

        $o->formatQueryString();
        $this->assertMatchesRegularExpression('/&name=this\+is\+a\+test&intFilter=849$/', $o->getQueryString());

        $_POST = [];
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function testMissingTemplate()
    {
        $this->obj->setTemplatePath('/path/to/non-existent/template.php');
        $this->expectException(ResourceNotFoundException::class);
        $this->obj->render();
    }

    /**
     * @return void
     * @throws ResourceNotFoundException
     * @throws ConfigurationUndefinedException
     */
    public function testRender()
    {
        $this->obj->setTemplatePath($this::getDynamicTemplatePath());
        $this->expectOutputRegex('/This is test template content\./');
        $this->obj->render(array('test_var' => ''));
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function testSendResponse()
    {
        $inject_test = 'This is my injected text.';
        $pattern = '/This is test template content\.(.|\n)*'.str_replace('.', '\.', $inject_test).'/';

        $o = new PageContentChild();
        $o->injected_text = $inject_test;
        $o->setTemplatePath($this::getDynamicTemplatePath());

        $this->expectOutputRegex($pattern);
        $o->sendResponse();
    }

    public function testSetTemplatePath()
    {
        $test_template_path = '/path/to/template.php';
        $this->assertEquals('', $this->obj->template_path);

        $this->obj->setTemplatePath($test_template_path);
        $this->assertEquals($test_template_path, $this->obj->template_path);
    }

    /**
     * @return void
     * @throws ConfigurationUndefinedException
     * @throws ResourceNotFoundException
     */
    public function testUnsetTemplatePath()
    {
        $this->expectException(ConfigurationUndefinedException::class);
        $this->obj->render();
    }
}