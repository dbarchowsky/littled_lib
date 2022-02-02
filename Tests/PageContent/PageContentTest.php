<?php
namespace Littled\Tests\PageContent;
require_once(realpath(dirname(__FILE__)) . "/../bootstrap.php");

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
use Littled\Tests\PageContent\TestObject\PageContentChild;
use PHPUnit\Framework\TestCase;


class PageContentTest extends TestCase
{
    /** @var PageContent */
    public $obj;
    /** @var string */
    protected const STATIC_TEMPLATE_PATH = '../assets/templates/page-content-test-static.php';
    /** @var string */
    protected const DYNAMIC_TEMPLATE_PATH = '../assets/templates/page-content-test-variable.php';

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

	/**
	 * @dataProvider \Littled\Tests\PageContent\DataProvider\PageContentTestDataProvider::collectEditActionTestProvider()
	 * @param string $expected
	 * @param array $data
	 * @return void
	 */
	function testCollectEditAction(string $expected, array $data)
	{
		$o = new PageContent();
		$o->collectEditAction($data);
		$this->assertEquals($expected, $o->edit_action);
	}

	function testGetContentLabel()
	{
		$o = new PageContentChild();
		$this->assertEquals('', $o->getContentLabel());

		$o->content->content_properties->name->setInputValue('my assigned value');
		$this->assertEquals('my assigned value', $o->getContentLabel());
	}

    /**
     * @dataProvider \Littled\Tests\PageContent\DataProvider\PageContentTestDataProvider::getRecordIdProvider()
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
        $this->obj->setTemplatePath(PageContentTest::DYNAMIC_TEMPLATE_PATH);
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
        $o->setTemplatePath(PageContentTest::DYNAMIC_TEMPLATE_PATH);

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