<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\ConfigurationUndefinedException;
use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
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
        $this->obj->render();
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