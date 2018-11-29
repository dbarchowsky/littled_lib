<?php
namespace Littled\Tests\PageContent;

use Littled\PageContent\PageContent;
use PHPUnit\Framework\TestCase;


class PageContentTest extends TestCase
{
    /**
     * @throws \Littled\Exception\ResourceNotFoundException
     */
    public function testLoadTemplateContentWithoutContext()
    {
        $markup = PageContent::loadTemplateContent('../assets/page-content-test-static.php');
        $this->assertContains('This is test template content.', $markup);
    }

    /**
     * @throws \Littled\Exception\ResourceNotFoundException
     */
    public function testLoadTemplateContentWithContext()
    {
        $context = array(
          'test_var' => 'Custom test value.'
        );
        $markup = PageContent::loadTemplateContent('../assets/page-content-test-variable.php', $context);
        $this->assertContains('variable content: Custom test value.', $markup);
    }

    /**
     * @throws \Littled\Exception\ResourceNotFoundException
     */
    public function testLoadTemplateContentUsingDocumentRoot()
    {
        $template_path = "/assets/page-content-test-static.php";
        $markup = PageContent::loadTemplateContent($template_path);
        $this->assertContains('This is test template content.', $markup);

        $template_path = $_SERVER['DOCUMENT_ROOT'].'assets/page-content-test-static.php';
        $markup = PageContent::loadTemplateContent($template_path);
        $this->assertContains('This is test template content.', $markup);
    }
}