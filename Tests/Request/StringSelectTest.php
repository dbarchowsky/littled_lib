<?php
namespace Littled\Tests\Request;

use Littled\Request\RequestInput;
use Littled\Request\StringSelect;
use Littled\Tests\Request\DataProvider\StringSelectTestData;
use PHPUnit\Framework\TestCase;

/**
 * Class StringSelectTest
 * @package Littled\Tests\Request
 */
class StringSelectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RequestInput::setTemplateBasePath(LITTLED_TEMPLATE_DIR.'forms/input-elements/');
    }

    /**
	 * @dataProvider \Littled\Tests\Request\DataProvider\StringSelectTestDataProvider::renderTestProvider()
	 * @param StringSelectTestData $data
	 * @return void
	 */
	function testRender(StringSelectTestData $data)
	{
		$this->expectOutputRegex($data->expected);
		$data->input->render($data->override_label, $data->css_class, $data->options);
	}
}