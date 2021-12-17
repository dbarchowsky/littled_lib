<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
use PHPUnit\Framework\TestCase;


class PageContentTest extends TestCase
{
	/** @var PageContent */
	protected $obj;

	protected function setUp(): void
	{
		parent::setUp();
		$this->obj = new PageContent();
	}

	public function testGetTemplatePath()
	{
		$test_path = '/path/to/template.tmpl';

		// default template value is empty string
		$this->assertEquals('', $this->obj->getTemplatePath());

		// tests setting and getting template value
		$this->obj->setTemplatePath($test_path);
		$this->assertEquals($test_path, $this->obj->getTemplatePath());
	}

    /**
     * @throws ResourceNotFoundException
     */
    public function testLoadTemplateContentWithoutContext()
    {
        $markup = PageContent::loadTemplateContent('../assets/templates/page-content-test-static.php');
        $this->assertStringContainsString('This is test template content.', $markup);
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function testLoadTemplateContentWithContext()
    {
        $context = array(
          'test_var' => 'Custom test value.'
        );
        $markup = PageContent::loadTemplateContent('../assets/templates/page-content-test-variable.php', $context);
        $this->assertStringContainsString('variable content: Custom test value.', $markup);
    }
}