<?php

namespace Littled\Tests\PageContent\SiteSection\DataProvider;


use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;
use Littled\PageContent\SiteSection\ContentTemplate;

class ContentTemplateTestDataProvider
{
	public const TEST_CONTENT_TYPE_ID = 6037;

	/** @var string */
	public $expected;
	/** @var ContentTemplate */
	public $template;
	/** @var string */
	public $msg;

	public function __construct(string $expected, string $name='', string $location='', string $template_dir='', string $path='', string $msg='')
	{
		$this->expected = $expected;
		$this->msg = $msg;
		$this->template =new ContentTemplate(null, self::TEST_CONTENT_TYPE_ID, $name, '', $path, $location);
        $this->template->template_dir->setInputValue($template_dir);
	}

	/**
	 * @throws ConfigurationUndefinedException
	 */
	public static function formatFullPathTestProvider(): array
	{
		LittledGlobals::setLocalTemplatesPath(LITTLED_TEMPLATE_DIR);
		LittledGlobals::setSharedTemplatesPath(LITTLED_TEMPLATE_DIR);
		return array(
			[new ContentTemplateTestDataProvider(LittledGlobals::getSharedTemplatesPath().'my-template.php', '', '', '', 'my-template.php', 'default location')],
			[new ContentTemplateTestDataProvider(LittledGlobals::getSharedTemplatesPath().'my-template.php', '', 'shared', '', 'my-template.php', 'location: shared')],
			[new ContentTemplateTestDataProvider(LittledGlobals::getLocalTemplatesPath().'my-template.php', '', 'local', '', 'my-template.php', 'location: local')],
			[new ContentTemplateTestDataProvider(LittledGlobals::getSharedTemplatesPath().'my-template.php', '', 'invalid', '', 'my-template.php', 'invalid location')],
            [new ContentTemplateTestDataProvider('/path/to/custom/my-template.php', '', 'shared', '/path/to/custom/', 'my-template.php', 'location: shared; custom template dir')],
            [new ContentTemplateTestDataProvider('/path/to/custom/my-template.php', '', 'local', '/path/to/custom/', '/my-template.php', 'location: local; custom template dir')],
            [new ContentTemplateTestDataProvider('/path/to/custom/my-template.php', '', '', '/path/to/custom', 'my-template.php', 'default location; custom template dir')],
		);
	}
}