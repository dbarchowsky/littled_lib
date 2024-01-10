<?php
namespace LittledTests\PageContent;

use Littled\Exception\ResourceNotFoundException;
use Littled\PageContent\ContentUtils;
use PHPUnit\Framework\TestCase;


class ContentUtilsTest extends TestCase
{
    /** @var string */
    protected const STATIC_TEMPLATE_PATH = APP_BASE_DIR.'Tests/assets/templates/page-content-test-static.php';
    /** @var string */
    protected const DYNAMIC_TEMPLATE_PATH = APP_BASE_DIR.'Tests/assets/templates/page-content-test-variable.php';

    /**
     * @throws ResourceNotFoundException
     */
    public function testLoadTemplateContentWithoutContext()
    {
        $markup = ContentUtils::loadTemplateContent(ContentUtilsTest::STATIC_TEMPLATE_PATH);
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
        $markup = ContentUtils::loadTemplateContent(ContentUtilsTest::DYNAMIC_TEMPLATE_PATH, $context);
        $this->assertStringContainsString('variable content: Custom test value.', $markup);
    }

    public function testPrintError()
    {
        $err = "Test error output.";

        // test output with all default options
        $this->expectOutputRegex('/class=\"alert alert-error.*'.str_replace('.', '\.', $err).'/');
        ContentUtils::printError($err);
        ob_clean();

        // test with non-default template
        $this->expectOutputRegex('/^\[ERROR\]'.str_replace('.', '\.', $err).'\[ERROR\]$/');
        ContentUtils::printError($err, '[ERROR]%s[ERROR]');
        ob_clean();

        // test with non-default css class
        $this->expectOutputRegex('/^<div class=\"my-custom-class\">.*'.str_replace('.', '\.', $err).'/');
        ContentUtils::printError($err, '', 'my-custom-class');
    }

    /**
     * @return void
     * @throws ResourceNotFoundException
     */
    public function testRenderTemplate()
    {
        $inject_test = 'This is injected text.';

        // test with static template content
        $this->expectOutputRegex('/This is test template content\./');
        ContentUtils::renderTemplate(ContentUtilsTest::STATIC_TEMPLATE_PATH);
        ob_clean();

        // test with dynamic template content
        $this->expectOutputRegex('/This is test template content\.(.|\n)*'.str_replace('.', '\.', $inject_test).'/');
        ContentUtils::renderTemplate(ContentUtilsTest::DYNAMIC_TEMPLATE_PATH, array(
            'test_var' => $inject_test
        ));
    }
}