<?php
namespace Littled\Tests\PageContent;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\PageContent;
use PHPUnit\Framework\TestCase;


class PageContentTest extends TestCase
{
    /**
     * @throws ResourceNotFoundException
     */
    public function testLoadTemplateContentWithoutContext()
    {
        $markup = PageContent::loadTemplateContent('../assets/page-content-test-static.php');
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
        $markup = PageContent::loadTemplateContent('../assets/page-content-test-variable.php', $context);
        $this->assertStringContainsString('variable content: Custom test value.', $markup);
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function testLoadTemplateContentUsingDocumentRoot()
    {
        $template_path = "/assets/page-content-test-static.php";
        $markup = PageContent::loadTemplateContent($template_path);
        $this->assertStringContainsString('This is test template content.', $markup);

        $template_path = $_SERVER['DOCUMENT_ROOT'].'assets/page-content-test-static.php';
        $markup = PageContent::loadTemplateContent($template_path);
        $this->assertStringContainsString('This is test template content.', $markup);
    }
}