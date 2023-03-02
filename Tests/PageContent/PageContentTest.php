<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\NotImplementedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
use Littled\Tests\TestHarness\PageContent\PageContentChild;
use Littled\Tests\TestHarness\PageContent\PageContentTestHarness;
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
        $this->obj = new PageContentTestHarness();
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

	function testGetContentLabel()
	{
		$o = new PageContentChild();
		$this->assertEquals('', $o->getContentLabel());

		$o->content->content_properties->name->setInputValue('my assigned value');
		$this->assertEquals('my assigned value', $o->getContentLabel());
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
        $this->assertEquals('', $this->obj->getTemplatePath());

        $this->obj->setTemplatePath($test_template_path);
        $this->assertEquals($test_template_path, $this->obj->getTemplatePath());
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