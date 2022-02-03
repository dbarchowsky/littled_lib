<?php
namespace Littled\PageContent\Navigation\CMS;

use Littled\App\LittledGlobals;
use Littled\PageContent\Navigation\NavigationMenuNode;


class CMSNavigationMenuNode extends NavigationMenuNode
{
	/**
	 * CMSNavigationMenuNode constructor.
	 * @param string $label Menu item label
	 * @param string $url URL that the menu items links to.
	 * @param string $target Browser window to target for the link
	 * @param int $level Nesting level of the menu item.
	 * @param string $dom_id DOM id to assign to the menu item element
	 * @param string $attributes Additional attributes to assign to the menu item element
	 */
	public function __construct( string $label='', string $url='', string $target='', int $level=0, string $dom_id='', string $attributes='' )
	{
		parent::__construct( $label, $url, $target, $level, $dom_id, $attributes );
		static::setNodeTemplatePath(LittledGlobals::getTemplatePath()."framework/navigation/navigation-menu-node.php");
	}
}