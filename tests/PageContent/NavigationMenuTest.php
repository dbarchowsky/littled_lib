<?php
namespace Littled\Tests\PageContent;

use Littled\PageContent\Navigation\NavigationMenu;


define ('LITTLED_TEMPLATE_DIR', realpath(dirname(__FILE__)."/../../")."/templates/");

class NavigationMenuTest extends \PHPUnit_Framework_TestCase
{
	public function testNodeImagePath()
	{
		$m = new NavigationMenu();
		$m->addNode("Without an image");
		$m->addNode("With image");
		$m->last->setImagePath("/path/to/image.jpg");
		$m->addNode("With image and link", "/path/to/page");
		$m->last->setImagePath("/path/to/image2.jpg");

		$n = $m->first;
		ob_start();
		$n->render();
		$markup = ob_get_contents();
		ob_end_clean();

		$this->assertEquals("Without an image", $n->label, "Text node label");
		$this->assertFalse(strpos($markup, '<img'), "Node without image has no 'img' tag.");
		$this->assertNotFalse(strpos($markup, '<a href="#"'), "Node without url has no 'href' attribute.");

		$n = $n->nextNode;
		ob_start();
		$n->render();
		$markup = ob_get_contents();
		ob_end_clean();

		$this->assertEquals("With image", $n->label, "Unlinked image node label");
		$this->assertNotFalse(strpos($markup, '<img'), "Unlinked image node has 'img' tag.");
		$this->assertNotFalse(strpos($markup, '/path/to/image.jpg'), "Unlinked image node expected image path.");
		$this->assertNotFalse(strpos($markup, '<a href="#"'), "Unlinked image node has no 'href' attribute.");

		$n = $n->nextNode;
		ob_start();
		$n->render();
		$markup = ob_get_contents();
		ob_end_clean();

		$this->assertEquals("With image and link", $n->label, "Linked image node label");
		$this->assertNotFalse(strpos($markup, '<img'), "Linked image node has 'img' tag.");
		$this->assertNotFalse(strpos($markup, '/path/to/image2.jpg'), "Linked image node expected image path.");
		$this->assertFalse(strpos($markup, '<a href="#"'), "Linked image node has no 'href' attribute.");
	}
}